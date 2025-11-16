<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class TestDurationCalculation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:duration-calculation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test duration calculation for bookings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing duration calculation...');

        $bookings = Booking::with(['details.slot'])->get();

        foreach ($bookings as $booking) {
            $this->info("\n--- Booking {$booking->id} ---");
            $this->info("Database Duration: {$booking->duration} hours");

            $actualDuration = 0;
            $timeSlots = [];

            if ($booking->details->isNotEmpty()) {
                $this->info("Booking Details Count: {$booking->details->count()}");

                foreach ($booking->details as $detail) {
                    $this->info("  Detail ID: {$detail->id}");
                    $this->info("  Ground ID: {$detail->ground_id}");
                    $this->info("  Slot ID: " . ($detail->slot_id ?? 'NULL'));

                    if ($detail->slot) {
                        $this->info("  Slot Time Range: {$detail->slot->time_range}");
                        $this->info("  Slot Start Time: " . ($detail->slot->start_time ?? 'NULL'));
                        $this->info("  Slot End Time: " . ($detail->slot->end_time ?? 'NULL'));

                        $timeSlots[] = $detail->slot->time_range;

                        if ($detail->slot->start_time && $detail->slot->end_time) {
                            $startTime = Carbon::parse($detail->slot->start_time);
                            $endTime = Carbon::parse($detail->slot->end_time);

                            if ($endTime < $startTime) {
                                $endTime->addDay();
                            }

                            $slotDuration = $endTime->diffInHours($startTime);
                            $actualDuration += $slotDuration;

                            $this->info("  Calculated Slot Duration: {$slotDuration} hours");
                        }
                    } else {
                        $this->info("  Slot: NULL");
                    }
                }
            } else {
                $this->info("No booking details found");
            }

            $this->info("Time Slots: " . implode(', ', $timeSlots));
            $this->info("Calculated Duration: {$actualDuration} hours");
            $this->info("Final Duration: " . ($actualDuration > 0 ? $actualDuration : $booking->duration) . " hours");
        }

        $this->info("\nTest completed!");
    }
}
