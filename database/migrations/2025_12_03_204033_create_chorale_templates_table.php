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
        Schema::create('chorale_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du template (ex: "Base Chorale", "Chorale Classique", etc.)
            $table->text('description')->nullable();
            $table->json('default_pupitres')->nullable(); // Liste des pupitres par défaut
            $table->json('default_categories')->nullable(); // Liste des catégories par défaut
            $table->boolean('is_system')->default(false); // Si c'est un template système (non modifiable)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chorale_templates');
    }
};
