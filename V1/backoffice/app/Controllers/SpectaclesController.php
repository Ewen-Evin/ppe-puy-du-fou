<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class SpectaclesController extends Controller
{
    public function index(): void
    {
        $items = $this->api->get('/api/spectacles');
        $this->view('spectacles/index', ['items' => $items]);
    }

    public function create(): void
    {
        $lieux = $this->api->get('/api/lieux');
        $this->view('spectacles/form', ['item' => null, 'lieux' => $lieux]);
    }

    public function store(): void
    {
        $this->checkCsrf();
        $this->api->post('/api/admin/spectacles', $this->payload());
        $this->redirect('/spectacles', 'Spectacle créé');
    }

    public function edit(array $params): void
    {
        $item = $this->api->get('/api/spectacles/' . (int)$params['id']);
        $lieux = $this->api->get('/api/lieux');
        $this->view('spectacles/form', ['item' => $item, 'lieux' => $lieux]);
    }

    public function update(array $params): void
    {
        $this->checkCsrf();
        $this->api->put('/api/admin/spectacles/' . (int)$params['id'], $this->payload());
        $this->redirect('/spectacles', 'Spectacle mis à jour');
    }

    public function delete(array $params): void
    {
        $this->checkCsrf();
        $this->api->delete('/api/admin/spectacles/' . (int)$params['id']);
        $this->redirect('/spectacles', 'Spectacle supprimé');
    }

    private function payload(): array
    {
        return [
            'libelle'         => trim($_POST['libelle'] ?? ''),
            'description'     => trim($_POST['description'] ?? ''),
            'duree_spectacle' => $_POST['duree_spectacle'] ?? '00:30:00',
            'duree_attente'   => $_POST['duree_attente']   ?? '00:00:00',
            'id_lieu'         => (int)($_POST['id_lieu'] ?? 0),
        ];
    }
}
