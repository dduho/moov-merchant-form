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
            // Ajouter la date d'expiration ANID après anid_number
            $table->date('anid_expiry_date')->nullable()->after('anid_number');
            // Ajouter la date d'expiration CFE après cfe_number
            $table->date('cfe_expiry_date')->nullable()->after('cfe_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            $table->dropColumn(['anid_expiry_date', 'cfe_expiry_date']);
        });
    }
};
