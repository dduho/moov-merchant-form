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
            // Champs pour l'approbation
            $table->timestamp('approved_at')->nullable()->after('reviewed_by');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            
            // Champs pour le rejet
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('rejected_at');
            $table->text('rejected_reason')->nullable()->after('rejected_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn([
                'approved_at',
                'approved_by', 
                'rejected_at',
                'rejected_by',
                'rejected_reason'
            ]);
        });
    }
};
