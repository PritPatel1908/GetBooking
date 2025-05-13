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
                'price_per_hour' => 50.00,
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
                'price_per_hour' => 40.00,
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
                'price_per_hour' => 35.00,
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

        // Insert grounds
        foreach ($grounds as $groundData) {
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
                    'name' => $feature,
                    'description' => 'Feature for ' . $ground->name,
                ]);
            }

            // Add a placeholder image
            GroundImage::create([
                'ground_id' => $ground->id,
                'image_path' => 'assets/images/ground-placeholder.jpg',
                'is_primary' => true,
            ]);
        }
    }
}
