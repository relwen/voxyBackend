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
        Schema::create('rubrique_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('nom'); // Nom de la section (ex: "Messe de Noël", "Kyrié", etc.)
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
            
            // Index pour éviter les doublons de noms dans une même rubrique
            $table->unique(['category_id', 'nom']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubrique_sections');
    }
};
