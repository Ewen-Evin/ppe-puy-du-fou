<?php
declare(strict_types=1);

namespace App\Core;

final class ApiClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim($GLOBALS['config']['api_base_url'], '/');
    }

    public function get(string $path): array    { return $this->call('GET',    $path); }
    public function post(string $path, array $body): array   { return $this->call('POST',   $path, $body); }
    public function put(string $path, array $body): array    { return $this->call('PUT',    $path, $body); }
    public function delete(string $path): array { return $this->call('DELETE', $path); }

    private function call(string $method, string $path, ?array $body = null): array
    {
        $ch = curl_init($this->baseUrl . $path);
        $headers = ['Accept: application/json', 'Content-Type: application/json'];
        if (!empty($_SESSION['token'])) {
            $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 10,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false) {
            return ['_status' => 0, '_error' => $err];
        }
        $data = json_decode($resp, true) ?? [];
        $data['_status'] = $code;
        return $data;
    }
}
