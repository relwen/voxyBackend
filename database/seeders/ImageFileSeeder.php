<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partition;
use Illuminate\Support\Facades\Storage;

class ImageFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer quelques fichiers image de test
        $imageFiles = [
            'test_image_1.jpg',
            'test_image_2.png',
            'test_image_3.jpg'
        ];

        // Créer les fichiers image de test dans le stockage
        foreach ($imageFiles as $imageFile) {
            $filePath = "partitions/images/{$imageFile}";
            
            // Créer un fichier image de test (fichier vide pour la démo)
            if (!Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->put($filePath, 'Fichier image de test - ' . $imageFile);
                echo "Fichier image créé: {$filePath}\n";
            }
        }

        // Associer des fichiers image à quelques partitions
        $partitions = Partition::whereNotNull('pdf_files')->skip(5)->take(5)->get();
        
        foreach ($partitions as $index => $partition) {
            $imageFiles = [];
            
            // Ajouter 1-2 fichiers image par partition
            $numImageFiles = rand(1, 2);
            for ($i = 0; $i < $numImageFiles; $i++) {
                $imageFile = "partitions/images/test_image_" . (($index + $i) % 3 + 1) . ".jpg";
                if (Storage::disk('public')->exists($imageFile)) {
                    $imageFiles[] = $imageFile;
                }
            }
            
            if (!empty($imageFiles)) {
                $partition->image_files = $imageFiles;
                $partition->save();
                echo "Fichiers image ajoutés à la partition: {$partition->title}\n";
            }
        }

        echo "\nSeeder image terminé avec succès!\n";
    }
}
