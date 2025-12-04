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
        Schema::table('rubrique_sections', function (Blueprint $table) {
            // Pour permettre une hiérarchie : Rubrique -> Dossier -> Section
            // Si dossier_id est null, la section est directement sous la rubrique
            // Si dossier_id est défini, la section est dans un dossier
            // Type : 'dossier' ou 'section'
            $table->string('type')->default('section')->after('category_id');
            $table->foreignId('dossier_id')->nullable()->after('type')
                  ->constrained('rubrique_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubrique_sections', function (Blueprint $table) {
            $table->dropForeign(['dossier_id']);
            $table->dropColumn(['dossier_id', 'type']);
        });
    }
};

