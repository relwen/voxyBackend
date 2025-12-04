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
            $table->enum('pupitre', ['SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON', 'TUTTI'])->default('TUTTI')->after('chorale_id');
            $table->foreignId('messe_id')->nullable()->constrained()->onDelete('set null')->after('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partitions', function (Blueprint $table) {
            $table->dropForeign(['messe_id']);
            $table->dropColumn(['pupitre', 'messe_id']);
        });
    }
};
