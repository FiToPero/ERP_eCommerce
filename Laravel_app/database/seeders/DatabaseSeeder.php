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
        $super_admin = User::firstOrCreate([
            'user_name' => 'super_admin',
            'type' => 'business',
            'email' => 'super_admin@example.com',
            'password' => bcrypt('admin1234'),
            'email_verified_at' => now(), 
        ]);
        $admin = User::firstOrCreate([
            'user_name' => 'admin',
            'type' => 'business',
            'email' => 'admin@example.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $manager = User::firstOrCreate([
            'user_name' => 'manager',
            'type' => 'business',
            'email' => 'manager@example.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $sales = User::firstOrCreate([
            'user_name' => 'sales',
            'type' => 'business',
            'email' => 'sales@example.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $warehouse = User::firstOrCreate([
            'user_name' => 'warehouse',
            'type' => 'business',
            'email' => 'warehouse@example.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $customer = User::firstOrCreate([
            'user_name' => 'customer',
            'type' => 'business',
            'email' => 'customer@example.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);

        // Asignar roles
        $super_admin->syncRoles('super_admin');
        $admin->syncRoles('admin');
        $manager->syncRoles('manager');
        $sales->syncRoles('sales');
        $warehouse->syncRoles('warehouse');
        $customer->syncRoles('customer');

        // Seedear categorías y productos
        $this->call([
            FactoriesSeeders\CategorySeeder::class,
            FactoriesSeeders\ProductSeeder::class,
            FactoriesSeeders\StorageSeeder::class,
        ]);
    }
}
