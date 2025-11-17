<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partition;
use App\Models\Messe;
use App\Models\Reference;
use Illuminate\Support\Facades\Storage;

class PartitionFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des messes si elles n'existent pas
        $messes = [
            'Air' => [
                'description' => 'Messe Air avec sections populaires et moore',
                'couleur' => '#2196F3',
                'icone' => '🎵',
                'sections' => [
                    'Kyrié' => [
                        'populaire' => 'air_populair_kyrie.pdf',
                        'moore' => 'air_moore_kyrie.pdf'
                    ],
                    'Gloria' => [
                        'populaire' => 'air_populair_gloria.pdf',
                        'moore' => 'air_moore_gloria.pdf'
                    ],
                    'Sanctus' => [
                        'populaire' => 'air_populair_sanctus.pdf',
                        'moore' => 'air_moore_sanctus.pdf'
                    ],
                    'Agnus Dei' => [
                        'populaire' => 'air_populair_agnus.pdf'
                    ]
                ]
            ],
            'Aka' => [
                'description' => 'Messe Aka avec toutes les sections',
                'couleur' => '#4CAF50',
                'icone' => '⛪',
                'sections' => [
                    'Kyrié' => ['aka_kyrie.pdf'],
                    'Gloria' => ['aka_gloria.pdf'],
                    'Sanctus' => ['aka_sanctus.pdf'],
                    'Agnus Dei' => ['aka_agnus.pdf'],
                    'Credo' => ['aka_credo.pdf']
                ]
            ],
            'Amina' => [
                'description' => 'Messe Amina Christi de Tino',
                'couleur' => '#FF9800',
                'icone' => '🎼',
                'sections' => [
                    'Kyrié' => ['amina_christi_de_tino_kyrie.pdf'],
                    'Gloria' => ['amina_christi_de_tino_gloria.pdf']
                ]
            ],
            'Clark' => [
                'description' => 'Messe Clark Eulalie',
                'couleur' => '#9C27B0',
                'icone' => '🎶',
                'sections' => [
                    'Kyrié' => ['clark_eulalie_kyrie.pdf'],
                    'Gloria' => ['clark_eulalie_gloria.pdf'],
                    'Agnus Dei' => ['clark_eulalie_agnus.pdf']
                ]
            ],
            'Goly' => [
                'description' => 'Messe Goly',
                'couleur' => '#F44336',
                'icone' => '🎵',
                'sections' => [
                    'Kyrié' => ['goly_kyrie.pdf'],
                    'Gloria' => ['goly_gloria.pdf'],
                    'Sanctus' => ['goly_sanctus.pdf'],
                    'Agnus Dei' => ['goly_agnus.pdf']
                ]
            ],
            'Jazz' => [
                'description' => 'Messe Jazz',
                'couleur' => '#795548',
                'icone' => '🎷',
                'sections' => [
                    'Kyrié' => ['jazz_kyrie.pdf'],
                    'Gloria' => ['jazz_gloria.pdf'],
                    'Sanctus' => ['jazz_sanctus.pdf'],
                    'Agnus Dei' => ['jazz_agnus.pdf'],
                    'Benedictus' => ['jazz_benedictus.pdf']
                ]
            ],
            'Rencontre' => [
                'description' => 'Messe Rencontre',
                'couleur' => '#607D8B',
                'icone' => '🤝',
                'sections' => [
                    'Kyrié' => ['rencontre_kyrie.pdf'],
                    'Gloria' => ['rencontre_gloria.pdf'],
                    'Sanctus' => ['rencontre_sanctus.pdf'],
                    'Agnus Dei' => ['rencontre_agnus.pdf']
                ]
            ],
            'Sainte' => [
                'description' => 'Messe Sainte Bernadette',
                'couleur' => '#E91E63',
                'icone' => '👼',
                'sections' => [
                    'Gloria' => ['sainte_bernadette_gloria.pdf'],
                    'Sanctus' => ['sainte_bernadette_sanctus.pdf'],
                    'Agnus Dei' => ['sainte_bernadette_agnus.pdf']
                ]
            ],
            'Sympathie' => [
                'description' => 'Messe Sympathie',
                'couleur' => '#00BCD4',
                'icone' => '💙',
                'sections' => [
                    'Kyrié' => ['sympathie_kyrie.pdf'],
                    'Gloria' => ['sympathie_gloria.pdf'],
                    'Sanctus' => ['sympathie_sanctus.pdf'],
                    'Agnus Dei' => ['sympathie_agnus.pdf'],
                    'Acclamation' => ['sympathie_acclamation.pdf']
                ]
            ]
        ];

        foreach ($messes as $messeName => $messeData) {
            // Créer ou récupérer la messe
            $messe = Messe::firstOrCreate(
                ['nom' => $messeName],
                [
                    'description' => $messeData['description'],
                    'couleur' => $messeData['couleur'],
                    'icone' => $messeData['icone']
                ]
            );

            echo "Traitement de la messe: {$messeName}\n";

            foreach ($messeData['sections'] as $sectionName => $files) {
                // Créer ou récupérer la référence (section)
                $reference = Reference::firstOrCreate(
                    [
                        'name' => $sectionName,
                        'messe_id' => $messe->id
                    ],
                    [
                        'description' => "Section {$sectionName} de la messe {$messeName}",
                        'order_position' => 0
                    ]
                );

                echo "  - Section: {$sectionName}\n";

                // Traiter chaque fichier PDF
                $fileIndex = 0;
                foreach ($files as $fileName) {
                    $filePath = "partitions/{$fileName}";
                    
                    // Vérifier si le fichier existe
                    if (Storage::disk('public')->exists($filePath)) {
                        // Créer une partition pour ce fichier
                        $partitionTitle = $fileIndex === 0 ? $sectionName : "{$sectionName} - Version " . ($fileIndex + 1);
                        
                        $partition = Partition::firstOrCreate(
                            [
                                'title' => $partitionTitle,
                                'reference_id' => $reference->id
                            ],
                            [
                                'description' => "Partition {$partitionTitle} de la messe {$messeName}",
                                'pdf_files' => [$filePath],
                                'category_id' => 1, // Catégorie par défaut
                                'chorale_id' => 1   // Chorale par défaut
                            ]
                        );

                        // Si la partition existe déjà, ajouter le fichier PDF
                        if ($partition->pdf_files === null) {
                            $partition->pdf_files = [$filePath];
                        } else {
                            $existingFiles = $partition->pdf_files;
                            if (!in_array($filePath, $existingFiles)) {
                                $existingFiles[] = $filePath;
                                $partition->pdf_files = $existingFiles;
                            }
                        }
                        
                        $partition->save();
                        echo "    ✓ Partition créée/mise à jour: {$partitionTitle}\n";
                        $fileIndex++;
                    } else {
                        echo "    ✗ Fichier non trouvé: {$filePath}\n";
                    }
                }
            }
        }

        echo "\nSeeder terminé avec succès!\n";
    }
}
