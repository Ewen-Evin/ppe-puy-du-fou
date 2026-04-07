<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\JoursModel;

final class JoursController extends Controller
{
    public function index(array $ctx): array { return $this->json(JoursModel::all()); }

    public function show(array $ctx): array
    {
        $j = JoursModel::find($ctx['params']['date']);
        return $j ? $this->json($j) : $this->error('Jour introuvable', 404);
    }

    public function upsert(array $ctx): array
    {
        $d = $this->input();
        if ($m = $this->require($d, ['id_jours', 'heure_ouverture', 'heure_fermeture'])) {
            return $this->error('Champs manquants', 422);
        }
        JoursModel::upsert($d['id_jours'], $d['heure_ouverture'], $d['heure_fermeture']);
        return $this->json(['ok' => true]);
    }

    public function delete(array $ctx): array
    {
        JoursModel::delete($ctx['params']['date']);
        return $this->json(['ok' => true]);
    }
}
