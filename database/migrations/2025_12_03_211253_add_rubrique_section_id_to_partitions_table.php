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
            $table->foreignId('rubrique_section_id')->nullable()->constrained('rubrique_sections')->onDelete('set null')->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partitions', function (Blueprint $table) {
            $table->dropForeign(['rubrique_section_id']);
            $table->dropColumn('rubrique_section_id');
        });
    }
};
