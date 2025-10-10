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
        Schema::table('user_objectives', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère d'abord
            $table->dropForeign(['user_id']);
            
            // Supprimer l'ancienne contrainte unique
            $table->dropUnique(['user_id', 'target_year', 'target_month']);
            
            // Modifier user_id pour être nullable (NULL = objectif global par défaut)
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Rendre target_month obligatoire (pas nullable)
            $table->integer('target_month')->nullable(false)->change();
            
            // Recréer la contrainte de clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Recréer la contrainte unique
            $table->unique(['user_id', 'target_year', 'target_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_objectives', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère
            $table->dropForeign(['user_id']);
            
            // Supprimer la contrainte unique
            $table->dropUnique(['user_id', 'target_year', 'target_month']);
            
            // Remettre user_id comme obligatoire
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            // Recréer la contrainte de clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Recréer la contrainte unique
            $table->unique(['user_id', 'target_year', 'target_month']);
        });
    }
};
