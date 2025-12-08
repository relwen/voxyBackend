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
                'name' => 'St Camille 1200 logements',
                'description' => 'La chorale des jeunes paroisse St Camille 1200 logements AD JESUM PER CANTICUM',
                'location' => 'Ouagadougou, Burkina Faso'
            ],
        ];

        foreach ($chorales as $chorale) {
            Chorale::create($chorale);
        }

        $this->command->info('Chorales de test créées avec succès!');
    }
}
