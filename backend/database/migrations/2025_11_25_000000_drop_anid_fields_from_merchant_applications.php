<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            // Drop ANID columns if they exist
            if (Schema::hasColumn('merchant_applications', 'has_anid_card')) {
                $table->dropColumn('has_anid_card');
            }
            if (Schema::hasColumn('merchant_applications', 'anid_number')) {
                $table->dropColumn('anid_number');
            }
            if (Schema::hasColumn('merchant_applications', 'anid_expiry_date')) {
                $table->dropColumn('anid_expiry_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            // Recreate ANID columns (safe defaults)
            if (!Schema::hasColumn('merchant_applications', 'has_anid_card')) {
                $table->boolean('has_anid_card')->default(false)->index();
            }
            if (!Schema::hasColumn('merchant_applications', 'anid_number')) {
                $table->string('anid_number', 50)->nullable();
            }
            if (!Schema::hasColumn('merchant_applications', 'anid_expiry_date')) {
                $table->date('anid_expiry_date')->nullable();
            }
        });
    }
};
