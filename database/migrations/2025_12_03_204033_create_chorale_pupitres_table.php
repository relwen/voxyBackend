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
        Schema::create('chorale_pupitres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chorale_id')->constrained()->onDelete('cascade');
            $table->string('nom'); // Nom du pupitre (ex: Soprane, Ténor, etc.)
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable(); // Couleur pour l'affichage
            $table->string('icon', 50)->nullable(); // Icône pour l'affichage
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->boolean('is_default')->default(false); // Si c'est le pupitre par défaut (Tutti)
            $table->timestamps();
            
            // Index pour éviter les doublons de noms dans une même chorale
            $table->unique(['chorale_id', 'nom']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chorale_pupitres');
    }
};
