<?php

namespace Database\Seeders;

use App\Models\Ground;
use App\Models\GroundImage;
use App\Models\GroundFeature;
use Illuminate\Database\Seeder;

class GroundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create three ground records
        $grounds = [
            [
                'name' => 'City Football Arena',
                'location' => 'Downtown, City Center',
                'capacity' => 22,
                'ground_type' => 'Football',
                'description' => 'Professional football pitch with floodlights, spectator seating and modern amenities for the perfect game experience.',
                'rules' => 'No smoking, No alcohol, No spikes',
                'opening_time' => '06:00:00',
                'closing_time' => '22:00:00',
                'phone' => '123-456-7890',
                'email' => 'cityfootball@example.com',
                'status' => 'active',
                'client_id' => 1,
            ],
            [
                'name' => 'Downtown Basketball Court',
                'location' => 'Central Park, Downtown',
                'capacity' => 10,
                'ground_type' => 'Basketball',
                'description' => 'Indoor basketball court with professional flooring, equipment and full climate control for year-round play.',
                'rules' => 'Indoor shoes only, No food on court',
                'opening_time' => '08:00:00',
                'closing_time' => '21:00:00',
                'phone' => '123-456-7891',
                'email' => 'downtownbasketball@example.com',
                'status' => 'active',
                'client_id' => 1,
            ],
            [
                'name' => 'Greenview Tennis Club',
                'location' => 'Greenview Park, North Side',
                'capacity' => 4,
                'ground_type' => 'Tennis',
                'description' => 'Professional tennis courts with clay and hard court options, club house amenities and coaching available.',
                'rules' => 'Tennis shoes only, Proper attire required',
                'opening_time' => '07:00:00',
                'closing_time' => '20:00:00',
                'phone' => '123-456-7892',
                'email' => 'greenview@example.com',
                'status' => 'active',
                'client_id' => 1,
            ],
        ];

        // Ensure a client id exists
        $clientId = \App\Models\Client::query()->value('id') ?? \App\Models\Client::create([
            'name' => 'Default Client',
            'email' => 'client@example.com',
            'phone' => '1234567890',
            'full_address' => '123 Test Street, City',
            'status' => 'active',
        ])->id;

        // Insert grounds
        foreach ($grounds as $groundData) {
            $groundData['client_id'] = $clientId;
            $ground = Ground::create($groundData);

            // Add features
            $features = [];
            switch ($ground->ground_type) {
                case 'Football':
                    $features = ['FIFA standard size', 'Artificial turf', 'Floodlights', 'Changing rooms', 'Parking'];
                    break;
                case 'Basketball':
                    $features = ['NBA standard size', 'Indoor court', 'Air conditioning', 'Electronic scoreboard', 'Spectator area'];
                    break;
                case 'Tennis':
                    $features = ['Professional grade court', 'Clay and hard court options', 'Ball machines available', 'Private lessons', 'Club membership'];
                    break;
            }

            foreach ($features as $feature) {
                GroundFeature::create([
                    'ground_id' => $ground->id,
                    'feature_name' => $feature,
                    'feature_type' => 'general',
                    'feature_status' => 'active',
                ]);
            }

            // Add a placeholder image
            GroundImage::create([
                'ground_id' => $ground->id,
                'image_path' => 'assets/images/ground-placeholder.jpg',
                'is_primary' => true,
            ]);

            // Add sample slots with prices
            $slotPrices = [
                'Football' => 50.00,
                'Basketball' => 40.00,
                'Tennis' => 35.00
            ];

            $slotPrice = $slotPrices[$ground->ground_type] ?? 50.00;

            // Create morning slot
            \App\Models\GroundSlot::create([
                'ground_id' => $ground->id,
                'slot_name' => 'Morning Slot',
                'start_time' => '06:00:00',
                'end_time' => '12:00:00',
                'slot_type' => 'morning',
                'slot_status' => 'active',
                'price_per_slot' => $slotPrice
            ]);

            // Create evening slot
            \App\Models\GroundSlot::create([
                'ground_id' => $ground->id,
                'slot_name' => 'Evening Slot',
                'start_time' => '18:00:00',
                'end_time' => '22:00:00',
                'slot_type' => 'evening',
                'slot_status' => 'active',
                'price_per_slot' => $slotPrice
            ]);
        }
    }
}
