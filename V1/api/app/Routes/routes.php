<?php
/** @var \App\Core\Router $router */

use App\Controllers\AuthController;
use App\Controllers\JoursController;
use App\Controllers\LieuController;
use App\Controllers\SeanceController;
use App\Controllers\SpectacleController;
use App\Controllers\VisiteController;
use App\Middlewares\AuthMiddleware;

$auth    = AuthMiddleware::handle();
$visiteur = AuthMiddleware::handle('visiteur');
$gestion  = AuthMiddleware::handle('gestionnaire');

// --- Public ---
$router->get('/api/health', fn($ctx) => ['status' => 'ok']);

// --- Auth ---
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login',    [AuthController::class, 'login']);
$router->get ('/api/auth/me',       [AuthController::class, 'me'],            [$auth]);
$router->put ('/api/auth/vitesse',  [AuthController::class, 'updateVitesse'], [$auth]);

// --- Lecture publique (visiteur ou anonyme) ---
$router->get('/api/spectacles',        [SpectacleController::class, 'index']);
$router->get('/api/spectacles/{id}',   [SpectacleController::class, 'show']);
$router->get('/api/lieux',             [LieuController::class, 'index']);
$router->get('/api/lieux/{id}',        [LieuController::class, 'show']);
$router->get('/api/distances',         [LieuController::class, 'distances']);
$router->get('/api/seances',                  [SeanceController::class, 'index']);
$router->get('/api/spectacles/{id}/seances',  [SeanceController::class, 'bySpectacle']);
$router->get('/api/jours',             [JoursController::class, 'index']);
$router->get('/api/jours/{date}',      [JoursController::class, 'show']);

// --- Visiteur ---
$router->post  ('/api/parcours/preview',         [VisiteController::class, 'preview'],      [$visiteur]);
$router->post  ('/api/visites',                  [VisiteController::class, 'create'],       [$visiteur]);
$router->get   ('/api/visites',                  [VisiteController::class, 'index'],        [$visiteur]);
$router->get   ('/api/visites/{id}/parcours',    [VisiteController::class, 'parcours'],     [$visiteur]);
$router->get   ('/api/visites/{id}/carte',       [VisiteController::class, 'carte'],        [$visiteur]);
$router->put   ('/api/visites/{id}/favori',      [VisiteController::class, 'updateFavori'], [$visiteur]);
$router->delete('/api/visites/{id}',             [VisiteController::class, 'delete'],       [$visiteur]);

// --- Gestionnaire ---
$router->post  ('/api/admin/spectacles',       [SpectacleController::class, 'create'], [$gestion]);
$router->put   ('/api/admin/spectacles/{id}',  [SpectacleController::class, 'update'], [$gestion]);
$router->delete('/api/admin/spectacles/{id}',  [SpectacleController::class, 'delete'], [$gestion]);

$router->post  ('/api/admin/lieux',            [LieuController::class, 'create'], [$gestion]);
$router->put   ('/api/admin/lieux/{id}',       [LieuController::class, 'update'], [$gestion]);
$router->delete('/api/admin/lieux/{id}',       [LieuController::class, 'delete'], [$gestion]);

$router->post  ('/api/admin/distances',           [LieuController::class, 'createDistance'], [$gestion]);
$router->delete('/api/admin/distances/{a}/{b}',   [LieuController::class, 'deleteDistance'], [$gestion]);

$router->post  ('/api/admin/seances',          [SeanceController::class, 'create'], [$gestion]);
$router->put   ('/api/admin/seances/{id}',     [SeanceController::class, 'update'], [$gestion]);
$router->delete('/api/admin/seances/{id}',     [SeanceController::class, 'delete'], [$gestion]);

$router->post  ('/api/admin/jours',            [JoursController::class, 'upsert'], [$gestion]);
$router->delete('/api/admin/jours/{date}',     [JoursController::class, 'delete'], [$gestion]);
