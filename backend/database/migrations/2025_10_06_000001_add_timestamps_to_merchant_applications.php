<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('merchant_applications', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('merchant_applications', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('merchant_applications', 'processed_at')) {
                $table->timestamp('processed_at')->nullable();
            }
            if (!Schema::hasColumn('merchant_applications', 'business_type')) {
                $table->string('business_type')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('merchant_applications', function (Blueprint $table) {
            $table->dropColumn(['submitted_at', 'reviewed_at', 'processed_at', 'business_type']);
        });
    }
};