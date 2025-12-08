<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Chorale;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MaestroChoraleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer une chorale d'exemple
        $chorale = Chorale::create([
            'name' => 'Chorale Exemple',
            'description' => 'Chorale d\'exemple pour le maestro',
            'location' => 'Paris, France'
        ]);

        // Créer un utilisateur maestro
        // Utiliser DB::table() pour contourner le cast 'hashed' du modèle User
        // et éviter le double hashage
        $userId = DB::table('users')->insertGetId([
            'name' => 'Maestro Jacob',
            'email' => 'maestro@chorale.com',
            'password' => Hash::make('maestro123'),
            'role' => 'maestro',
            'status' => 'approved',
            'phone' => '+22657023486',
            'chorale_id' => $chorale->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $maestro = User::find($userId);

        $this->command->info('Maestro chorale créé avec succès!');
        $this->command->info('Email: maestro@chorale.com');
        $this->command->info('Mot de passe: maestro123');
        $this->command->info('Chorale ID: ' . $chorale->id);
    }
}
