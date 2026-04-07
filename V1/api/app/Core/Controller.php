<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function input(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    protected function json($data, int $status = 200): array
    {
        http_response_code($status);
        return $data;
    }

    protected function error(string $message, int $status = 400): array
    {
        http_response_code($status);
        return ['error' => $message];
    }

    protected function require(array $data, array $fields): ?array
    {
        $missing = [];
        foreach ($fields as $f) {
            if (!isset($data[$f]) || $data[$f] === '') {
                $missing[] = $f;
            }
        }
        return $missing ? $missing : null;
    }
}
