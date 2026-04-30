<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $spectacles = $this->api->get('/api/spectacles');
        $lieux      = $this->api->get('/api/lieux');
        $jours      = $this->api->get('/api/jours');

        $this->view('dashboard', [
            'nbSpectacles' => $this->countItems($spectacles),
            'nbLieux'      => $this->countItems($lieux),
            'nbJours'      => $this->countItems($jours),
        ]);
    }
}
