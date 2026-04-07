<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, array $handler, bool $auth = true): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'auth');
    }

    public function get(string $p, array $h, bool $a = true): void  { $this->add('GET',  $p, $h, $a); }
    public function post(string $p, array $h, bool $a = true): void { $this->add('POST', $p, $h, $a); }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        // Strip script directory if present
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        if ($base && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        if ($uri === '' || $uri === false) $uri = '/';

        foreach ($this->routes as $r) {
            if ($r['method'] !== $method) continue;
            $regex = '#^' . preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $r['path']) . '$#';
            if (preg_match($regex, $uri, $m)) {
                if ($r['auth'] && empty($_SESSION['token'])) {
                    header('Location: ' . self::url('/login'));
                    return;
                }
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                [$class, $action] = $r['handler'];
                $controller = new $class();
                $controller->$action($params);
                return;
            }
        }
        http_response_code(404);
        echo '404 - Page introuvable';
    }

    public static function url(string $path): string
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        return ($base ?: '') . $path;
    }
}
