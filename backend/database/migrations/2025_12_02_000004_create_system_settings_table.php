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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, float, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'proximity_alert_distance',
                'value' => '300',
                'type' => 'integer',
                'description' => 'Distance en mètres pour les alertes de proximité entre PDV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'required_gps_accuracy',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Précision GPS maximale requise en mètres',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_reject_low_accuracy',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Rejeter automatiquement les PDV avec une précision GPS insuffisante',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
