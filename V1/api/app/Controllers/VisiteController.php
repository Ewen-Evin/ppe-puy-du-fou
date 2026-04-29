<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ParcoursModel;
use App\Models\VisiteModel;
use App\Services\ParcoursService;

final class VisiteController extends Controller
{
    public function create(array $ctx): array
    {
        $d = $this->input();
        if ($m = $this->require($d, ['date_visite', 'spectacles'])) {
            return $this->error('Champs manquants', 422);
        }
        if (!is_array($d['spectacles']) || !$d['spectacles']) {
            return $this->error('Aucun spectacle sélectionné', 422);
        }
        $idUser    = (int)$ctx['user']['sub'];
        $vitesse   = isset($d['vitesse_marche']) ? (float)$d['vitesse_marche'] : 4.0;
        $nomVisite = isset($d['nom_visite']) && $d['nom_visite'] !== '' ? (string)$d['nom_visite'] : null;

        $idVisite = VisiteModel::create($idUser, $d['date_visite'], $vitesse, $d['spectacles'], $nomVisite);

        $favoriteIndex = isset($d['parcours_favori']) ? (int)$d['parcours_favori'] : 0;

        $service  = new ParcoursService();
        $parcours = $service->calculer($idVisite);
        if ($parcours) {
            ParcoursModel::saveAll($idVisite, $parcours, $favoriteIndex);
        }

        return $this->json(['id_visite' => $idVisite], 201);
    }

    public function delete(array $ctx): array
    {
        $idVisite = (int)$ctx['params']['id'];
        $deleted  = VisiteModel::delete($idVisite, (int)$ctx['user']['sub']);
        if (!$deleted) {
            return $this->error('Visite introuvable', 404);
        }
        return $this->json(['ok' => true]);
    }

    public function updateFavori(array $ctx): array
    {
        $idVisite = (int)$ctx['params']['id'];
        $visite   = VisiteModel::find($idVisite);
        if (!$visite || (int)$visite['id_utilisateur'] !== (int)$ctx['user']['sub']) {
            return $this->error('Visite introuvable', 404);
        }
        $d = $this->input();
        ParcoursModel::setFavori($idVisite, (int)($d['parcours_favori'] ?? 0));
        return $this->json(['ok' => true]);
    }

    /** Calcule les parcours sans rien enregistrer en base. */
    public function preview(array $ctx): array
    {
        $d = $this->input();
        if ($m = $this->require($d, ['date_visite', 'spectacles'])) {
            return $this->error('Champs manquants', 422);
        }
        if (!is_array($d['spectacles']) || !$d['spectacles']) {
            return $this->error('Aucun spectacle sélectionné', 422);
        }
        $vitesse  = isset($d['vitesse_marche']) ? (float)$d['vitesse_marche'] : 4.0;
        $service  = new ParcoursService();
        $parcours = $service->calculerDirect($d['date_visite'], $vitesse, $d['spectacles']);
        return $this->json(['parcours' => $parcours]);
    }

    /** Retourne les étapes GPS du parcours favori pour l'écran Carte. */
    public function carte(array $ctx): array
    {
        $idVisite = (int)$ctx['params']['id'];
        $visite   = VisiteModel::find($idVisite);
        if (!$visite || (int)$visite['id_utilisateur'] !== (int)$ctx['user']['sub']) {
            return $this->error('Visite introuvable', 404);
        }

        $idParcours = ParcoursModel::favoriIdByVisite($idVisite);
        if (!$idParcours) {
            return $this->error('Aucun parcours pour cette visite', 404);
        }

        $etapes = ParcoursModel::etapesWithGps($idParcours);
        return $this->json([
            'vitesse_marche' => (float)$visite['vitesse_marche'],
            'date_visite'    => $visite['date_visite'],
            'nom_visite'     => $visite['nom_visite'] ?? '',
            'etapes'         => $etapes,
        ]);
    }

    public function index(array $ctx): array
    {
        return $this->json(VisiteModel::byUser((int)$ctx['user']['sub']));
    }

    public function parcours(array $ctx): array
    {
        $idVisite = (int)$ctx['params']['id'];
        $visite = VisiteModel::find($idVisite);
        if (!$visite || (int)$visite['id_utilisateur'] !== (int)$ctx['user']['sub']) {
            return $this->error('Visite introuvable', 404);
        }
        $parcours = ParcoursModel::byVisite($idVisite);
        if (!$parcours) {
            $service  = new ParcoursService();
            $parcours = $service->calculer($idVisite);
            if ($parcours) {
                ParcoursModel::saveAll($idVisite, $parcours);
            }
        }

        return $this->json([
            'visite'   => $visite,
            'parcours' => $parcours,
        ]);
    }
}
