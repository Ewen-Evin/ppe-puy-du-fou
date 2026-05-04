<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Jwt;

final class AuthMiddleware
{
    /** @param string|null $requiredRole 'visiteur' | 'gestionnaire' | null */
    public static function handle(?string $requiredRole = null): callable
    {
        return function (array $context) use ($requiredRole): ?array {
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            $auth = $headers['Authorization']
                 ?? $headers['authorization']
                 ?? $_SERVER['HTTP_AUTHORIZATION']
                 ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                 ?? '';
            if (!preg_match('/Bearer\s+(.+)/', $auth, $m)) {
                http_response_code(401);
                echo json_encode(['error' => 'Missing token']);
                return null;
            }
            $payload = Jwt::decode($m[1]);
            if (!$payload) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid or expired token']);
                return null;
            }
            if ($requiredRole !== null && ($payload['role'] ?? '') !== $requiredRole) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                return null;
            }
            $context['user'] = $payload;
            return $context;
        };
    }
}
