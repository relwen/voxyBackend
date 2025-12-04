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
        // Changer voice_part de ENUM à VARCHAR pour accepter n'importe quel nom de pupitre
        DB::statement("ALTER TABLE users MODIFY COLUMN voice_part VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ENUM (avec les valeurs actuelles)
        DB::statement("ALTER TABLE users MODIFY COLUMN voice_part ENUM('SOPRANE', 'SOPRANO', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON') NULL");
    }
};
