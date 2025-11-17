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
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Kyrié", "Gloria", "Agnus Dei"
            $table->text('description')->nullable();
            $table->string('order_position')->nullable(); // Pour ordonner les sections (1, 2, 3...)
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Lien vers la catégorie (ex: "Messe St Gabriel")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};