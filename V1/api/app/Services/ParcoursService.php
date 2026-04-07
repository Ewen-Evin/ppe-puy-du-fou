<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\DistanceModel;
use App\Models\JoursModel;
use App\Models\SeanceModel;
use App\Models\VisiteModel;

/**
 * Calcule des parcours possibles pour une visite donnée.
 *
 * Algorithme :
 *  1. Construction du graphe pondéré des lieux + plus courts chemins (Floyd-Warshall).
 *  2. Récupération des séances de chaque spectacle choisi pour la date.
 *  3. Backtracking : pour chaque combinaison ordonnée respectant les contraintes
 *     (ouverture parc, trajet possible entre deux séances), génération d'un parcours.
 *  4. Tri des résultats : complets d'abord, puis temps d'attente minimal.
 */
final class ParcoursService
{
    private const MAX_PARCOURS = 10;

    /** @return array<int, array{etapes:array, complet:bool, duree_totale_min:int, attente_min:int}> */
    public function calculer(int $idVisite): array
    {
        $visite = VisiteModel::find($idVisite);
        if (!$visite) {
            return [];
        }
        $date    = $visite['date_visite'];
        $vitesse = (float)$visite['vitesse_marche']; // km/h

        $jour = JoursModel::find($date);
        if (!$jour) {
            return [];
        }
        $ouverture = $this->toMinutes($jour['heure_ouverture']);
        $fermeture = $this->toMinutes($jour['heure_fermeture']);

        $spectacles = VisiteModel::spectaclesChoisis($idVisite);
        if (!$spectacles) {
            return [];
        }

        // Pour chaque spectacle, ses séances du jour
        $seancesParSpectacle = [];
        foreach ($spectacles as $sp) {
            $seancesParSpectacle[(int)$sp['id_spectacle']] = SeanceModel::bySpectacleAndDate(
                (int)$sp['id_spectacle'], $date
            );
        }

        // Graphe des distances + Floyd-Warshall (transitivité)
        $dist = $this->buildShortestPaths();

        // Backtracking
        $results = [];
        $this->explore(
            $seancesParSpectacle,
            array_keys($seancesParSpectacle),
            [],
            $ouverture,
            $fermeture,
            $vitesse,
            $dist,
            $results
        );

        // Si aucun parcours complet, accepter les partiels (au moins 1 spectacle)
        $complets = array_filter($results, fn($p) => $p['complet']);
        if (!$complets && !$results) {
            // Essayer parcours partiels en relâchant les contraintes
            $this->explorePartiel(
                $seancesParSpectacle, $ouverture, $fermeture, $vitesse, $dist, $results
            );
        }

        usort($results, function ($a, $b) {
            if ($a['complet'] !== $b['complet']) {
                return $b['complet'] <=> $a['complet'];
            }
            return $a['attente_min'] <=> $b['attente_min'];
        });

        return array_slice($results, 0, self::MAX_PARCOURS);
    }

    /** Backtracking récursif */
    private function explore(
        array $seancesParSpectacle, array $spectaclesRestants, array $courant,
        int $ouverture, int $fermeture, float $vitesse, array $dist, array &$results
    ): void {
        if (!$spectaclesRestants) {
            $results[] = $this->finaliser($courant, true);
            return;
        }

        foreach ($spectaclesRestants as $i => $idSp) {
            foreach ($seancesParSpectacle[$idSp] as $seance) {
                $debut = $this->toMinutes($seance['heure_debut']);
                $fin   = $this->toMinutes($seance['heure_fin']);

                if ($debut < $ouverture || $fin > $fermeture) {
                    continue;
                }

                if ($courant) {
                    $last = end($courant);
                    $finLast   = $this->toMinutes($last['heure_fin']);
                    $trajetMin = $this->trajetMinutes(
                        (int)$last['id_lieu'], (int)$seance['id_lieu'], $vitesse, $dist
                    );
                    if ($trajetMin === null || $finLast + $trajetMin > $debut) {
                        continue;
                    }
                }

                $next = $courant;
                $next[] = $seance;
                $reste = $spectaclesRestants;
                array_splice($reste, $i, 1);

                $this->explore(
                    $seancesParSpectacle, $reste, $next,
                    $ouverture, $fermeture, $vitesse, $dist, $results
                );

                if (count($results) >= self::MAX_PARCOURS * 5) {
                    return;
                }
            }
        }
    }

