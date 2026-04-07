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
            'nbSpectacles' => is_array($spectacles) ? max(0, count($spectacles) - 1) : 0,
            'nbLieux'      => is_array($lieux)      ? max(0, count($lieux) - 1)      : 0,
            'nbJours'      => is_array($jours)      ? max(0, count($jours) - 1)      : 0,
        ]);
    }
}
