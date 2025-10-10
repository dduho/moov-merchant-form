<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://10.80.3.159:3000', // Votre IP locale
        'http://*:3000' // Accepte toutes les IPs sur le port 3000
    ],
    'allowed_origins_patterns' => [
        '/^http:\/\/.*:3000$/' // Pattern pour accepter toutes les IPs sur le port 3000
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];