<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => 'indian@super',
            'user_type' => 'admin',
        ]);

        // Ensure at least one client exists for ground ownership
        // if (!Client::query()->exists()) {
        //     Client::create([
        //         'name' => 'Default Client',
        //         'email' => 'client@example.com',
        //         'phone' => '1234567890',
        //         'full_address' => '123 Test Street, City',
        //         'status' => 'active',
        //     ]);
        // }

        // Run ground seeder
        // $this->call([
        //     GroundSeeder::class,
        // ]);
    }
}
