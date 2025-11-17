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
        Schema::create('messes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Messe St Gabriel"
            $table->text('description')->nullable();
            $table->string('color')->nullable(); // Couleur pour l'affichage
            $table->string('icon')->nullable(); // Icône pour l'affichage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messes');
    }
};