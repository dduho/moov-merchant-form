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
        // Mettre merchant_phone à NULL où il est identique à phone
        // Cela corrige les anciennes candidatures où merchant_phone était automatiquement rempli avec phone
        DB::table('merchant_applications')
            ->whereColumn('merchant_phone', '=', 'phone')
            ->update(['merchant_phone' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne rien faire lors du rollback
        // On ne peut pas restaurer les valeurs originales car on ne les a pas sauvegardées
    }
};
