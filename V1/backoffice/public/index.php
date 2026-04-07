<?php
declare(strict_types=1);

session_start();

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $base   = dirname(__DIR__) . '/app/';
    if (strpos($class, $prefix) !== 0) return;
    $file = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

$config = require dirname(__DIR__) . '/config/config.php';
$GLOBALS['config'] = $config;

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

use App\Core\Router;

try {
    $router = new Router();
    require dirname(__DIR__) . '/app/Routes/routes.php';
    $router->dispatch();
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre>Erreur: ' . htmlspecialchars($e->getMessage()) . '</pre>';
}
