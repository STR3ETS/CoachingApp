<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Coach;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CoachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Eline','Nicky','Roy'] as $name) {
            $user = User::create([
                'name' => $name,
                'email' => strtolower($name).'@example.com',
                'password' => Hash::make('password'),
                'role' => 'coach',
            ]);
            Coach::create(['user_id' => $user->id, 'specialties' => ['Hyrox']]);
        }
    }
}
