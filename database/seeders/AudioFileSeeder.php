<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partition;
use Illuminate\Support\Facades\Storage;

class AudioFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer quelques fichiers audio de test
        $audioFiles = [
            'test_audio_1.mp3',
            'test_audio_2.wav',
            'test_audio_3.mp3'
        ];

        // Créer les fichiers audio de test dans le stockage
        foreach ($audioFiles as $audioFile) {
            $filePath = "partitions/audio/{$audioFile}";
            
            // Créer un fichier audio de test (fichier vide pour la démo)
            if (!Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->put($filePath, 'Fichier audio de test - ' . $audioFile);
                echo "Fichier audio créé: {$filePath}\n";
            }
        }

        // Associer des fichiers audio à quelques partitions
        $partitions = Partition::whereNotNull('pdf_files')->take(5)->get();
        
        foreach ($partitions as $index => $partition) {
            $audioFiles = [];
            
            // Ajouter 1-2 fichiers audio par partition
            $numAudioFiles = rand(1, 2);
            for ($i = 0; $i < $numAudioFiles; $i++) {
                $audioFile = "partitions/audio/test_audio_" . (($index + $i) % 3 + 1) . ".mp3";
                if (Storage::disk('public')->exists($audioFile)) {
                    $audioFiles[] = $audioFile;
                }
            }
            
            if (!empty($audioFiles)) {
                $partition->audio_files = $audioFiles;
                $partition->save();
                echo "Fichiers audio ajoutés à la partition: {$partition->title}\n";
            }
        }

        echo "\nSeeder audio terminé avec succès!\n";
    }
}
