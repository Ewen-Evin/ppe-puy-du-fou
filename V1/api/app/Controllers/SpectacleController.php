<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SpectacleModel;

final class SpectacleController extends Controller
{
    public function index(array $ctx): array
    {
        return $this->json(SpectacleModel::all());
    }

    public function show(array $ctx): array
    {
        $sp = SpectacleModel::find((int)$ctx['params']['id']);
        return $sp ? $this->json($sp) : $this->error('Spectacle introuvable', 404);
    }

    public function create(array $ctx): array
    {
        $d = $this->input();
        if ($m = $this->require($d, ['libelle', 'duree_spectacle', 'id_lieu'])) {
            return $this->error('Champs manquants : ' . implode(',', $m), 422);
        }
        $id = SpectacleModel::create($d);
        return $this->json(['id_spectacle' => $id], 201);
    }

    public function update(array $ctx): array
    {
        SpectacleModel::update((int)$ctx['params']['id'], $this->input());
        return $this->json(['ok' => true]);
    }

    public function delete(array $ctx): array
    {
        SpectacleModel::delete((int)$ctx['params']['id']);
        return $this->json(['ok' => true]);
    }
}
