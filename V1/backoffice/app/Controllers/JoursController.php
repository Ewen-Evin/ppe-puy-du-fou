<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class JoursController extends Controller
{
    public function index(): void
    {
        $items = $this->api->get('/api/jours');
        $this->view('jours/index', ['items' => $items]);
    }

    public function store(): void
    {
        $this->checkCsrf();
        $this->api->post('/api/admin/jours', [
            'id_jours'        => $_POST['id_jours'] ?? '',
            'heure_ouverture' => $_POST['heure_ouverture'] ?? '09:00:00',
            'heure_fermeture' => $_POST['heure_fermeture'] ?? '19:00:00',
        ]);
        $this->redirect('/jours', 'Jour enregistré');
    }

    public function delete(array $params): void
    {
        $this->checkCsrf();
        $this->api->delete('/api/admin/jours/' . urlencode($params['date']));
        $this->redirect('/jours', 'Jour supprimé');
    }
}
