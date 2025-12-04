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
        // Modifier l'enum pour ajouter 'SOPRANO' (compatible avec l'app mobile)
        DB::statement("ALTER TABLE users MODIFY COLUMN voice_part ENUM('SOPRANE', 'SOPRANO', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'enum original sans SOPRANO
        DB::statement("ALTER TABLE users MODIFY COLUMN voice_part ENUM('SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON') NULL");
    }
};