    /** Fallback : tente d'inclure le maximum de spectacles, en ignorant ceux infaisables */
    private function explorePartiel(
        array $seancesParSpectacle, int $ouverture, int $fermeture,
        float $vitesse, array $dist, array &$results
    ): void {
        // Approche gloutonne : trier toutes les séances par heure_debut, prendre celles compatibles
        $toutes = [];
        foreach ($seancesParSpectacle as $idSp => $seances) {
            foreach ($seances as $s) {
                $s['_id_sp'] = $idSp;
                $toutes[] = $s;
            }
        }
        usort($toutes, fn($a, $b) => strcmp($a['heure_debut'], $b['heure_debut']));

        $courant = [];
        $vus = [];
        foreach ($toutes as $seance) {
            if (in_array($seance['_id_sp'], $vus, true)) {
                continue;
            }
            $debut = $this->toMinutes($seance['heure_debut']);
            $fin   = $this->toMinutes($seance['heure_fin']);
            if ($debut < $ouverture || $fin > $fermeture) {
                continue;
            }
            if ($courant) {
                $last = end($courant);
                $trajet = $this->trajetMinutes(
                    (int)$last['id_lieu'], (int)$seance['id_lieu'], $vitesse, $dist
                );
                if ($trajet === null || $this->toMinutes($last['heure_fin']) + $trajet > $debut) {
                    continue;
                }
            }
            $courant[] = $seance;
            $vus[] = $seance['_id_sp'];
        }
        if ($courant) {
            $results[] = $this->finaliser($courant, count($vus) === count($seancesParSpectacle));
        }
    }

    private function finaliser(array $seances, bool $complet): array
    {
        $etapes = [];
        $attente = 0;
        $ordre = 1;
        $debutGlobal = $this->toMinutes($seances[0]['heure_debut']);
        $finGlobale  = $this->toMinutes(end($seances)['heure_fin']);

        foreach ($seances as $i => $s) {
            $heureArrivee = $i === 0 ? $s['heure_debut'] : $seances[$i - 1]['heure_fin'];
            if ($i > 0) {
                $gap = $this->toMinutes($s['heure_debut']) - $this->toMinutes($seances[$i - 1]['heure_fin']);
                if ($gap > 0) {
                    $attente += $gap;
                }
            }
            $etapes[] = [
                'ordre'         => $ordre++,
                'id_seance'     => (int)$s['id_seance'],
                'id_spectacle'  => (int)$s['id_spectacle'],
                'heure_debut'   => $s['heure_debut'],
                'heure_fin'     => $s['heure_fin'],
                'heure_arrivee' => $heureArrivee,
            ];
        }

        return [
            'etapes'           => $etapes,
            'complet'          => $complet,
            'duree_totale_min' => $finGlobale - $debutGlobal,
            'attente_min'      => $attente,
        ];
    }

    /**
     * Floyd-Warshall sur le graphe non orienté des distances.
     * @return array<int, array<int, int>> distance en mètres entre deux lieux
     */
    private function buildShortestPaths(): array
    {
        $edges = DistanceModel::all();
        $nodes = [];
        foreach ($edges as $e) {
            $nodes[(int)$e['id_lieu']] = true;
            $nodes[(int)$e['id_lieu_1']] = true;
        }
        $nodes = array_keys($nodes);

        $dist = [];
        foreach ($nodes as $a) {
            foreach ($nodes as $b) {
                $dist[$a][$b] = $a === $b ? 0 : PHP_INT_MAX;
            }
        }
        foreach ($edges as $e) {
            $a = (int)$e['id_lieu'];
            $b = (int)$e['id_lieu_1'];
            $m = (int)$e['distance_metres'];
            if ($m < $dist[$a][$b]) {
                $dist[$a][$b] = $m;
                $dist[$b][$a] = $m;
            }
        }
        foreach ($nodes as $k) {
            foreach ($nodes as $i) {
                foreach ($nodes as $j) {
                    if ($dist[$i][$k] !== PHP_INT_MAX && $dist[$k][$j] !== PHP_INT_MAX) {
                        $alt = $dist[$i][$k] + $dist[$k][$j];
                        if ($alt < $dist[$i][$j]) {
                            $dist[$i][$j] = $alt;
                        }
                    }
                }
            }
        }
        return $dist;
    }

    /** Renvoie le temps de trajet en minutes (entier arrondi) ou null si inaccessible */
    private function trajetMinutes(int $a, int $b, float $vitesseKmh, array $dist): ?int
    {
        if ($a === $b) {
            return 0;
        }
        if (!isset($dist[$a][$b]) || $dist[$a][$b] === PHP_INT_MAX) {
            return null;
        }
        $metres = $dist[$a][$b];
        $heures = ($metres / 1000) / max(0.1, $vitesseKmh);
        return (int)ceil($heures * 60);
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }
}
