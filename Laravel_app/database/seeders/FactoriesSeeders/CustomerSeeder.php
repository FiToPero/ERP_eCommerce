<?php

namespace Database\Seeders\FactoriesSeeders;

use App\Models\Customer;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer_01 = Customer::firstOrCreate(['email' => 'customer_01@example.com'], [
            'customer_name' => 'Customer 01',
            'type' => 'personal',
            'web' => env('FRONTEND_URL_WEB_01'),
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'remember_token' => null,
        ]);

        $customer_02 = Customer::firstOrCreate(['email' => 'customer_02@example.com'], [
            'customer_name' => 'Customer 02',
            'type' => 'personal',
            'web' => env('FRONTEND_URL_WEB_02'),
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'remember_token' => null,
        ]);

        foreach ([$customer_01, $customer_02] as $customer) {
            $customer->profile()->firstOrCreate(
                [],
                fn (): array => Profile::factory()->for($customer)->make()->getAttributes(),
            );
        }
    }
}
