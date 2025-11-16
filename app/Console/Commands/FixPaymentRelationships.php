<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class FixPaymentRelationships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:payment-relationships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix payment relationships for existing bookings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix payment relationships...');

        // Get all bookings without payment relationships
        $bookingsWithoutPayment = Booking::whereNull('payment_id')->get();

        $this->info("Found {$bookingsWithoutPayment->count()} bookings without payment relationships");

        foreach ($bookingsWithoutPayment as $booking) {
            // Check if there's already a payment record for this booking
            $existingPayment = Payment::where('booking_id', $booking->id)->first();

            if ($existingPayment) {
                // Link the existing payment to the booking
                $booking->payment_id = $existingPayment->id;
                $booking->save();
                $this->info("Linked existing payment {$existingPayment->id} to booking {$booking->id}");
            } else {
                // Create a new payment record
                $payment = new Payment([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'date' => $booking->created_at,
                    'amount' => $booking->amount,
                    'payment_status' => 'pending',
                    'payment_method' => 'online',
                    'payment_type' => 'booking',
                    'transaction_id' => 'FIXED_' . $booking->id . '_' . time()
                ]);

                $payment->save();

                // Link the payment to the booking
                $booking->payment_id = $payment->id;
                $booking->save();

                $this->info("Created and linked payment {$payment->id} to booking {$booking->id}");
            }
        }

        // Get all payments without booking relationships
        $paymentsWithoutBooking = Payment::whereNull('booking_id')->get();

        $this->info("Found {$paymentsWithoutBooking->count()} payments without booking relationships");

        foreach ($paymentsWithoutBooking as $payment) {
            // Try to find a booking for this payment
            $booking = Booking::where('user_id', $payment->user_id)
                ->where('amount', $payment->amount)
                ->whereNull('payment_id')
                ->first();

            if ($booking) {
                $payment->booking_id = $booking->id;
                $payment->save();

                $booking->payment_id = $payment->id;
                $booking->save();

                $this->info("Linked payment {$payment->id} to booking {$booking->id}");
            }
        }

        $this->info('Payment relationship fixing completed!');

        // Show summary
        $totalBookings = Booking::count();
        $bookingsWithPayment = Booking::whereNotNull('payment_id')->count();

        $this->info("Summary:");
        $this->info("- Total bookings: {$totalBookings}");
        $this->info("- Bookings with payment: {$bookingsWithPayment}");
        $this->info("- Bookings without payment: " . ($totalBookings - $bookingsWithPayment));
    }
}
