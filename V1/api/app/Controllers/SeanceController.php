<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SeanceModel;

final class SeanceController extends Controller
{
    public function index(array $ctx): array
    {
        if (!empty($_GET['date'])) {
            return $this->json(SeanceModel::byDate($_GET['date']));
        }
        return $this->json(SeanceModel::all());
    }

    public function bySpectacle(array $ctx): array
    {
        return $this->json(SeanceModel::bySpectacle((int)$ctx['params']['id']));
    }

    public function create(array $ctx): array
    {
        $d = $this->input();
        if ($m = $this->require($d, ['date_seance', 'heure_debut', 'heure_fin', 'id_spectacle'])) {
            return $this->error('Champs manquants', 422);
        }
        return $this->json(['id_seance' => SeanceModel::create($d)], 201);
    }

    public function update(array $ctx): array
    {
        SeanceModel::update((int)$ctx['params']['id'], $this->input());
        return $this->json(['ok' => true]);
    }

    public function delete(array $ctx): array
    {
        SeanceModel::delete((int)$ctx['params']['id']);
        return $this->json(['ok' => true]);
    }
}
