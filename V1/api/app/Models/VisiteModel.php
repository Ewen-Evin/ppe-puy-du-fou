<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class VisiteModel
{
    public static function create(int $idUser, string $date, float $vitesse, array $idsSpectacles): int
    {
        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            $s = $pdo->prepare(
                'INSERT INTO Visite (date_visite, vitesse_marche, id_utilisateur)
                 VALUES (:d,:v,:u)'
            );
            $s->execute([':d' => $date, ':v' => $vitesse, ':u' => $idUser]);
            $idVisite = (int)$pdo->lastInsertId();

            $sc = $pdo->prepare('INSERT INTO Choisir (id_spectacle, id_visite) VALUES (:s,:v)');
            foreach ($idsSpectacles as $idSp) {
                $sc->execute([':s' => (int)$idSp, ':v' => $idVisite]);
            }
            $pdo->commit();
            return $idVisite;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function find(int $id): ?array
    {
        $s = Database::get()->prepare('SELECT * FROM Visite WHERE id_visite=:id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public static function spectaclesChoisis(int $idVisite): array
    {
        $s = Database::get()->prepare(
            'SELECT sp.* FROM Spectacle sp
             JOIN Choisir c ON c.id_spectacle = sp.id_spectacle
             WHERE c.id_visite = :v'
        );
        $s->execute([':v' => $idVisite]);
        return $s->fetchAll();
    }

    public static function byUser(int $idUser): array
    {
        $s = Database::get()->prepare(
            'SELECT * FROM Visite WHERE id_utilisateur=:u ORDER BY date_creation DESC'
        );
        $s->execute([':u' => $idUser]);
        return $s->fetchAll();
    }
}
