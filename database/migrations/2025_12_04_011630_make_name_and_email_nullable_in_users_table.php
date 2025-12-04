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
        // Rendre name et email nullable pour permettre la création de compte avec profil incomplet
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
        
        // Supprimer la contrainte unique sur email si elle existe (car on peut avoir plusieurs NULL)
        // Note: MySQL permet plusieurs NULL dans une colonne unique
        // Mais on doit gérer le cas où email peut être NULL
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre name et email comme requis
        // Note: Cela peut échouer si des utilisateurs ont des valeurs NULL
        DB::statement("ALTER TABLE users MODIFY COLUMN name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NOT NULL");
    }
};
