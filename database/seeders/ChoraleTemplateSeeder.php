<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChoraleTemplate;

class ChoraleTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = ChoraleTemplate::create([
            'name' => 'Base Chorale',
            'description' => 'Template de base avec les pupitres et rubriques standards',
            'is_system' => true,
            'default_pupitres' => [
                [
                    'nom' => 'Tutti',
                    'description' => 'Tous les pupitres ensemble',
                    'color' => '#9E9E9E',
                    'icon' => 'groups',
                    'order' => 0,
                    'is_default' => true,
                ],
                [
                    'nom' => 'Soprane',
                    'description' => 'Voix aiguës féminines',
                    'color' => '#E91E63',
                    'icon' => 'person',
                    'order' => 1,
                    'is_default' => false,
                ],
                [
                    'nom' => 'Mézosoprane',
                    'description' => 'Voix moyennes féminines',
                    'color' => '#9C27B0',
                    'icon' => 'person',
                    'order' => 2,
                    'is_default' => false,
                ],
                [
                    'nom' => 'Alto',
                    'description' => 'Voix graves féminines',
                    'color' => '#3F51B5',
                    'icon' => 'person',
                    'order' => 3,
                    'is_default' => false,
                ],
                [
                    'nom' => 'Ténor',
                    'description' => 'Voix aiguës masculines',
                    'color' => '#2196F3',
                    'icon' => 'person',
                    'order' => 4,
                    'is_default' => false,
                ],
                [
                    'nom' => 'Baryton',
                    'description' => 'Voix moyennes masculines',
                    'color' => '#00BCD4',
                    'icon' => 'person',
                    'order' => 5,
                    'is_default' => false,
                ],
                [
                    'nom' => 'Basse',
                    'description' => 'Voix graves masculines',
                    'color' => '#009688',
                    'icon' => 'person',
                    'order' => 6,
                    'is_default' => false,
                ],
            ],
            'default_categories' => [
                [
                    'name' => 'Vocalises',
                    'description' => 'Exercices vocaux et vocalises pour l\'entraînement',
                    'color' => '#4CAF50',
                    'icon' => 'mic',
                ],
                [
                    'name' => 'Messes',
                    'description' => 'Chants et partitions pour les messes',
                    'color' => '#2196F3',
                    'icon' => 'church',
                ],
                [
                    'name' => 'Chants',
                    'description' => 'Chants traditionnels et contemporains',
                    'color' => '#FF9800',
                    'icon' => 'library_music',
                ],
                [
                    'name' => 'Cantiques',
                    'description' => 'Cantiques religieux et spirituels',
                    'color' => '#9C27B0',
                    'icon' => 'favorite',
                ],
                [
                    'name' => 'Hymnes',
                    'description' => 'Hymnes nationaux et religieux',
                    'color' => '#F44336',
                    'icon' => 'flag',
                ],
            ],
        ]);

        $this->command->info('Template de base créé avec succès!');
    }
}
