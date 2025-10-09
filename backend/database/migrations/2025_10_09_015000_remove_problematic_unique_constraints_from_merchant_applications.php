<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            // Supprimer les contraintes d'unicité problématiques
            
            // 1. Supprimer la contrainte unique sur 'phone' - plusieurs personnes peuvent partager le même téléphone
            $table->dropUnique(['phone']);
            
            // 2. Supprimer la contrainte unique sur 'email' - plusieurs personnes peuvent partager le même email (famille, entreprise)  
            $table->dropUnique(['email']);
            
            // 3. Supprimer la contrainte unique sur 'id_number' - dans certains cas, le même numéro peut être réutilisé
            $table->dropUnique(['id_number']);
            
            // Garder seulement les contraintes vraiment nécessaires :
            // - reference_number (reste unique car c'est notre clé métier)
            // - uuid (reste unique car c'est un identifiant technique)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            // Restaurer les contraintes si besoin (pour rollback)
            $table->unique('phone');
            $table->unique('email'); 
            $table->unique('id_number');
        });
    }
};
