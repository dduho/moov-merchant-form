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
            $table->enum('region', [
                'Maritime',
                'Plateaux',
                'Centrale',
                'Kara',
                'Savanes'
            ])->after('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};
