<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseCleanupSeeder extends Seeder
{
    /**
     * Vider la base de données en gardant uniquement l'utilisateur admin
     */
    public function run(): void
    {
        // Désactiver les contraintes de clés étrangères temporairement
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        echo "🗑️  Suppression des candidatures et documents...\n";
        DB::table('application_documents')->truncate();
        DB::table('merchant_applications')->truncate();

        echo "🗑️  Suppression des notifications...\n";
        DB::table('notifications')->truncate();

        echo "🗑️  Suppression des objectifs utilisateurs...\n";
        DB::table('user_objectives')->truncate();

        echo "🗑️  Suppression des jobs et caches...\n";
        DB::table('jobs')->truncate();
        DB::table('failed_jobs')->truncate();
        DB::table('cache')->truncate();
        DB::table('cache_locks')->truncate();
        DB::table('sessions')->truncate();

        echo "🗑️  Nettoyage des utilisateurs (conservation de l'admin)...\n";
        
        // Récupérer l'ID de l'utilisateur admin
        $adminUser = User::where('email', 'admin@moov.com')->first();
        
        if ($adminUser) {
            // Supprimer tous les utilisateurs sauf l'admin
            User::where('id', '!=', $adminUser->id)->delete();
            
            // Nettoyer les relations de l'admin (objectifs, etc.)
            DB::table('user_objectives')->where('user_id', $adminUser->id)->delete();
            
            echo "✅ Utilisateur admin conservé (ID: {$adminUser->id})\n";
        } else {
            echo "⚠️  Aucun utilisateur admin trouvé!\n";
        }

        // Réactiver les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "\n✨ Nettoyage terminé!\n";
    }
}
