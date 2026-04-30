<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class LieuxController extends Controller
{
    public function index(): void
    {
        $items = $this->api->get('/api/lieux');
        $this->view('lieux/index', ['items' => $items]);
    }

    public function create(): void
    {
        $this->view('lieux/form', ['item' => null]);
    }

    public function store(): void
    {
        $this->checkCsrf();
        $resp = $this->api->post('/api/admin/lieux', $this->payload());
        if (!$this->isOk($resp)) {
            $this->redirect('/lieux/new', $this->apiError($resp), 'danger');
            return;
        }
        $this->redirect('/lieux', 'Lieu créé');
    }

    public function edit(array $params): void
    {
        $item = $this->api->get('/api/lieux/' . (int)$params['id']);
        $this->view('lieux/form', ['item' => $item]);
    }

    public function update(array $params): void
    {
        $this->checkCsrf();
        $id = (int)$params['id'];
        $resp = $this->api->put('/api/admin/lieux/' . $id, $this->payload());
        if (!$this->isOk($resp)) {
            $this->redirect('/lieux/' . $id . '/edit', $this->apiError($resp), 'danger');
            return;
        }
        $this->redirect('/lieux', 'Lieu mis à jour');
    }

    public function delete(array $params): void
    {
        $this->checkCsrf();
        $resp = $this->api->delete('/api/admin/lieux/' . (int)$params['id']);
        if (!$this->isOk($resp)) {
            $this->redirect('/lieux', $this->apiError($resp), 'danger');
            return;
        }
        $this->redirect('/lieux', 'Lieu supprimé');
    }

    private function payload(): array
    {
        return [
            'nom'             => trim($_POST['nom'] ?? ''),
            'coordonnees_gps' => trim($_POST['coordonnees_gps'] ?? ''),
        ];
    }
}
