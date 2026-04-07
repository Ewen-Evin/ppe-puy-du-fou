<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
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
        $idUser = (int)$ctx['user']['sub'];
        $vitesse = isset($d['vitesse_marche']) ? (float)$d['vitesse_marche'] : 4.0;
        $idVisite = VisiteModel::create($idUser, $d['date_visite'], $vitesse, $d['spectacles']);
        return $this->json(['id_visite' => $idVisite], 201);
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
        $service = new ParcoursService();
        return $this->json([
            'visite'   => $visite,
            'parcours' => $service->calculer($idVisite),
        ]);
    }
}
