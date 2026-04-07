<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected ApiClient $api;

    public function __construct()
    {
        $this->api = new ApiClient();
    }

    protected function view(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $url = fn(string $p) => Router::url($p);
        $csrf = $_SESSION['csrf'] ?? '';
        $user = $_SESSION['user'] ?? null;
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $contentFile = dirname(__DIR__) . '/Views/' . $template . '.php';
        ob_start();
        require $contentFile;
        $content = ob_get_clean();

        require dirname(__DIR__) . '/Views/layout.php';
    }

    protected function redirect(string $path, ?string $flash = null): void
    {
        if ($flash !== null) $_SESSION['flash'] = $flash;
        header('Location: ' . Router::url($path));
        exit;
    }

    protected function checkCsrf(): void
    {
        if (($_POST['_csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
            http_response_code(403);
            exit('CSRF token invalide');
        }
    }
}
