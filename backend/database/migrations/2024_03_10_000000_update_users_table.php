<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // On garde email, password et on ajoute les nouveaux champs
            $table->dropColumn('name');
            
            // Ajout des nouvelles colonnes
            $table->enum('role', ['admin', 'commercial'])->default('commercial')->after('id');
            $table->string('first_name')->after('email');
            $table->string('last_name')->after('first_name');
            $table->string('phone')->nullable()->after('last_name');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'first_name', 'last_name', 'phone']);
            $table->string('name')->nullable();
        });
    }
}