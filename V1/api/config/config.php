<?php
declare(strict_types=1);

return [
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'ppe_puy_du_fou',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],
    'jwt' => [
        // À remplacer en prod par une vraie variable d'environnement
        'secret'     => 'change-me-in-production-please',
        'algo'       => 'HS256',
        'expires_in' => 86400, // 24h
        'issuer'     => 'ppe-puy-du-fou-api',
    ],
];
