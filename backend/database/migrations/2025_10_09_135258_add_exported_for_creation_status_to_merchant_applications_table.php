<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier la colonne status pour ajouter le nouveau statut
        DB::statement("ALTER TABLE merchant_applications MODIFY status ENUM('pending', 'under_review', 'approved', 'rejected', 'needs_info', 'archived', 'exported_for_creation') NOT NULL DEFAULT 'pending'");
        
        // Ajouter un index sur le nouveau statut pour les performances
        Schema::table('merchant_applications', function (Blueprint $table) {
            $table->index(['status', 'updated_at'], 'ma_status_updated_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les candidatures exportées à approuvées avant de supprimer le statut
        DB::table('merchant_applications')
            ->where('status', 'exported_for_creation')
            ->update(['status' => 'approved']);
        
        // Supprimer l'index créé
        Schema::table('merchant_applications', function (Blueprint $table) {
            $table->dropIndex('ma_status_updated_idx');
        });
        
        // Revenir à l'ancienne définition de l'enum
        DB::statement("ALTER TABLE merchant_applications MODIFY status ENUM('pending', 'under_review', 'approved', 'rejected', 'needs_info', 'archived') NOT NULL DEFAULT 'pending'");
    }
};
