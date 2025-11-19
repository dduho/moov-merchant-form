<?php

return [
    'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://10.80.3.159:3000', // Votre IP locale
        'http://10.80.3.159:3001',
        'http://10.80.3.159:5173',
        'http://10.80.7.160:3001', // Autre IP réseau
        'http://10.80.16.51', // IP serveur de production
        'http://10.80.16.51:80',
        'http://merch.moov-africa.tg', // Domaine de production HTTP
        'https://merch.moov-africa.tg', // Domaine de production HTTPS (pour plus tard)
    ],
    'allowed_origins_patterns' => [
        '/^http:\/\/.*:3000$/', // Pattern pour accepter toutes les IPs sur le port 3000
        '/^http:\/\/.*:5173$/', // Pattern pour accepter toutes les IPs sur le port 5173
        '/^http:\/\/10\.80\.16\.51(:\d+)?$/', // Pattern pour le serveur de production
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];