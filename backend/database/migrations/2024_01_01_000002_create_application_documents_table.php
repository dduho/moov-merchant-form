<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            
            // Relation avec candidature
            $table->foreignId('merchant_application_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            // Type de document
            $table->enum('document_type', [
                'id_card',
                'anid_card',
                'residence_card',
                'cfe_card',
                'nif_document',
                'business_license',
                'other'
            ])->index('ad_type_idx'); // 🔹 nom court

            // Informations du fichier
            $table->string('original_name');
            $table->string('file_name')->unique('ad_file_name_unq'); // 🔹 nom court
            $table->string('file_path');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            
            // Sécurité et intégrité
            $table->string('hash_sha256', 64)->nullable()->index('ad_sha256_idx'); // 🔹 nom court
            $table->string('hash_md5', 32)->nullable();
            $table->ipAddress('upload_ip')->nullable();
            $table->text('description')->nullable();
            
            // Vérification administrative
            $table->boolean('is_verified')->default(false)->index('ad_verified_flag_idx'); // 🔹 nom court
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->uuid('uuid')->unique('ad_uuid_unq')->nullable(); // 🔹 nom court
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index composés (🔹 noms courts)
            $table->index(['merchant_application_id', 'document_type', 'deleted_at'], 'ad_mad_type_del_idx');
            $table->index(['is_verified', 'verified_at'], 'ad_verified_at_idx');

            // Unique composé (🔹 nom court)
            $table->unique(['merchant_application_id', 'document_type'], 'ad_mad_type_unq');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
