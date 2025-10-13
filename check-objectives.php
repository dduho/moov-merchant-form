<?php

require __DIR__ . '/backend/vendor/autoload.php';

$app = require_once __DIR__ . '/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== User Objectives ===\n\n";

$objectives = App\Models\UserObjective::all();

echo "Total objectives: " . $objectives->count() . "\n\n";

foreach ($objectives as $obj) {
    echo "ID: {$obj->id}\n";
    echo "User ID: {$obj->user_id}\n";
    echo "Year: {$obj->target_year}\n";
    echo "Month: " . ($obj->target_month ?? 'ALL (yearly)') . "\n";
    echo "Monthly Target: {$obj->monthly_target}\n";
    echo "Yearly Target: {$obj->yearly_target}\n";
    echo "Active: " . ($obj->is_active ? 'Yes' : 'No') . "\n";
    echo str_repeat('-', 50) . "\n";
}

echo "\n=== Commercial Users ===\n\n";

$commercials = App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'commercial');
})->get();

foreach ($commercials as $user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    $objectivesCount = $user->objectives()->count();
    echo "Objectives count: {$objectivesCount}\n";
    echo str_repeat('-', 50) . "\n";
}
