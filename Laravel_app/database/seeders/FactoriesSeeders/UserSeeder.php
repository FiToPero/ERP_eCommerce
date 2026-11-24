<?php

namespace Database\Seeders\FactoriesSeeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $super_admin_employee = User::where('email', 'super_admin@example.com')->value('employee_id')
                ?? Employee::factory()->create()->id;
            $admin_employee = User::where('email', 'admin@example.com')->value('employee_id')
                ?? Employee::factory()->create()->id;
            $manager_employee = User::where('email', 'manager@example.com')->value('employee_id')
                ?? Employee::factory()->create()->id;
            $sales_employee = User::where('email', 'sales@example.com')->value('employee_id')
                ?? Employee::factory()->create()->id;
            $warehouse_employee = User::where('email', 'warehouse@example.com')->value('employee_id')
                ?? Employee::factory()->create()->id;
            $customer_employee = User::where('email', 'customer@example.com')->value('employee_id')
                ?? Employee::factory()->create()->id;

            User::unguarded(function () use ($super_admin_employee, $admin_employee, $manager_employee, $sales_employee, $warehouse_employee, $customer_employee): void {
                $super_admin = User::firstOrCreate(['email' => 'super_admin@example.com'], [
                    'employee_id' => $super_admin_employee,
                    'user_name' => 'super_admin',
                    'type' => 'business',
                    'password' => bcrypt('admin1234'),
                    'email_verified_at' => now(),
                ]);
                $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
                    'employee_id' => $admin_employee,
                    'user_name' => 'admin',
                    'type' => 'business',
                    'password' => bcrypt('12345678'),
                    'email_verified_at' => now(),
                ]);
                $manager = User::firstOrCreate(['email' => 'manager@example.com'], [
                    'employee_id' => $manager_employee,
                    'user_name' => 'manager',
                    'type' => 'business',
                    'password' => bcrypt('12345678'),
                    'email_verified_at' => now(),
                ]);
                $sales = User::firstOrCreate(['email' => 'sales@example.com'], [
                    'employee_id' => $sales_employee,
                    'user_name' => 'sales',
                    'type' => 'business',
                    'password' => bcrypt('12345678'),
                    'email_verified_at' => now(),
                ]);
                $warehouse = User::firstOrCreate(['email' => 'warehouse@example.com'], [
                    'employee_id' => $warehouse_employee,
                    'user_name' => 'warehouse',
                    'type' => 'business',
                    'password' => bcrypt('12345678'),
                    'email_verified_at' => now(),
                ]);
                $customer = User::firstOrCreate(['email' => 'customer@example.com'], [
                    'employee_id' => $customer_employee,
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

            });
        });
    }
}
