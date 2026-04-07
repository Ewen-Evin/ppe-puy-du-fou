<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Implémentation HS256 minimale - sans dépendance externe.
 */
final class Jwt
{
    private static function config(): array
    {
        $cfg = require dirname(__DIR__, 2) . '/config/config.php';
        return $cfg['jwt'];
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64dec(string $data): string
    {
        $r = strlen($data) % 4;
        if ($r) {
            $data .= str_repeat('=', 4 - $r);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }

    public static function encode(array $payload): string
    {
        $cfg = self::config();
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $payload['iss'] = $cfg['issuer'];
        $payload['iat'] = time();
        $payload['exp'] = time() + (int)$cfg['expires_in'];

        $h = self::b64(json_encode($header));
        $p = self::b64(json_encode($payload));
        $sig = hash_hmac('sha256', "$h.$p", $cfg['secret'], true);
        return $h . '.' . $p . '.' . self::b64($sig);
    }

    public static function decode(string $token): ?array
    {
        $cfg = self::config();
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$h, $p, $s] = $parts;
        $expected = self::b64(hash_hmac('sha256', "$h.$p", $cfg['secret'], true));
        if (!hash_equals($expected, $s)) {
            return null;
        }
        $payload = json_decode(self::b64dec($p), true);
        if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            return null;
        }
        return $payload;
    }
}
