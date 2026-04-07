<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:callable|array, middlewares:array}> */
    private array $routes = [];

    public function add(string $method, string $pattern, $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method'      => strtoupper($method),
            'pattern'     => $pattern,
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function get(string $p, $h, array $m = []): void    { $this->add('GET',    $p, $h, $m); }
    public function post(string $p, $h, array $m = []): void   { $this->add('POST',   $p, $h, $m); }
    public function put(string $p, $h, array $m = []): void    { $this->add('PUT',    $p, $h, $m); }
    public function delete(string $p, $h, array $m = []): void { $this->add('DELETE', $p, $h, $m); }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Strip /index.php prefix if present
        $uri = preg_replace('#^/index\.php#', '', $uri) ?? $uri;
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $regex = '#^' . preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $route['pattern']) . '$#';
            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $context = ['params' => $params, 'user' => null];

                foreach ($route['middlewares'] as $middleware) {
                    $context = $middleware($context);
                    if ($context === null) {
                        return; // middleware a déjà répondu
                    }
                }

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    echo json_encode($controller->$action($context));
                } else {
                    echo json_encode($handler($context));
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Not found', 'path' => $uri]);
    }
}
