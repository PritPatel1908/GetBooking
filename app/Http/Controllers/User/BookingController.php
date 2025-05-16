<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ground;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\GroundSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Get available slots for a ground on a specific date
     *
     * @param string $date Date in Y-m-d format
     * @param int $groundId Ground ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableSlots($date, $groundId)
    {
        try {
            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to view available slots.'
                ], 401);
            }

            // Validate date format
            try {
                $selectedDate = Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format. Expected Y-m-d.'
                ], 400);
            }

            // Find the ground
            try {
                $ground = Ground::findOrFail($groundId);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ground not found.'
                ], 404);
            }

            // Get all slots for this ground
            $groundSlots = $ground->slots;

            // Get booked time slots for the selected date using BookingDetail
            $bookedSlotIds = BookingDetail::where('ground_id', $ground->id)
                ->whereHas('booking', function ($query) use ($selectedDate) {
                    $query->where('booking_date', $selectedDate)
                        ->where('booking_status', '!=', 'cancelled');
                })
                ->pluck('slot_id')
                ->toArray();

            $slots = [];

            // If no slots defined in database, generate them based on opening/closing time
            if ($groundSlots->isEmpty()) {
                $openingTime = Carbon::parse($ground->opening_time ?? '08:00:00');
                $closingTime = Carbon::parse($ground->closing_time ?? '22:00:00');
                $interval = 2; // 2 hour slots

                $currentTime = clone $openingTime;

                while ($currentTime < $closingTime) {
                    $slotStart = $currentTime->format('H:i');
                    $slotStartCarbon = Carbon::parse($slotStart);
                    $currentTime->addHours($interval);
                    $slotEnd = $currentTime->format('H:i');

                    if (Carbon::parse($slotEnd) <= $closingTime) {
                        // Calculate actual hours
                        $hours = $interval;
                        $slots[] = [
                            'id' => null, // No ID since these are generated
                            'time' => "$slotStart-$slotEnd",
                            'price' => round($ground->price_per_hour * $hours),
                            'hours' => $hours,
                            'available' => true // Default is available, we'll check bookings later
                        ];
                    }
                }
            } else {
                // Use slots from database
                foreach ($groundSlots as $slot) {
                    // Calculate the actual duration based on slot_name (format: "HH:MM-HH:MM")
                    $slotTime = explode('-', $slot->slot_name);
                    $hours = 2; // Default

                    if (count($slotTime) == 2) {
                        try {
                            $startTime = Carbon::parse($slotTime[0]);
                            $endTime = Carbon::parse($slotTime[1]);

                            // Handle slots that cross midnight
                            if ($endTime < $startTime) {
                                $endTime->addDay();
                            }

                            $hours = $endTime->diffInHours($startTime);
                            $hours = max(1, abs($hours)); // Ensure positive and at least 1 hour
                        } catch (\Exception $e) {
                            // If time parsing fails, use default
                        }
                    }

                    $slots[] = [
                        'id' => $slot->id,
                        'time' => $slot->slot_name, // Assuming slot_name is in format "HH:MM-HH:MM"
                        'price' => round($ground->price_per_hour * $hours),
                        'hours' => $hours,
                        'available' => !in_array($slot->id, $bookedSlotIds) && $slot->slot_status === 'active'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'slots' => $slots
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableSlots: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available slots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a booking
     *
     * @param int $id Booking ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelBooking($id)
    {
        try {
            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to cancel a booking.'
                ], 401);
            }

            // Get the current user
            $user = Auth::user();

            // Find the booking and ensure it belongs to this user
            $booking = Booking::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or does not belong to you.'
                ], 404);
            }

            // Check if the booking can be cancelled
            if ($booking->booking_status == 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking is already cancelled.'
                ], 400);
            }

            if ($booking->booking_status == 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Completed bookings cannot be cancelled.'
                ], 400);
            }

            // Check if the booking date is in the past
            if (Carbon::parse($booking->booking_date)->lt(Carbon::today())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Past bookings cannot be cancelled.'
                ], 400);
            }

            // Calculate cancellation fee based on time difference
            $bookingDate = Carbon::parse($booking->booking_date)->format('Y-m-d');
            $bookingTime = Carbon::parse($booking->booking_time)->format('H:i:s');
            $bookingDateTime = Carbon::parse($bookingDate . ' ' . $bookingTime);
            $now = Carbon::now();
            $hoursDifference = $now->diffInHours($bookingDateTime, false);

            $refundAmount = 0;
            $refundPercentage = 0;

            if ($hoursDifference >= 24) {
                // More than 24 hours - full refund
                $refundPercentage = 100;
                $refundAmount = $booking->amount;
            } elseif ($hoursDifference >= 12) {
                // Between 12-24 hours - 50% refund
                $refundPercentage = 50;
                $refundAmount = $booking->amount * 0.5;
            } else {
                // Less than 12 hours - no refund
                $refundPercentage = 0;
                $refundAmount = 0;
            }

            // Update booking status to cancelled
            $booking->booking_status = 'cancelled';
            $booking->notes = "Cancelled by user. " . ($refundPercentage > 0 ? "Refund of {$refundPercentage}% applied." : "No refund applied.");
            $booking->save();

            // If there's a payment, update its status
            if ($booking->payment) {
                $booking->payment->payment_status = 'refunded';
                $booking->payment->save();
            }

            // You might want to send an email notification here

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully.',
                'refundPercentage' => $refundPercentage,
                'refundAmount' => $refundAmount
            ]);
        } catch (\Exception $e) {
            Log::error('Error in cancelBooking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling booking: ' . $e->getMessage()
            ], 500);
        }
    }
}
