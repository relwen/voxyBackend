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
            $table->foreignId('rubrique_section_id')->nullable()->constrained('rubrique_sections')->onDelete('set null')->after('chorale_id');
            $table->json('vocalise_part')->nullable()->after('rubrique_section_id'); // Référence à une partie de la structure (ex: {"part": "Exercice 1", "subPart": null})
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vocalises', function (Blueprint $table) {
            $table->dropForeign(['rubrique_section_id']);
            $table->dropColumn(['rubrique_section_id', 'vocalise_part']);
        });
    }
};
