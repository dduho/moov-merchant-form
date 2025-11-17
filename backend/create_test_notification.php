<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel');

$user = App\Models\User::first();

if ($user) {
    $notification = new App\Models\Notification([
        'id' => Illuminate\Support\Str::uuid(),
        'type' => 'test_notification',
        'notifiable_type' => 'App\Models\User',
        'notifiable_id' => $user->id,
        'user_id' => $user->id,
        'data' => json_encode(['message' => 'Test notification for marking as read']),
        'read_at' => null,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    $notification->save();
    
    echo "Notification créée: " . $notification->id . PHP_EOL;
    echo "Pour l'utilisateur: " . $user->email . PHP_EOL;
    echo "Role: " . $user->role . PHP_EOL;
} else {
    echo "Aucun utilisateur trouvé" . PHP_EOL;
}