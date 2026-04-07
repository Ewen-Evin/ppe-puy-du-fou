<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\DistanceModel;
use App\Models\LieuModel;

final class LieuController extends Controller
{
    public function index(array $ctx): array { return $this->json(LieuModel::all()); }

    public function show(array $ctx): array
    {
        $l = LieuModel::find((int)$ctx['params']['id']);
        return $l ? $this->json($l) : $this->error('Lieu introuvable', 404);
    }

    public function create(array $ctx): array
    {
        $d = $this->input();
        if ($m = $this->require($d, ['nom', 'coordonnees_gps'])) {
            return $this->error('Champs manquants : ' . implode(',', $m), 422);
        }
        return $this->json(['id_lieu' => LieuModel::create($d)], 201);
    }

    public function update(array $ctx): array
    {
        LieuModel::update((int)$ctx['params']['id'], $this->input());
        return $this->json(['ok' => true]);
    }

    public function delete(array $ctx): array
    {
        LieuModel::delete((int)$ctx['params']['id']);
        return $this->json(['ok' => true]);
    }

    public function distances(array $ctx): array { return $this->json(DistanceModel::all()); }

    public function createDistance(array $ctx): array
    {
        $d = $this->input();
        if ($m = $this->require($d, ['id_lieu', 'id_lieu_1', 'distance_metres'])) {
            return $this->error('Champs manquants', 422);
        }
        DistanceModel::create((int)$d['id_lieu'], (int)$d['id_lieu_1'], (int)$d['distance_metres']);
        return $this->json(['ok' => true], 201);
    }

    public function deleteDistance(array $ctx): array
    {
        DistanceModel::delete((int)$ctx['params']['a'], (int)$ctx['params']['b']);
        return $this->json(['ok' => true]);
    }
}
