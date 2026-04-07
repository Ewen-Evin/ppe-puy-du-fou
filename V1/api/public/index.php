<?php
/**
 * PPE Puy du Fou - API REST
 * Point d'entrée unique
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Autoload simple PSR-4 maison
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require dirname(__DIR__) . '/config/config.php';

use App\Core\Router;

try {
    $router = new Router();
    require dirname(__DIR__) . '/app/Routes/routes.php';
    $router->dispatch();
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => 'Internal server error',
        'message' => $e->getMessage(),
    ]);
}
