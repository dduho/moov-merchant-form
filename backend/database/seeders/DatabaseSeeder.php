<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MerchantApplication;
use App\Models\ApplicationDocument;
use Database\Seeders\UserSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les rôles
        $this->call(RoleSeeder::class);
        
        // Créer les utilisateurs par défaut
        $this->call(UserSeeder::class);
        
        // Créer la hiérarchie géographique (Régions, Préfectures, Communes du Togo)
        $this->call(GeographicHierarchySeeder::class);
        
        // (facultatif) repartir propre
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\ApplicationDocument::truncate();
        \App\Models\MerchantApplication::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Récupérer l'utilisateur commercial créé
        $commercial = \App\Models\User::where('username', 'commercial')->first();

        // 1) Créer quelques applications liées à l'utilisateur commercial
        MerchantApplication::factory()
            ->count(3)
            ->state(['user_id' => $commercial->id])
            ->create()
            ->each(function ($app) {
                // 2) Attacher 3 documents max, 1 par type avec sequence()
                ApplicationDocument::factory()
                    ->count(3)
                    ->sequence(
                        ['document_type' => 'id_card'],
                        ['document_type' => 'residence_card'],
                        ['document_type' => 'cfe_card'],
                    )
                    ->for($app, 'merchantApplication')
                    ->create();
            });

        // 3) Créer des applications sans utilisateur (soumissions anonymes)
        MerchantApplication::factory()
            ->count(7)
            ->create()
            ->each(function ($app) {
                // 2) Attacher 3 documents max, 1 par type avec sequence()
                ApplicationDocument::factory()
                    ->count(3)
                    ->sequence(
                        ['document_type' => 'id_card'],
                        ['document_type' => 'residence_card'],
                        ['document_type' => 'cfe_card'],
                    )
                    ->for($app, 'merchantApplication') // si ta relation s'appelle merchantApplication()
                    // sinon ->state(fn() => ['merchant_application_id' => $app->id])
                    ->create();
            });
    }
}
