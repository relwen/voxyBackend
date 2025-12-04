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
        Schema::table('categories', function (Blueprint $table) {
            // Type de structure : 'simple' (pas de sections), 'with_sections' (avec sections), 'with_dossiers' (avec dossiers puis sections)
            $table->string('structure_type')->default('simple')->after('icon');
            // Configuration JSON pour la structure flexible
            $table->json('structure_config')->nullable()->after('structure_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['structure_type', 'structure_config']);
        });
    }
};
