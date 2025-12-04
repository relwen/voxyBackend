<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partitions', function (Blueprint $table) {
            // Supprimer l'ancien champ enum pupitre
            $table->dropColumn('pupitre');
            
            // Ajouter le nouveau champ pupitre_id (foreign key vers chorale_pupitres)
            $table->foreignId('pupitre_id')->nullable()->constrained('chorale_pupitres')->onDelete('set null')->after('chorale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partitions', function (Blueprint $table) {
            $table->dropForeign(['pupitre_id']);
            $table->dropColumn('pupitre_id');
            
            // Recréer l'ancien champ enum
            $table->enum('pupitre', ['SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON', 'TUTTI'])->default('TUTTI')->after('chorale_id');
        });
    }
};
