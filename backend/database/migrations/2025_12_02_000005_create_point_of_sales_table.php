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
        Schema::create('point_of_sales', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Status
            $table->enum('status', ['pending', 'validated', 'rejected'])->default('pending');
            
            // Informations Dealer
            $table->string('numero')->nullable(); // N° séquentiel
            $table->string('dealer_name'); // NOM DU DEALER
            $table->string('numero_flooz')->nullable(); // NUMERO FLOOZ
            $table->string('shortcode')->nullable(); // SHORTCODE
            
            // Informations PDV
            $table->string('nom_point'); // NOM DU POINT
            $table->string('profil')->nullable(); // PROFIL
            $table->string('type_activite')->nullable(); // TYPE D'ACTIVITE
            
            // Informations du Gérant
            $table->string('firstname'); // PRENOM
            $table->string('lastname'); // NOM
            $table->date('date_of_birth')->nullable(); // DATE DE NAISSANCE
            $table->enum('gender', ['M', 'F'])->nullable(); // SEXE
            $table->string('id_description')->nullable(); // TYPE DE PIECE
            $table->string('id_number')->nullable(); // NUMERO DE PIECE
            $table->date('id_expiry_date')->nullable(); // DATE D'EXPIRATION
            $table->string('nationality')->default('Togolaise'); // NATIONALITE
            $table->string('profession')->nullable(); // PROFESSION
            
            // Localisation Hiérarchique
            $table->enum('region', ['MARITIME', 'PLATEAUX', 'CENTRALE', 'KARA', 'SAVANES']); // REGION
            $table->string('prefecture')->nullable(); // PREFECTURE
            $table->string('commune')->nullable(); // COMMUNE
            $table->string('canton')->nullable(); // CANTON
            $table->string('ville')->nullable(); // VILLE
            $table->string('quartier')->nullable(); // QUARTIER
            $table->text('localisation')->nullable(); // LOCALISATION - description textuelle
            
            // Coordonnées GPS
            $table->decimal('latitude', 10, 8); // LATITUDE
            $table->decimal('longitude', 11, 8); // LONGITUDE
            $table->decimal('gps_accuracy', 8, 2)->nullable(); // Précision GPS en mètres
            
            // Contacts
            $table->string('numero_proprietaire'); // NUMERO PROPRIETAIRE DU PDV
            $table->string('autre_contact')->nullable(); // AUTRE CONTACT DU PDV
            
            // Fiscalité
            $table->string('nif')->nullable(); // NIF
            $table->string('regime_fiscal')->nullable(); // REGIME FISCAL
            
            // Visibilité
            $table->string('support_visibilite')->nullable(); // Chevalet, Potence, Autocollant
            $table->enum('etat_support', ['BON', 'MAUVAIS'])->nullable(); // ETAT DU SUPPORT
            
            // Autres
            $table->string('numero_cagnt')->nullable(); // NUMERO CAGNT
            
            // Validation
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // UUID pour référence externe
            $table->uuid('uuid')->unique();
            $table->string('reference_number')->unique();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour les recherches
            $table->index(['organization_id', 'status']);
            $table->index(['region', 'prefecture']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_of_sales');
    }
};
