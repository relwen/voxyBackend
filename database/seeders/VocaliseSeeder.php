<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vocalise;
use App\Models\Chorale;

class VocaliseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chorales = Chorale::all();
        
        if ($chorales->count() == 0) {
            $this->command->info('Aucune chorale trouvée. Créez d\'abord des chorales.');
            return;
        }

        $vocalises = [
            [
                'title' => 'Échauffement Soprane - Do Ré Mi',
                'description' => 'Exercice d\'échauffement pour les sopranes sur la gamme Do-Ré-Mi',
                'voice_part' => 'SOPRANE',
                'chorale_id' => $chorales->first()->id,
            ],
            [
                'title' => 'Vocalise Alto - Arpèges',
                'description' => 'Exercice d\'arpèges pour les altos',
                'voice_part' => 'ALTO',
                'chorale_id' => $chorales->first()->id,
            ],
            [
                'title' => 'Ténor - Vocalise en tierces',
                'description' => 'Exercice de tierces pour les ténors',
                'voice_part' => 'TENOR',
                'chorale_id' => $chorales->first()->id,
            ],
            [
                'title' => 'Basse - Vocalise chromatique',
                'description' => 'Exercice chromatique pour les basses',
                'voice_part' => 'BASSE',
                'chorale_id' => $chorales->first()->id,
            ],
        ];

        // Si il y a une deuxième chorale, ajoutons des vocalises pour elle aussi
        if ($chorales->count() > 1) {
            $secondChorale = $chorales->get(1);
            $vocalises[] = [
                'title' => 'Soprane - Vocalise en octaves',
                'description' => 'Exercice d\'octaves pour les sopranes',
                'voice_part' => 'SOPRANE',
                'chorale_id' => $secondChorale->id,
            ];
            $vocalises[] = [
                'title' => 'Mezzo-soprane - Vocalise en gammes',
                'description' => 'Exercice de gammes pour les mezzo-sopranes',
                'voice_part' => 'MEZOSOPRANE',
                'chorale_id' => $secondChorale->id,
            ];
        }

        foreach ($vocalises as $vocaliseData) {
            Vocalise::create($vocaliseData);
        }

        $this->command->info('Vocalises créées avec succès !');
    }
}
