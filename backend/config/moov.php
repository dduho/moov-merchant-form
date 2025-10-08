<?php

return [
    // Administration
    'admin_email' => env('MOOV_ADMIN_EMAIL', 'admin@moovmoney.com'),
    'admin_phone' => env('MOOV_ADMIN_PHONE', '+22891000000'),
    
    // SMS/Notifications
    'sms_api_url' => env('MOOV_SMS_API_URL'),
    'sms_api_key' => env('MOOV_SMS_API_KEY'),
    
    // Fichiers
    'max_file_size' => env('MOOV_MAX_FILE_SIZE', 5120), // KB
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
    
    // CORS
    'allowed_origins' => explode(',', env('MOOV_ALLOWED_ORIGINS', 'http://localhost:5173')),
    
    // Rate limiting
    'api_rate_limit' => env('API_RATE_LIMIT', 60),
    'api_rate_decay' => env('API_RATE_DECAY', 1),
];