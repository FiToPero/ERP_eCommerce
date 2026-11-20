<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles y permisos primero
        $this->command->call('shield:generate', ['--all' => true, '--panel' => 'admin']);

        // Asegurar que el roles necesarios existen
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'warehouse', 'guard_name' => 'web']);

        // Crear relaciones entre roles y permisos
        $this->call(FactoriesSeeders\RolePermissionSeeder::class);

        // Crear usuarios
        $this->call(FactoriesSeeders\UserSeeder::class);
        $this->call(FactoriesSeeders\CustomerSeeder::class);

        // Seedear categorías y productos
        $this->call([
            FactoriesSeeders\CategorySeeder::class,
            FactoriesSeeders\ProductSeeder::class,
            FactoriesSeeders\StorageSeeder::class,
        ]);
    }
}
