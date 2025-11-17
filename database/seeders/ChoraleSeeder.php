<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chorale;

class ChoraleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chorales = [
            [
                'name' => 'Chorale Saint-Michel',
                'description' => 'Chorale paroissiale de la cathédrale Saint-Michel',
                'location' => 'Paris, France'
            ],
            [
                'name' => 'Ensemble Vocal de Lyon',
                'description' => 'Ensemble vocal professionnel de Lyon',
                'location' => 'Lyon, France'
            ],
            [
                'name' => 'Chorale Universitaire',
                'description' => 'Chorale des étudiants de l\'université',
                'location' => 'Marseille, France'
            ],
            [
                'name' => 'Voix d\'Or',
                'description' => 'Chorale amateur de chants traditionnels',
                'location' => 'Toulouse, France'
            ]
        ];

        foreach ($chorales as $chorale) {
            Chorale::create($chorale);
        }

        $this->command->info('Chorales de test créées avec succès!');
    }
}
