<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Schedule::command('applications:sync-pending')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('applications:report --period=week')
    ->weeklyOn(1, '09:00');

Artisan::command('test:filter', function () {
    $commercial = \App\Models\User::where('username', 'commercial')->first();
    
    if (!$commercial) {
        $this->error('Commercial user not found');
        return;
    }
    
    // Charger les rôles
    $commercial->load('roles');
    
    $this->info('Commercial User ID: ' . $commercial->id);
    $this->info('Commercial username: ' . $commercial->username);
    $this->info('Commercial roles: ' . $commercial->roles->pluck('name')->implode(', '));
    
    $total = \App\Models\MerchantApplication::count();
    $commercialApps = \App\Models\MerchantApplication::where('user_id', $commercial->id)->count();
    $withUserId = \App\Models\MerchantApplication::whereNotNull('user_id')->count();
    
    $this->info('Total applications: ' . $total);
    $this->info('Commercial applications: ' . $commercialApps);
    $this->info('Applications with user_id: ' . $withUserId);
    
    // Afficher les 5 premières candidatures avec leur user_id
    $applications = \App\Models\MerchantApplication::limit(5)->get();
    $this->info('First 5 applications:');
    foreach ($applications as $app) {
        $this->line("ID: {$app->id}, user_id: {$app->user_id}, reference: {$app->reference_number}");
    }
});

Artisan::command('test:dashboard-recent', function () {
    $commercial = \App\Models\User::where('username', 'commercial')->with('roles')->first();
    
    if (!$commercial) {
        $this->error('Commercial user not found');
        return;
    }
    
    $this->info('Testing with user: ' . $commercial->username);
    $this->info('User roles: ' . $commercial->roles->pluck('name')->implode(', '));
    
    // Simuler une requête avec l'utilisateur commercial connecté
    $controller = new \App\Http\Controllers\DashboardController();
    
    // Créer une fausse requête avec l'utilisateur commercial
    $request = new \Illuminate\Http\Request();
    $request->setUserResolver(function () use ($commercial) {
        return $commercial;
    });
    
    try {
        $response = $controller->recent($request);
        $data = json_decode($response->getContent(), true);
        
        $this->info('Dashboard recent method results:');
        $this->info('Success: ' . ($data['success'] ? 'true' : 'false'));
        $this->info('Count: ' . $data['count']);
        $this->info('Applications returned: ' . count($data['data']));
        
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $index => $app) {
                $userId = $app['user_id'] ?? 'null';
                $this->line("App {$index}: ID={$app['id']}, user_id={$userId}, reference={$app['reference_number']}");
            }
        }
        
    } catch (\Exception $e) {
        $this->error('Error: ' . $e->getMessage());
        $this->error('Trace: ' . $e->getTraceAsString());
    }
});