<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class DistancesController extends Controller
{
    public function index(): void
    {
        $items = $this->api->get('/api/distances');
        $lieux = $this->api->get('/api/lieux');
        $byId  = [];
        foreach ($lieux as $l) {
            if (is_array($l) && isset($l['id_lieu'])) $byId[(int)$l['id_lieu']] = $l['nom'];
        }
        $this->view('distances/index', ['items' => $items, 'lieux' => $lieux, 'byId' => $byId]);
    }

    public function store(): void
    {
        $this->checkCsrf();
        $resp = $this->api->post('/api/admin/distances', [
            'id_lieu'         => (int)($_POST['id_lieu'] ?? 0),
            'id_lieu_1'       => (int)($_POST['id_lieu_1'] ?? 0),
            'distance_metres' => (int)($_POST['distance_metres'] ?? 0),
        ]);
        if (!$this->isOk($resp)) {
            $this->redirect('/distances', $this->apiError($resp), 'danger');
            return;
        }
        $this->redirect('/distances', 'Distance créée');
    }

    public function delete(array $params): void
    {
        $this->checkCsrf();
        $resp = $this->api->delete('/api/admin/distances/' . (int)$params['a'] . '/' . (int)$params['b']);
        if (!$this->isOk($resp)) {
            $this->redirect('/distances', $this->apiError($resp), 'danger');
            return;
        }
        $this->redirect('/distances', 'Distance supprimée');
    }
}
