<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter le rôle "personnel" s'il n'existe pas
        if (!Role::where('slug', 'personnel')->exists()) {
            Role::create([
                'name' => 'Personnel',
                'slug' => 'personnel',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer le rôle "personnel"
        Role::where('slug', 'personnel')->delete();
    }
};
