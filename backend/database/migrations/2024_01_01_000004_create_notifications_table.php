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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 50); // 'application_approved', 'application_rejected', etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Données supplémentaires (ID candidature, etc.)
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('action_url')->nullable(); // URL vers l'action liée
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};