<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->boot();

$users = App\Models\User::all();
echo "Nombre d'utilisateurs: " . $users->count() . PHP_EOL;

foreach ($users as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Role: {$user->role}" . PHP_EOL;
}