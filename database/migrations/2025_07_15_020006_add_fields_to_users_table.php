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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('role');
            $table->unsignedBigInteger('chorale_id')->nullable()->after('status');
            $table->enum('voice_part', ['SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON'])->nullable()->after('chorale_id');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('role');
            $table->unsignedBigInteger('chorale_id')->nullable()->after('status');
            $table->enum('voice_part', ['SOPRANE', 'TENOR', 'MEZOSOPRANE', 'ALTO', 'BASSE', 'BARITON'])->nullable()->after('chorale_id');
            //
        });
    }
};
