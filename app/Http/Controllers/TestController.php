<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;

class TestController extends Controller
{
    public function testBookingData()
    {
        // Get the first booking with all relationships
        $booking = Booking::with([
            'details.ground.images',
            'details.ground.features',
            'details.slot',
            'payment',
            'user'
        ])->first();

        if (!$booking) {
            return response()->json(['error' => 'No bookings found']);
        }

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'booking_sku' => $booking->booking_sku,
                'amount' => $booking->amount,
                'booking_status' => $booking->booking_status,
                'payment_id' => $booking->payment_id,
                'payment' => $booking->payment ? [
                    'id' => $booking->payment->id,
                    'payment_method' => $booking->payment->payment_method,
                    'payment_status' => $booking->payment->payment_status,
                    'transaction_id' => $booking->payment->transaction_id,
                    'amount' => $booking->payment->amount,
                    'date' => $booking->payment->date
                ] : null,
                'details' => $booking->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'ground_id' => $detail->ground_id,
                        'slot_id' => $detail->slot_id,
                        'ground' => $detail->ground ? [
                            'id' => $detail->ground->id,
                            'name' => $detail->ground->name,
                            'location' => $detail->ground->location,
                            'phone' => $detail->ground->phone
                        ] : null,
                        'slot' => $detail->slot ? [
                            'id' => $detail->slot->id,
                            'time_range' => $detail->slot->time_range,
                            'price_per_slot' => $detail->slot->price_per_slot
                        ] : null
                    ];
                })
            ]
        ]);
    }
}
