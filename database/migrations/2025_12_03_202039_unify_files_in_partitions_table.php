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
        Schema::table('partitions', function (Blueprint $table) {
            // Ajouter le nouveau champ files unifié
            $table->json('files')->nullable()->after('image_files');
        });

        // Migrer les données existantes vers le nouveau format
        $partitions = DB::table('partitions')->get();
        
        foreach ($partitions as $partition) {
            $files = [];
            
            // Migrer audio_files
            if ($partition->audio_files) {
                $audioFiles = json_decode($partition->audio_files, true);
                if (is_array($audioFiles)) {
                    $files = array_merge($files, $audioFiles);
                }
            }
            
            // Migrer pdf_files
            if ($partition->pdf_files) {
                $pdfFiles = json_decode($partition->pdf_files, true);
                if (is_array($pdfFiles)) {
                    $files = array_merge($files, $pdfFiles);
                }
            }
            
            // Migrer image_files
            if ($partition->image_files) {
                $imageFiles = json_decode($partition->image_files, true);
                if (is_array($imageFiles)) {
                    $files = array_merge($files, $imageFiles);
                }
            }
            
            // Migrer les anciens champs uniques
            if ($partition->audio_path) {
                $files[] = $partition->audio_path;
            }
            if ($partition->pdf_path) {
                $files[] = $partition->pdf_path;
            }
            if ($partition->image_path) {
                $files[] = $partition->image_path;
            }
            
            // Mettre à jour seulement si on a des fichiers
            if (!empty($files)) {
                DB::table('partitions')
                    ->where('id', $partition->id)
                    ->update(['files' => json_encode(array_unique($files))]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partitions', function (Blueprint $table) {
            $table->dropColumn('files');
        });
    }
};
