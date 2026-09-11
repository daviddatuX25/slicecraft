<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'username' => 'test-user',
                'password' => Hash::make('password'),
            ],
        );

        // NOTE (change NOT in the worksheet instructions): The midterm needs a
        // repeatable database-backed admin login for local demonstration.
        User::updateOrCreate(
            ['username' => 'slicecraft-admin'],
            [
                'name' => 'SliceCraft Admin',
                'email' => 'admin@slicecraft.test',
                'password' => Hash::make('slicecraft-demo'),
            ],
        );

        $this->call([
            BookSeeder::class,
            ProductSeeder::class,
            CakePreviewSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
