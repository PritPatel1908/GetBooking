<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ground;
use App\Models\BookingDetail;

class TestBookingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:booking-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test booking data relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing booking data relationships...');

        // Get all bookings with relationships
        $bookings = Booking::with([
            'details.ground.images',
            'details.ground.features',
            'details.slot',
            'payment',
            'user'
        ])->get();

        $this->info("Found {$bookings->count()} bookings");

        foreach ($bookings as $booking) {
            $this->info("\n--- Booking {$booking->id} ---");
            $this->info("SKU: {$booking->booking_sku}");
            $this->info("Amount: ₹{$booking->amount}");
            $this->info("Status: {$booking->booking_status}");
            $this->info("Payment ID: " . ($booking->payment_id ?? 'NULL'));

            if ($booking->payment) {
                $this->info("Payment Method: {$booking->payment->payment_method}");
                $this->info("Payment Status: {$booking->payment->payment_status}");
                $this->info("Transaction ID: {$booking->payment->transaction_id}");
            } else {
                $this->info("Payment: NULL");
            }

            $this->info("Details Count: {$booking->details->count()}");

            foreach ($booking->details as $detail) {
                $this->info("  Detail ID: {$detail->id}");
                $this->info("  Ground ID: {$detail->ground_id}");
                $this->info("  Slot ID: " . ($detail->slot_id ?? 'NULL'));

                if ($detail->ground) {
                    $this->info("  Ground Name: {$detail->ground->name}");
                    $this->info("  Ground Location: {$detail->ground->location}");
                    $this->info("  Ground Phone: " . ($detail->ground->phone ?? 'N/A'));
                } else {
                    $this->info("  Ground: NULL");
                }

                if ($detail->slot) {
                    $this->info("  Slot Time: {$detail->slot->time_range}");
                    $this->info("  Slot Price: ₹{$detail->slot->price_per_slot}");
                } else {
                    $this->info("  Slot: NULL");
                }
            }
        }

        // Test ground data
        $this->info("\n--- Testing Ground Data ---");
        $grounds = Ground::with(['images', 'features'])->get();
        $this->info("Found {$grounds->count()} grounds");

        foreach ($grounds as $ground) {
            $this->info("\nGround {$ground->id}: {$ground->name}");
            $this->info("Location: {$ground->location}");
            $this->info("Phone: " . ($ground->phone ?? 'N/A'));
            $this->info("Images: {$ground->images->count()}");
            $this->info("Features: {$ground->features->count()}");
        }

        $this->info("\nTest completed!");
    }
}
