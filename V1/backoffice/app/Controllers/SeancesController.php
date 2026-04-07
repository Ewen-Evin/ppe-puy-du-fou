<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class SeancesController extends Controller
{
    public function index(): void
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        $items = $this->api->get('/api/seances?date=' . urlencode($date));
        $this->view('seances/index', ['items' => $items, 'date' => $date]);
    }

    public function create(): void
    {
        $spectacles = $this->api->get('/api/spectacles');
        $this->view('seances/form', ['item' => null, 'spectacles' => $spectacles]);
    }

    public function store(): void
    {
        $this->checkCsrf();
        $this->api->post('/api/admin/seances', $this->payload());
        $this->redirect('/seances?date=' . urlencode($_POST['date_seance'] ?? date('Y-m-d')), 'Séance créée');
    }

    public function delete(array $params): void
    {
        $this->checkCsrf();
        $this->api->delete('/api/admin/seances/' . (int)$params['id']);
        $this->redirect('/seances', 'Séance supprimée');
    }

    private function payload(): array
    {
        return [
            'date_seance'  => $_POST['date_seance'] ?? date('Y-m-d'),
            'heure_debut'  => $_POST['heure_debut'] ?? '00:00:00',
            'heure_fin'    => $_POST['heure_fin']   ?? '00:00:00',
            'id_spectacle' => (int)($_POST['id_spectacle'] ?? 0),
        ];
    }
}
