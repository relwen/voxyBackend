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
        Schema::table('vocalises', function (Blueprint $table) {
            // Supprimer l'ancien champ enum voice_part
            $table->dropColumn('voice_part');
            
            // Ajouter le nouveau champ pupitre_id (foreign key vers chorale_pupitres)
            $table->foreignId('pupitre_id')->nullable()->constrained('chorale_pupitres')->onDelete('set null')->after('chorale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vocalises', function (Blueprint $table) {
            $table->dropForeign(['pupitre_id']);
            $table->dropColumn('pupitre_id');
            
            // Recréer l'ancien champ enum
            $table->enum('voice_part', ['SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON'])->after('description');
        });
    }
};
