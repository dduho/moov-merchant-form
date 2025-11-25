<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'carte_anid' to the id_type enum so the application can store ANID as an id_type
        DB::statement("ALTER TABLE merchant_applications MODIFY COLUMN id_type ENUM('cni', 'passport', 'residence', 'elector', 'driving_license', 'foreign_id', 'carte_anid') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'carte_anid' from the enum (revert to previous set)
        DB::statement("ALTER TABLE merchant_applications MODIFY COLUMN id_type ENUM('cni', 'passport', 'residence', 'elector', 'driving_license', 'foreign_id') NULL");
    }
};
