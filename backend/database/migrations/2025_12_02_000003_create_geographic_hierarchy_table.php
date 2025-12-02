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
        Schema::create('geographic_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // region, prefecture, commune, canton, ville
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('geographic_hierarchy')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'parent_id']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geographic_hierarchy');
    }
};
