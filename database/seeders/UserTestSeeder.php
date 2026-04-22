<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Chorale;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\ChoralePupitre;

class UserTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Récupérer ou créer des chorales
        $stCamille = Chorale::where('name', 'St Camille 1200 logements')->first();
        if (!$stCamille) {
            $stCamille = Chorale::create([
                'name' => 'St Camille 1200 logements',
                'description' => 'La chorale des jeunes paroisse St Camille 1200 logements AD JESUM PER CANTICUM',
                'location' => 'Ouagadougou, Burkina Faso'
            ]);
            $this->createDefaultTemplate($stCamille->id);
        }

        $choraleBoni = Chorale::where('name', 'Chorale Ste Cécile de Boni')->first();
        if (!$choraleBoni) {
            $choraleBoni = Chorale::create([
                'name' => 'Chorale Ste Cécile de Boni',
                'description' => 'Chorale paroissiale de Boni',
                'location' => 'Boni, Burkina Faso'
            ]);
            $this->createDefaultTemplate($choraleBoni->id);
        }

        // 2. Créer des Maestros
        $maestros = [
            [
                'name' => 'Maestro Camille',
                'email' => 'relwendezoundi295@gmail.com',
                'password' => Hash::make('linux123'),
                'role' => 'maestro',
                'status' => 'approved',
                'chorale_id' => $stCamille->id,
                'phone' => '+22601020304',
                'is_active' => true,
            ],
            [
                'name' => 'Maestro Boni',
                'email' => 'jacoco@gmail.com',
                'password' => Hash::make('linux123'),
                'role' => 'maestro',
                'status' => 'approved',
                'chorale_id' => $choraleBoni->id,
                'phone' => '+22605060708',
                'is_active' => true,
            ]
        ];

        foreach ($maestros as $maestroData) {
            DB::table('users')->updateOrInsert(
                ['email' => $maestroData['email']],
                array_merge($maestroData, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 3. Créer des Utilisateurs simples
        $users = [
            [
                'name' => 'Jean Baptiste',
                'email' => 'user1@test.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'approved',
                'chorale_id' => $stCamille->id,
                'phone' => '+22610111213',
                'voice_part' => 'Ténor',
                'is_active' => true,
            ],
            [
                'name' => 'Marie Madeleine',
                'email' => 'user2@test.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'pending',
                'chorale_id' => $stCamille->id,
                'phone' => '+22614151617',
                'voice_part' => 'Soprano',
                'is_active' => true,
            ],
            [
                'name' => 'Paul l\'Apôtre',
                'email' => 'user3@test.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'approved',
                'chorale_id' => $choraleBoni->id,
                'phone' => '+22618192021',
                'voice_part' => 'Basse',
                'is_active' => true,
            ]
        ];

        foreach ($users as $userData) {
            DB::table('users')->updateOrInsert(
                ['email' => $userData['email']],
                array_merge($userData, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('Utilisateurs et Maestros de test créés avec succès !');
        $this->command->info('Maestros: maestro1@test.com, maestro2@test.com (pass: password)');
        $this->command->info('Users: user1@test.com, user2@test.com, user3@test.com (pass: password)');
    }

    /**
     * Appliquer le template par défaut à une chorale
     */
    private function createDefaultTemplate($choraleId)
    {
        // Créer les pupitres par défaut
        $defaultPupitres = [
            ['nom' => 'SOPRANE', 'order' => 1, 'is_default' => false],
            ['nom' => 'ALTO', 'order' => 2, 'is_default' => false],
            ['nom' => 'TENOR', 'order' => 3, 'is_default' => false],
            ['nom' => 'BASSES', 'order' => 4, 'is_default' => false],
            ['nom' => 'TUTTIES', 'order' => 0, 'is_default' => true],
        ];

        foreach ($defaultPupitres as $pupitre) {
            ChoralePupitre::updateOrCreate(
                ['chorale_id' => $choraleId, 'nom' => $pupitre['nom']],
                $pupitre
            );
        }

        // Créer la rubrique "Messes" par défaut
        Category::updateOrCreate(
            ['chorale_id' => $choraleId, 'name' => 'Messes'],
            [
                'description' => 'Rubrique universelle pour les messes',
                'structure_type' => 'with_sections',
                'color' => '#9E0250',
                'icon' => 'church',
            ]
        );

        // Créer la rubrique "Vocalises"
        Category::updateOrCreate(
            ['chorale_id' => $choraleId, 'name' => 'Vocalises'],
            [
                'description' => 'Rubrique pour les vocalises avec dossiers',
                'structure_type' => 'with_sections',
                'color' => '#2196F3',
                'icon' => 'mic',
            ]
        );

        // Créer la rubrique "Chants"
        Category::updateOrCreate(
            ['chorale_id' => $choraleId, 'name' => 'Chants'],
            [
                'description' => 'Rubrique pour les chants avec parties',
                'structure_type' => 'with_sections',
                'color' => '#4CAF50',
                'icon' => 'music_note',
            ]
        );
    }
}
