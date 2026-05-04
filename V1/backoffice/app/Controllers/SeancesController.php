<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class SeancesController extends Controller
{
    public function index(): void
    {
        $date = $_GET['date'] ?? null;
        $path = $date ? '/api/seances?date=' . urlencode($date) : '/api/seances';
        $items      = $this->api->get($path);
        $spectacles = $this->api->get('/api/spectacles');
        $jours      = $this->api->get('/api/jours');
        $this->view('seances/index', [
            'items'      => $items,
            'spectacles' => $spectacles,
            'jours'      => $jours,
            'date'       => $date ?? '',
        ]);
    }

    public function create(): void
    {
        $spectacles = $this->api->get('/api/spectacles');
        $jours      = $this->api->get('/api/jours');
        $this->view('seances/form', ['item' => null, 'spectacles' => $spectacles, 'jours' => $jours]);
    }

    public function store(): void
    {
        $this->checkCsrf();
        $resp = $this->api->post('/api/admin/seances', $this->payload());
        if (!$this->isOk($resp)) {
            $this->redirect('/seances/new', $this->apiError($resp), 'danger');
            return;
        }
        $this->redirect('/seances?date=' . urlencode($_POST['date_seance'] ?? ''), 'Seance creee');
    }

    public function delete(array $params): void
    {
        $this->checkCsrf();
        $resp = $this->api->delete('/api/admin/seances/' . (int)$params['id']);
        if (!$this->isOk($resp)) {
            $this->redirect('/seances', $this->apiError($resp), 'danger');
            return;
        }
        $this->redirect('/seances', 'Seance supprimee');
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
