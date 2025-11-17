<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Messe;
use App\Models\MesseSection;
use App\Models\ChantDeMesse;

class MesseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les messes
        $messes = [
            [
                'nom' => 'St GABRIEL',
                'description' => 'Messe de la paroisse St Gabriel',
                'couleur' => '#4CAF50',
                'icone' => 'church',
            ],
            [
                'nom' => 'SYMPATHIE',
                'description' => 'Messe de la paroisse Sympathie',
                'couleur' => '#2196F3',
                'icone' => 'favorite',
            ],
            [
                'nom' => 'PENTECOTE',
                'description' => 'Messe de Pentecôte',
                'couleur' => '#FF9800',
                'icone' => 'celebration',
            ],
            [
                'nom' => 'MESSE DOMINICALE',
                'description' => 'Messe dominicale générale',
                'couleur' => '#9C27B0',
                'icone' => 'self_improvement',
            ],
        ];

        foreach ($messes as $messeData) {
            $messe = Messe::create($messeData);

            // Créer les sections pour chaque messe
            $sections = [
                [
                    'nom' => 'Kyrié',
                    'description' => 'Seigneur, prends pitié',
                    'ordre' => 1,
                ],
                [
                    'nom' => 'Gloria',
                    'description' => 'Gloire à Dieu',
                    'ordre' => 2,
                ],
                [
                    'nom' => 'Sanctus',
                    'description' => 'Saint, Saint, Saint',
                    'ordre' => 3,
                ],
                [
                    'nom' => 'Agnus Dei',
                    'description' => 'Agneau de Dieu',
                    'ordre' => 4,
                ],
            ];

            foreach ($sections as $sectionData) {
                $section = MesseSection::create([
                    'messe_id' => $messe->id,
                    'nom' => $sectionData['nom'],
                    'description' => $sectionData['description'],
                    'ordre' => $sectionData['ordre'],
                ]);

                // Créer quelques chants pour chaque section
                $chants = [
                    [
                        'titre' => "Chant principal - {$sectionData['nom']}",
                        'description' => "Chant principal pour la section {$sectionData['nom']}",
                        'ordre' => 1,
                    ],
                    [
                        'titre' => "Chant alternatif - {$sectionData['nom']}",
                        'description' => "Chant alternatif pour la section {$sectionData['nom']}",
                        'ordre' => 2,
                    ],
                ];

                foreach ($chants as $chantData) {
                    ChantDeMesse::create([
                        'section_id' => $section->id,
                        'titre' => $chantData['titre'],
                        'description' => $chantData['description'],
                        'ordre' => $chantData['ordre'],
                        'audio_path' => 'chants/' . strtolower($sectionData['nom']) . '_' . $chantData['ordre'] . '.mp3',
                        'pdf_path' => 'chants/' . strtolower($sectionData['nom']) . '_' . $chantData['ordre'] . '.pdf',
                        'image_path' => 'chants/' . strtolower($sectionData['nom']) . '_' . $chantData['ordre'] . '.jpg',
                        'soprano_files' => [
                            'chants/soprano/' . strtolower($sectionData['nom']) . '_soprano_' . $chantData['ordre'] . '.mp3',
                            'chants/soprano/' . strtolower($sectionData['nom']) . '_soprano_' . $chantData['ordre'] . '.pdf'
                        ],
                        'alto_files' => [
                            'chants/alto/' . strtolower($sectionData['nom']) . '_alto_' . $chantData['ordre'] . '.mp3',
                            'chants/alto/' . strtolower($sectionData['nom']) . '_alto_' . $chantData['ordre'] . '.pdf'
                        ],
                        'tenor_files' => [
                            'chants/tenor/' . strtolower($sectionData['nom']) . '_tenor_' . $chantData['ordre'] . '.mp3',
                            'chants/tenor/' . strtolower($sectionData['nom']) . '_tenor_' . $chantData['ordre'] . '.pdf'
                        ],
                        'basse_files' => [
                            'chants/basse/' . strtolower($sectionData['nom']) . '_basse_' . $chantData['ordre'] . '.mp3',
                            'chants/basse/' . strtolower($sectionData['nom']) . '_basse_' . $chantData['ordre'] . '.pdf'
                        ],
                        'tutti_files' => [
                            'chants/tutti/' . strtolower($sectionData['nom']) . '_tutti_' . $chantData['ordre'] . '.mp3',
                            'chants/tutti/' . strtolower($sectionData['nom']) . '_tutti_' . $chantData['ordre'] . '.pdf'
                        ],
                    ]);
                }
            }
        }
    }
}