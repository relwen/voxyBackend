<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrateur VoXY',
            'email' => 'admin@voxy.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'approved',
            'phone' => '+33 1 23 45 67 89'
        ]);

        $this->command->info('Utilisateur administrateur créé avec succès!');
        $this->command->info('Email: admin@voxy.com');
        $this->command->info('Mot de passe: admin123');
    }
}
