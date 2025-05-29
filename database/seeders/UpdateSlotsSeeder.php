<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GroundSlot;

class UpdateSlotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all slots
        $slots = GroundSlot::all();

        foreach ($slots as $slot) {
            // Check if the slot_name contains a time range format (e.g., "10:30 - 11:30")
            if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $slot->slot_name, $matches)) {
                $startTime = $matches[1];
                $endTime = $matches[2];

                // Update the slot with start_time and end_time
                $slot->update([
                    'start_time' => $startTime,
                    'end_time' => $endTime
                ]);

                echo "Updated slot ID {$slot->id}: {$slot->slot_name} with start_time: {$startTime}, end_time: {$endTime}\n";
            } else {
                echo "Slot ID {$slot->id}: {$slot->slot_name} doesn't match the expected format\n";
            }
        }
    }
}
