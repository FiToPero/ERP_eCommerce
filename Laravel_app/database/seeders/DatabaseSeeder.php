<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


         // Crear roles y permisos primero
        $this->command->call('shield:generate', ['--all' => true]);

        // Crear usuario admin
        $admin = User::factory()->create([
            'user_name' => 'admin',
            'type' => 'business',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin1234'),
            'email_verified_at' => now(), 
        ]);

        // Asignar rol super_admin
        $admin->assignRole('super_admin');
    }
}
