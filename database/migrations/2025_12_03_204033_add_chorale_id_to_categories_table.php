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
        Schema::table('categories', function (Blueprint $table) {
            // Rendre le nom non unique d'abord (car on va permettre les doublons par chorale)
            $table->dropUnique(['name']);
            
            // Ajouter chorale_id (nullable pour les catégories globales)
            $table->foreignId('chorale_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            
            // Recréer l'index unique pour name + chorale_id (null = global)
            $table->unique(['name', 'chorale_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name', 'chorale_id']);
            $table->dropForeign(['chorale_id']);
            $table->dropColumn('chorale_id');
            $table->unique(['name']);
        });
    }
};
