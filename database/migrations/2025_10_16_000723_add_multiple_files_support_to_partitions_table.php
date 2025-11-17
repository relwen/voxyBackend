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
            // Ajouter tous les champs nécessaires pour les partitions
            $table->string('title')->after('id');
            $table->text('description')->nullable()->after('title');
            $table->string('audio_path')->nullable()->after('description');
            $table->string('pdf_path')->nullable()->after('audio_path');
            $table->string('image_path')->nullable()->after('pdf_path');
            $table->json('audio_files')->nullable()->after('image_path'); // Array de chemins audio
            $table->json('pdf_files')->nullable()->after('audio_files'); // Array de chemins PDF
            $table->json('image_files')->nullable()->after('pdf_files'); // Array de chemins images
            $table->foreignId('category_id')->constrained()->onDelete('cascade')->after('image_files');
            $table->foreignId('chorale_id')->constrained()->onDelete('cascade')->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partitions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['chorale_id']);
            $table->dropColumn([
                'title', 'description', 'audio_path', 'pdf_path', 'image_path',
                'audio_files', 'pdf_files', 'image_files', 'category_id', 'chorale_id'
            ]);
        });
    }
};