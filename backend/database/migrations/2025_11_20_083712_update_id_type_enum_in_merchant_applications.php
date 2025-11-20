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
        // Modifier la colonne id_type pour accepter plus de valeurs
        DB::statement("ALTER TABLE merchant_applications MODIFY COLUMN id_type ENUM('cni', 'passport', 'residence', 'elector', 'driving_license', 'foreign_id') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir aux 3 valeurs d'origine
        DB::statement("ALTER TABLE merchant_applications MODIFY COLUMN id_type ENUM('cni', 'passport', 'residence') NULL");
    }
};
