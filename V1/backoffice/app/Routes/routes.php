<?php
/** @var \App\Core\Router $router */

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DistancesController;
use App\Controllers\JoursController;
use App\Controllers\LieuxController;
use App\Controllers\SeancesController;
use App\Controllers\SpectaclesController;

// Auth (publiques)
$router->get ('/login',  [AuthController::class, 'showLogin'], false);
$router->post('/login',  [AuthController::class, 'login'],     false);
$router->get ('/logout', [AuthController::class, 'logout'],    false);

// Dashboard
$router->get('/', [DashboardController::class, 'index']);

// Spectacles
$router->get ('/spectacles',                  [SpectaclesController::class, 'index']);
$router->get ('/spectacles/new',              [SpectaclesController::class, 'create']);
$router->post('/spectacles',                  [SpectaclesController::class, 'store']);
$router->get ('/spectacles/{id}/edit',        [SpectaclesController::class, 'edit']);
$router->post('/spectacles/{id}/edit',        [SpectaclesController::class, 'update']);
$router->post('/spectacles/{id}/delete',      [SpectaclesController::class, 'delete']);

// Lieux
$router->get ('/lieux',                  [LieuxController::class, 'index']);
$router->get ('/lieux/new',              [LieuxController::class, 'create']);
$router->post('/lieux',                  [LieuxController::class, 'store']);
$router->get ('/lieux/{id}/edit',        [LieuxController::class, 'edit']);
$router->post('/lieux/{id}/edit',        [LieuxController::class, 'update']);
$router->post('/lieux/{id}/delete',      [LieuxController::class, 'delete']);

// Distances
$router->get ('/distances',                  [DistancesController::class, 'index']);
$router->post('/distances',                  [DistancesController::class, 'store']);
$router->post('/distances/{a}/{b}/delete',   [DistancesController::class, 'delete']);

// Séances
$router->get ('/seances',                  [SeancesController::class, 'index']);
$router->get ('/seances/new',              [SeancesController::class, 'create']);
$router->post('/seances',                  [SeancesController::class, 'store']);
$router->post('/seances/{id}/delete',      [SeancesController::class, 'delete']);

// Jours
$router->get ('/jours',                  [JoursController::class, 'index']);
$router->post('/jours',                  [JoursController::class, 'store']);
$router->post('/jours/{date}/delete',    [JoursController::class, 'delete']);
