<?php

namespace Database\Seeders\FactoriesSeeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $super_admin = User::firstOrCreate(['email' => 'super_admin@example.com'], [
            'user_name' => 'super_admin',
            'type' => 'business',
            'password' => bcrypt('admin1234'),
            'email_verified_at' => now(), 
        ]);
        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'user_name' => 'admin',
            'type' => 'business',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $manager = User::firstOrCreate(['email' => 'manager@example.com'], [
            'user_name' => 'manager',
            'type' => 'business',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $sales = User::firstOrCreate(['email' => 'sales@example.com'], [
            'user_name' => 'sales',
            'type' => 'business',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $warehouse = User::firstOrCreate(['email' => 'warehouse@example.com'], [
            'user_name' => 'warehouse',
            'type' => 'business',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(), 
        ]);
        $customer = User::firstOrCreate(['email' => 'customer@example.com'], [
            'user_name' => 'customer',
            'type' => 'business',
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

        foreach ([$super_admin, $admin, $manager, $sales, $warehouse, $customer] as $user) {
            $user->employee()->firstOrCreate(
                [],
                fn (): array => Employee::factory()->for($user)->make()->getAttributes(),
            );
        }
    }
}
