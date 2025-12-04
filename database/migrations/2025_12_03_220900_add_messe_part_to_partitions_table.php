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
        Schema::table('partitions', function (Blueprint $table) {
            // Stocker la référence à la partie de messe (nom de la partie et sous-partie si applicable)
            $table->json('messe_part')->nullable()->after('rubrique_section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partitions', function (Blueprint $table) {
            $table->dropColumn('messe_part');
        });
    }
};
