<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ParcoursModel
{
    /**
     * Persiste tous les parcours calculés pour une visite.
     * @param array<int, array{etapes:array, complet:bool, duree_totale_min:int, attente_min:int}> $parcoursList
     */
    public static function saveAll(int $idVisite, array $parcoursList, int $favoriteIndex = 0): void
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            $stmtP = $pdo->prepare(
                'INSERT INTO Parcours (id_visite, duree, est_complet, est_favori, temps_attente)
                 VALUES (:v, :d, :c, :f, :t)'
            );
            $stmtE = $pdo->prepare(
                'INSERT INTO Etape (id_parcours, id_seance, ordre, heure_arrivee)
                 VALUES (:p, :s, :o, :h)'
            );

            foreach ($parcoursList as $idx => $p) {
                $stmtP->execute([
                    ':v' => $idVisite,
                    ':d' => self::minutesToTime($p['duree_totale_min']),
                    ':c' => $p['complet'] ? 1 : 0,
                    ':f' => ($idx === $favoriteIndex) ? 1 : 0,
                    ':t' => self::minutesToTime($p['attente_min']),
                ]);
                $idParcours = (int)$pdo->lastInsertId();

                foreach ($p['etapes'] as $e) {
                    $stmtE->execute([
                        ':p' => $idParcours,
                        ':s' => $e['id_seance'],
                        ':o' => $e['ordre'],
                        ':h' => $e['heure_arrivee'],
                    ]);
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Lit tous les parcours sauvegardés pour une visite, avec leurs étapes.
     * Retourne le même format que ParcoursService::calculer().
     */
    public static function byVisite(int $idVisite): array
    {
        $pdo = Database::get();

        $stmtP = $pdo->prepare(
            'SELECT * FROM Parcours WHERE id_visite = :v
             ORDER BY est_complet DESC, temps_attente ASC'
        );
        $stmtP->execute([':v' => $idVisite]);
        $rows = $stmtP->fetchAll();

        if (!$rows) {
            return [];
        }

        $stmtE = $pdo->prepare(
            'SELECT e.ordre, e.heure_arrivee,
                    s.id_seance, s.heure_debut, s.heure_fin,
                    sp.id_spectacle, sp.libelle
             FROM Etape e
             JOIN Seance s    ON s.id_seance      = e.id_seance
             JOIN Spectacle sp ON sp.id_spectacle = s.id_spectacle
             WHERE e.id_parcours = :p
             ORDER BY e.ordre'
        );

        $result = [];
        foreach ($rows as $p) {
            $stmtE->execute([':p' => $p['id_parcours']]);
            $etapes = [];
            foreach ($stmtE->fetchAll() as $e) {
                $etapes[] = [
                    'ordre'         => (int)$e['ordre'],
                    'id_seance'     => (int)$e['id_seance'],
                    'id_spectacle'  => (int)$e['id_spectacle'],
                    'libelle'       => $e['libelle'] ?? '',
                    'heure_debut'   => $e['heure_debut'],
                    'heure_fin'     => $e['heure_fin'],
                    'heure_arrivee' => $e['heure_arrivee'],
                ];
            }
            $result[] = [
                'etapes'           => $etapes,
                'complet'          => (bool)$p['est_complet'],
                'favori'           => (bool)$p['est_favori'],
                'duree_totale_min' => self::timeToMinutes($p['duree']),
                'attente_min'      => self::timeToMinutes($p['temps_attente']),
            ];
        }
        return $result;
    }

    /**
     * Change le parcours favori d'une visite.
     * $favoriteIndex correspond à la position dans la liste triée (complets d'abord, attente min).
     */
    public static function setFavori(int $idVisite, int $favoriteIndex): void
    {
        $pdo = Database::get();
        // Récupère les IDs dans le même ordre que byVisite()
        $stmt = $pdo->prepare(
            'SELECT id_parcours FROM Parcours WHERE id_visite = :v
             ORDER BY est_complet DESC, temps_attente ASC'
        );
        $stmt->execute([':v' => $idVisite]);
        $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($ids)) return;

        $reset = $pdo->prepare('UPDATE Parcours SET est_favori = 0 WHERE id_visite = :v');
        $reset->execute([':v' => $idVisite]);

        if (isset($ids[$favoriteIndex])) {
            $set = $pdo->prepare('UPDATE Parcours SET est_favori = 1 WHERE id_parcours = :p');
            $set->execute([':p' => $ids[$favoriteIndex]]);
        }
    }

    /** Retourne l'id_parcours favori d'une visite (ou le premier si aucun favori). */
    public static function favoriIdByVisite(int $idVisite): ?int
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare(
            'SELECT id_parcours FROM Parcours WHERE id_visite = :v AND est_favori = 1 LIMIT 1'
        );
        $stmt->execute([':v' => $idVisite]);
        $row = $stmt->fetch();
        if ($row) return (int)$row['id_parcours'];

        // Fallback : premier parcours trié
        $stmt2 = $pdo->prepare(
            'SELECT id_parcours FROM Parcours WHERE id_visite = :v
             ORDER BY est_complet DESC, temps_attente ASC LIMIT 1'
        );
        $stmt2->execute([':v' => $idVisite]);
        $row2 = $stmt2->fetch();
        return $row2 ? (int)$row2['id_parcours'] : null;
    }

    /** Retourne les étapes d'un parcours avec les coordonnées GPS de chaque lieu. */
    public static function etapesWithGps(int $idParcours): array
    {
        $pdo  = Database::get();
        $stmt = $pdo->prepare(
            'SELECT e.ordre, e.heure_arrivee,
                    s.heure_debut, s.heure_fin,
                    sp.libelle,
                    l.nom AS lieu_nom, l.coordonnees_gps
             FROM Etape e
             JOIN Seance    s  ON s.id_seance    = e.id_seance
             JOIN Spectacle sp ON sp.id_spectacle = s.id_spectacle
             JOIN Lieu      l  ON l.id_lieu       = sp.id_lieu
             WHERE e.id_parcours = :p
             ORDER BY e.ordre'
        );
        $stmt->execute([':p' => $idParcours]);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $coords = array_map('trim', explode(',', $row['coordonnees_gps']));
            $result[] = [
                'ordre'         => (int)$row['ordre'],
                'libelle'       => $row['libelle'],
                'lieu_nom'      => $row['lieu_nom'],
                'lat'           => (float)($coords[0] ?? 0),
                'lng'           => (float)($coords[1] ?? 0),
                'heure_debut'   => $row['heure_debut'],
                'heure_fin'     => $row['heure_fin'],
                'heure_arrivee' => $row['heure_arrivee'],
            ];
        }
        return $result;
    }

    private static function minutesToTime(int $min): string
    {
        return sprintf('%02d:%02d:00', intdiv($min, 60), $min % 60);
    }

    private static function timeToMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }
}
