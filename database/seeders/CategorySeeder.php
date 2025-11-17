<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vocalises',
                'description' => 'Exercices vocaux et vocalises pour l\'entraînement',
                'color' => '#4CAF50',
                'icon' => 'music_note',
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
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Catégories créées avec succès!');
    }
}