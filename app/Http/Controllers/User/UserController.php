<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ground;
use App\Models\User;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\GroundSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\PDF;

class UserController extends Controller
{
    public function home()
    {
        // Get popular grounds (ordered by most bookings)
        $grounds = Ground::with(['images', 'features'])
            // ->active()
            ->take(3)
            ->get();
        // dd($grounds);

        return view('user.home', compact('grounds'));
    }

    public function all_grounds()
    {
        return view('user.all-grounds');
    }

    public function view_ground($id)
    {
        // Fetch ground with relationships
        $ground = Ground::with(['images', 'features', 'bookings', 'client', 'reviews.user', 'reviews.replies.user'])
            ->findOrFail($id);

        return view('user.view-ground-details', compact('ground'));
    }

    public function my_bookings()
    {
        // Enable query logging
        DB::enableQueryLog();

        // Get authenticated user
        $user = Auth::user();

        // Log user info
        Log::info('User ID: ' . $user->id);
        Log::info('User Email: ' . $user->email);

        // Fetch all bookings for the user with their details, ground and slot info
        $bookings = Booking::with([
            'details.ground.images',
            'details.ground.features',
            'details.slot',
            'payment'
        ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Log the query and results
        Log::info('Bookings Query:', DB::getQueryLog());
        Log::info('Total Bookings Found: ' . $bookings->count());

        if ($bookings->count() > 0) {
            Log::info('First Booking Details:', [
                'id' => $bookings->first()->id,
                'status' => $bookings->first()->booking_status,
                'date' => $bookings->first()->booking_date,
                'details_count' => $bookings->first()->details->count()
            ]);
        }

        // Group bookings by status
        $upcomingBookings = $bookings->filter(function ($booking) {
            return $booking->booking_status != 'cancelled' &&
                $booking->booking_status != 'completed' &&
                Carbon::parse($booking->booking_date)->gte(Carbon::today());
        });

        $completedBookings = $bookings->filter(function ($booking) {
            return $booking->booking_status == 'completed' ||
                (Carbon::parse($booking->booking_date)->lt(Carbon::today()) &&
                    $booking->booking_status != 'cancelled');
        });

        $cancelledBookings = $bookings->filter(function ($booking) {
            return $booking->booking_status == 'cancelled';
        });

        // Count each category
        $upcomingCount = $upcomingBookings->count();
        $completedCount = $completedBookings->count();
        $cancelledCount = $cancelledBookings->count();

        // Log counts
        Log::info('Booking Counts:', [
            'total' => $bookings->count(),
            'upcoming' => $upcomingCount,
            'completed' => $completedCount,
            'cancelled' => $cancelledCount
        ]);

        return view('user.my-bookings', compact(
            'bookings',
            'upcomingBookings',
            'completedBookings',
            'cancelledBookings',
            'upcomingCount',
            'completedCount',
            'cancelledCount'
        ));
    }

    /**
     * Get ground details for displaying in a modal
     *
     * @param int $id The ground ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroundDetails($id)
    {
        try {
            $ground = Ground::with(['images', 'features'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'ground' => $ground,
                'imageUrl' => $ground->getImageUrl(),
                'features' => $ground->features,
                'images' => $ground->images
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ground not found or error fetching details',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get available slots for a ground on a specific date
     *
     * @param string $date The date in Y-m-d format
     * @param int $groundId The ground ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroundSlots($date, $groundId)
    {
        try {
            Log::info("getGroundSlots called with date: {$date}, groundId: {$groundId}");

            // Check if the date is valid
            try {
                $parsedDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
                if ($parsedDate !== $date) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid date format. Expected Y-m-d.'
                    ], 400);
                }
            } catch (\Exception $e) {
                Log::error("Invalid date format: {$date}. Error: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format. Expected Y-m-d.'
                ], 400);
            }

            // Validate the ground ID
            try {
                $ground = Ground::with('slots')->findOrFail($groundId);
                Log::info("Ground found: {$ground->name}");
                Log::info("Number of slots: " . $ground->slots->count());
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::error("Ground not found with ID: {$groundId}");
                return response()->json([
                    'success' => false,
                    'message' => 'Ground not found.'
                ], 404);
            }

            // Get booked slot IDs for this date and ground
            $bookedSlotIds = [];

            try {
                $bookedSlotIds = BookingDetail::where('ground_id', $groundId)
                    ->whereHas('booking', function ($query) use ($date) {
                        $query->where('booking_date', $date)
                            ->where('booking_status', '!=', 'cancelled');
                    })
                    ->pluck('slot_id')
                    ->toArray();

                Log::info("Booked slot IDs: " . json_encode($bookedSlotIds));
            } catch (\Exception $e) {
                // If there's an error, log it and continue with empty array
                Log::error('Error fetching booked slots: ' . $e->getMessage());
            }

            $slots = [];

            // If no slots defined, generate them based on opening/closing times
            if ($ground->slots->isEmpty()) {
                Log::info("No slots defined in database, generating slots");

                $openingTime = \Carbon\Carbon::parse($ground->opening_time ?? '08:00:00');
                $closingTime = \Carbon\Carbon::parse($ground->closing_time ?? '22:00:00');
                $interval = 2; // 2 hour slots
                $currentTime = clone $openingTime;

                Log::info("Opening time: {$openingTime}, Closing time: {$closingTime}, Interval: {$interval}h");

                // Generate slots
                while ($currentTime < $closingTime) {
                    $slotStart = $currentTime->format('H:i');
                    $slotEndObj = (clone $currentTime)->addHours($interval);

                    // Only add slot if it ends before or at closing time
                    if ($slotEndObj <= $closingTime) {
                        $slotEnd = $slotEndObj->format('H:i');

                        $slots[] = [
                            'id' => null, // Generated slots have no ID
                            'time' => "$slotStart-$slotEnd",
                            'price' => round($ground->price_per_hour * $interval),
                            'hours' => $interval,
                            'available' => true // All generated slots are available by default
                        ];
                    }

                    $currentTime->addHours($interval);
                }

                Log::info("Generated " . count($slots) . " slots");
            } else {
                Log::info("Using slots from database");
                // Use slots from database
                foreach ($ground->slots as $slot) {
                    // Calculate the actual duration based on slot_name (format: "HH:MM-HH:MM")
                    $slotTime = explode('-', $slot->slot_name);
                    $duration = 2; // Default duration if we can't calculate

                    if (count($slotTime) == 2) {
                        try {
                            $startTime = \Carbon\Carbon::parse($slotTime[0]);
                            $endTime = \Carbon\Carbon::parse($slotTime[1]);

                            // Handle slots that cross midnight
                            if ($endTime < $startTime) {
                                $endTime->addDay();
                            }

                            $duration = $endTime->diffInHours($startTime);

                            // Ensure we always have a positive duration
                            $duration = abs($duration);

                            // If duration is 0 (maybe less than an hour), set minimum to 1
                            $duration = max(1, $duration);
                        } catch (\Exception $e) {
                            // If there's an error parsing the time, use default
                            Log::warning("Error calculating slot duration: " . $e->getMessage());
                        }
                    }

                    $isAvailable = !in_array($slot->id, $bookedSlotIds) && $slot->slot_status === 'active';

                    Log::info("Slot {$slot->id} ({$slot->slot_name}): available = " . ($isAvailable ? 'true' : 'false') .
                        ", status = {$slot->slot_status}");

                    $slots[] = [
                        'id' => $slot->id,
                        'time' => $slot->slot_name,
                        'price' => round($ground->price_per_hour * $duration), // Price based on actual duration
                        'hours' => $duration,
                        'available' => $isAvailable
                    ];
                }
            }

            Log::info("Returning " . count($slots) . " slots");

            return response()->json([
                'success' => true,
                'slots' => $slots
            ]);
        } catch (\Exception $e) {
            Log::error("Error in getGroundSlots: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching slots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Book a ground
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookGround(Request $request)
    {
        try {
            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to book a ground.'
                ], 401);
            }

            // Validate request
            $validated = $request->validate([
                'ground_id' => 'required|exists:grounds,id',
                'date' => 'required|date|after_or_equal:today',
                'slot_ids' => 'required|array',
                'slot_ids.*' => 'nullable',
                'time_slots' => 'required|array',
                'time_slots.*' => 'required|string',
                'total_price' => 'required|numeric|min:1'
            ], [
                'ground_id.required' => 'Ground selection is required.',
                'ground_id.exists' => 'Selected ground does not exist.',
                'date.required' => 'Booking date is required.',
                'date.date' => 'Invalid date format.',
                'date.after_or_equal' => 'Booking date must be today or in the future.',
                'slot_ids.required' => 'Time slot selection is required.',
                'time_slots.required' => 'Time slot selection is required.',
                'total_price.required' => 'Total price is required.',
                'total_price.numeric' => 'Total price must be a number.',
                'total_price.min' => 'Total price must be at least 1.',
            ]);

            // Get current user
            $user = Auth::user();

            // Check if ground exists and is active
            $ground = Ground::find($validated['ground_id']);
            if (!$ground) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ground not found or is unavailable.'
                ], 404);
            }

            // Verify the selected date is not in the past
            $bookingDate = \Carbon\Carbon::parse($validated['date']);
            $today = \Carbon\Carbon::today();

            if ($bookingDate->lt($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot book for past dates.'
                ], 400);
            }

            // Get existing bookings for this ground and date through booking details
            $existingBookingDetails = BookingDetail::where('ground_id', $validated['ground_id'])
                ->whereHas('booking', function ($query) use ($validated) {
                    $query->where('booking_date', $validated['date'])
                        ->where('booking_status', '!=', 'cancelled');
                })
                ->with('booking')
                ->get();

            $existingBookings = [];
            foreach ($existingBookingDetails as $detail) {
                $existingBookings[] = $detail->booking;
            }

            // Check if any of the selected slots are already booked
            foreach ($validated['time_slots'] as $index => $timeSlot) {
                $times = explode('-', $timeSlot);
                $startTime = trim($times[0] ?? '08:00');
                $endTime = trim($times[1] ?? null);

                if (empty($endTime)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid time slot format. Expected format: "HH:MM-HH:MM"'
                    ], 400);
                }

                // Calculate duration
                $startTimeObj = \Carbon\Carbon::parse($startTime);
                $endTimeObj = \Carbon\Carbon::parse($endTime);

                // Handle slots that cross midnight
                if ($endTimeObj < $startTimeObj) {
                    $endTimeObj->addDay();
                }

                $duration = $endTimeObj->diffInHours($startTimeObj);
                $duration = max(1, abs($duration)); // Ensure positive and at least 1 hour

                // Check for overlapping bookings
                foreach ($existingBookings as $existingBooking) {
                    $existingStart = \Carbon\Carbon::parse($existingBooking->booking_time);
                    $existingEnd = (clone $existingStart)->addHours($existingBooking->duration);

                    $newStart = \Carbon\Carbon::parse($startTime);
                    $newEnd = (clone $newStart)->addHours($duration);

                    // Check if there's an overlap
                    if (
                        ($newStart >= $existingStart && $newStart < $existingEnd) ||
                        ($newEnd > $existingStart && $newEnd <= $existingEnd) ||
                        ($newStart <= $existingStart && $newEnd >= $existingEnd)
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' => "The time slot $startTime-$endTime is already booked. Please refresh and try again."
                        ], 409); // 409 Conflict
                    }
                }
            }

            // All slots are available, now create the booking
            $bookingSku = 'BK' . time() . rand(1000, 9999);
            $totalAmount = floatval($validated['total_price']);

            Log::info('Creating booking with SKU: ' . $bookingSku);
            Log::info('Total slots selected: ' . count($validated['time_slots']));

            // Find start and end times for the entire booking
            $earliestStartTime = null;
            $latestEndTime = null;
            $totalDuration = 0;

            foreach ($validated['time_slots'] as $timeSlot) {
                $times = explode('-', $timeSlot);
                $startTime = trim($times[0]);
                $endTime = trim($times[1]);

                $startObj = \Carbon\Carbon::parse($startTime);
                $endObj = \Carbon\Carbon::parse($endTime);

                // Handle slots that cross midnight
                if ($endObj < $startObj) {
                    $endObj->addDay();
                }

                if (!$earliestStartTime || $startObj < $earliestStartTime) {
                    $earliestStartTime = $startObj;
                }

                if (!$latestEndTime || $endObj > $latestEndTime) {
                    $latestEndTime = $endObj;
                }

                $slotDuration = $endObj->diffInHours($startObj);
                $totalDuration += max(1, abs($slotDuration));
            }

            // Create a single main booking record
            $booking = new Booking([
                'user_id' => $user->id,
                'booking_sku' => $bookingSku,
                'booking_date' => $validated['date'],
                'booking_time' => $earliestStartTime->format('H:i'),
                'duration' => $totalDuration,
                'amount' => $totalAmount,
                'booking_status' => 'pending'
            ]);

            $booking->save();
            Log::info("Created booking ID: {$booking->id} with SKU: {$bookingSku}");

            // Create booking details for each selected slot
            foreach ($validated['time_slots'] as $index => $timeSlot) {
                $slotId = $validated['slot_ids'][$index] ?? null;

                // Create booking detail with ground and slot info
                $bookingDetail = new BookingDetail([
                    'booking_id' => $booking->id,
                    'ground_id' => $validated['ground_id'],
                    'slot_id' => $slotId
                ]);

                $bookingDetail->save();
                Log::info("Created booking detail ID: {$bookingDetail->id} for slot: {$timeSlot}");
            }

            // Create a payment record
            $payment = new Payment([
                'date' => now(),
                'amount' => $totalAmount,
                'payment_status' => 'pending',
                'payment_method' => 'online',
                'payment_type' => 'booking',
                'booking_id' => $booking->id,
                'user_id' => $user->id
            ]);

            $payment->save();
            Log::info("Created payment ID: {$payment->id} for booking: {$booking->id}");

            // Update booking with payment ID
            $booking->payment_id = $payment->id;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Your booking has been confirmed successfully!',
                'booking' => $booking,
                'booking_sku' => $bookingSku,
                'redirect_url' => route('user.my_bookings')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in bookGround: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Please check your booking details.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in bookGround: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a specific booking
     *
     * @param string $bookingSku
     * @return \Illuminate\Http\Response
     */
    public function view_booking($bookingSku)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the current user
        $user = Auth::user();

        // Find the booking by SKU and make sure it belongs to the current user
        $booking = Booking::with(['details.ground', 'details.slot', 'payment', 'user'])
            ->where('booking_sku', $bookingSku)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('user.view-booking', compact('booking'));
    }

    /**
     * Debug function to check booking data
     */
    public function debug_bookings()
    {
        // Enable query logging
        DB::enableQueryLog();

        // Get authenticated user
        $user = Auth::user();

        // Fetch all bookings for the user with their details, ground and slot info
        $bookings = Booking::with(['details.ground', 'details.slot', 'payment'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get the executed query
        $query = DB::getQueryLog();

        // Show all bookings in database regardless of user
        $allBookings = Booking::all();

        return response()->json([
            'user_id' => $user->id,
            'booking_count' => $bookings->count(),
            'bookings' => $bookings,
            'query' => $query,
            'all_bookings_count' => $allBookings->count(),
            'all_bookings' => $allBookings
        ]);
    }

    /**
     * Download invoice for a booking
     *
     * @param string $bookingSku
     * @return \Illuminate\Http\Response
     */
    public function download_invoice($bookingSku)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the current user
        $user = Auth::user();

        // Find the booking by SKU and make sure it belongs to the current user
        $booking = Booking::with(['details.ground', 'details.slot', 'payment', 'user'])
            ->where('booking_sku', $bookingSku)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Generate PDF using a view
        $pdf = PDF::loadView('user.invoice', compact('booking'));

        // Set PDF options
        $pdf->setPaper('a4');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);

        // Generate filename
        $filename = 'Invoice_' . $booking->booking_sku . '.pdf';

        // Download the PDF
        return $pdf->download($filename);
    }
}
