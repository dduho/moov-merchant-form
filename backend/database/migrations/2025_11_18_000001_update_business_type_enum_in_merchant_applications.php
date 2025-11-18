<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum business_type pour ajouter les nouvelles valeurs
        DB::statement("ALTER TABLE merchant_applications MODIFY COLUMN business_type ENUM(
            'boulangerie',
            'entrepreneuriat',
            'secretariat-bureautique',
            'commerce-general',
            'coiffure',
            'vente-objets-arts',
            'informatique',
            'restaurant',
            'pret-a-porter',
            'vente-pieces-detachees',
            'directrice-societe',
            'btp',
            'elevage',
            'quincaillerie',
            'vente-pagnes',
            'lavage-sec',
            'vente-produits-vivriers',
            'vente-equipements-sportifs',
            'fabrication-reparation-chaussures',
            'graphiste-designer',
            'menuiserie-decoration',
            'artiste-plasticien',
            'transfert-argent',
            'location-appartements-meubles',
            'pharmacie',
            'hotel',
            'autre',
            'boutique',
            'station-service',
            'supermarche'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retour aux anciennes valeurs
        DB::statement("ALTER TABLE merchant_applications MODIFY COLUMN business_type ENUM(
            'boutique',
            'pharmacie',
            'station-service',
            'supermarche',
            'autre'
        ) NOT NULL");
    }
};
