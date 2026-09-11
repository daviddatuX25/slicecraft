<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // NOTE (change NOT in the worksheet instructions): These deterministic
        // customers make the Manage Customer rubric repeatable during the demo.
        $customers = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria@slicecraft.test',
                'phone' => '0917 555 0101',
                'address' => 'Vigan City',
                'status' => 'active',
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@slicecraft.test',
                'phone' => '0917 555 0102',
                'address' => 'Candon City',
                'status' => 'pending',
            ],
            [
                'name' => 'Ana Reyes',
                'email' => 'ana@slicecraft.test',
                'phone' => '0917 555 0103',
                'address' => 'Narvacan',
                'status' => 'inactive',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
