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
            // Ajouter des colonnes pour les pupitres
            $table->json('soprano_files')->nullable()->after('image_files');
            $table->json('alto_files')->nullable()->after('soprano_files');
            $table->json('tenor_files')->nullable()->after('alto_files');
            $table->json('basse_files')->nullable()->after('tenor_files');
            $table->json('tutti_files')->nullable()->after('basse_files'); // Tous les pupitres ensemble
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chants_de_messe', function (Blueprint $table) {
            $table->dropColumn(['soprano_files', 'alto_files', 'tenor_files', 'basse_files', 'tutti_files']);
        });
    }
};