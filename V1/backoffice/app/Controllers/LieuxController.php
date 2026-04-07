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
        $this->api->post('/api/admin/lieux', $this->payload());
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
        $this->api->put('/api/admin/lieux/' . (int)$params['id'], $this->payload());
        $this->redirect('/lieux', 'Lieu mis à jour');
    }

    public function delete(array $params): void
    {
        $this->checkCsrf();
        $this->api->delete('/api/admin/lieux/' . (int)$params['id']);
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
