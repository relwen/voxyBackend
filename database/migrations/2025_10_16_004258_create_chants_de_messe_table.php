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
        Schema::create('chants_de_messe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('messe_sections')->onDelete('cascade');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chants_de_messe');
    }
};