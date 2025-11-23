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
        // Modifier la colonne status pour ajouter le nouveau statut exported_for_update
        DB::statement("ALTER TABLE merchant_applications MODIFY status ENUM('pending', 'under_review', 'approved', 'rejected', 'needs_info', 'archived', 'exported_for_creation', 'exported_for_update') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les candidatures exported_for_update à exported_for_creation avant de supprimer le statut
        DB::table('merchant_applications')
            ->where('status', 'exported_for_update')
            ->update(['status' => 'exported_for_creation']);

        // Revenir à l'ancienne définition de l'enum
        DB::statement("ALTER TABLE merchant_applications MODIFY status ENUM('pending', 'under_review', 'approved', 'rejected', 'needs_info', 'archived', 'exported_for_creation') NOT NULL DEFAULT 'pending'");
    }
};
