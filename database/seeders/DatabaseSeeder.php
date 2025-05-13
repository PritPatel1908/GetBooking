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
            'password' => 'admin',
            'user_type' => 'admin',
        ]);

        // Create a client record for ground ownership
        Client::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '1234567890',
            'address' => '123 Test Street',
            'user_id' => 1
        ]);

        // Run ground seeder
        $this->call([
            GroundSeeder::class,
        ]);
    }
}
