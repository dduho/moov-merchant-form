<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_applications', function (Blueprint $table) {
            $table->id();
            
            // Informations personnelles
            $table->string('first_name'); // NOUVEAU
            $table->string('last_name'); // NOUVEAU
            $table->string('full_name')->index(); // Généré automatiquement
            $table->date('birth_date');
            $table->string('birth_place')->nullable(); // NOUVEAU
            $table->enum('gender', ['M', 'F'])->nullable(); // NOUVEAU
            $table->string('nationality')->nullable(); // NOUVEAU
            $table->string('phone', 20)->unique();
            $table->string('merchant_phone', 20)->nullable(); // NOUVEAU - Téléphone marchand
            $table->string('email')->nullable()->unique();
            $table->text('address');
            
            // Documents d'identité
            $table->enum('id_type', ['cni', 'passport', 'residence'])->nullable(); // NOUVEAU - Type de pièce
            $table->string('id_number', 50)->unique()->index();
            $table->date('id_expiry_date');
            $table->boolean('has_anid_card')->default(false)->index();
            $table->string('anid_number', 50)->nullable();
            $table->boolean('is_foreigner')->default(false)->index();
            
            // Informations commerciales
            $table->string('business_name')->index();
            $table->enum('business_type', [
                'boutique',
                'pharmacie',
                'station-service',
                'supermarche',
                'autre'
            ])->index();
            $table->json('business_phones')->nullable(); // NOUVEAU - Téléphones multiples
            $table->string('business_email')->nullable(); // NOUVEAU
            $table->text('business_address')->nullable(); // NOUVEAU
            $table->enum('usage_type', ['TRADER', 'MERC', 'TRADERWNIF', 'CORP'])->nullable(); // NOUVEAU
            $table->boolean('has_cfe')->default(false);
            $table->string('cfe_number', 50)->nullable();
            $table->boolean('has_nif')->default(false);
            $table->string('nif_number', 50)->nullable();
            
            // Localisation
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('location_accuracy', 8, 2)->nullable();
            $table->text('location_description')->nullable();
            $table->text('shop_address')->nullable(); // NOUVEAU
            $table->string('shop_city')->nullable(); // NOUVEAU
            
            // Signature et validation
            $table->longText('signature')->nullable();
            $table->boolean('accept_terms')->default(false);
            
            // Statut et workflow
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'needs_info',
                'archived'
            ])->default('pending')->index();
            
            $table->text('admin_notes')->nullable();
            $table->string('reference_number', 50)->unique()->index();
            
            // Métadonnées
            $table->timestamp('submitted_at')->useCurrent()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            
            // UUID
            $table->uuid('uuid')->unique()->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index composites pour performances
            $table->index(['status', 'created_at']);
            $table->index(['business_type', 'status']);
            $table->index(['submitted_at', 'status']);
            $table->index(['first_name', 'last_name']); // NOUVEAU
            $table->index('merchant_phone'); // NOUVEAU
            
            // Index normaux à la place de fulltext
            $table->index(['full_name', 'business_name', 'reference_number', 'phone'], 'ma_search_index');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('merchant_applications');
    }
};