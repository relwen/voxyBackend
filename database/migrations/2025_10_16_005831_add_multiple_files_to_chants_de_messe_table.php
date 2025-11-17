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
        Schema::table('chants_de_messe', function (Blueprint $table) {
            // Ajouter des colonnes pour les fichiers multiples
            $table->json('audio_files')->nullable()->after('audio_path');
            $table->json('pdf_files')->nullable()->after('pdf_path');
            $table->json('image_files')->nullable()->after('image_path');
            
            // Garder les anciennes colonnes pour compatibilité
            // $table->string('audio_path')->nullable();
            // $table->string('pdf_path')->nullable();
            // $table->string('image_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chants_de_messe', function (Blueprint $table) {
            $table->dropColumn(['audio_files', 'pdf_files', 'image_files']);
        });
    }
};