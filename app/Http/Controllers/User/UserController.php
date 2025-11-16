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
        // Check if user is authenticated and is admin - admins should not access user panel
        if (Auth::check() && Auth::user()->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Return React app view for SPA
        // React will handle authentication check and redirect to login if needed
        return view('user.react-app');
    }

    public function all_grounds()
    {
        // Check if user is authenticated and is admin - admins should not access user panel
        if (Auth::check() && Auth::user()->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Return React app view for SPA
        return view('user.react-app');
    }

    public function view_ground($id)
    {
        // Check if user is authenticated and is admin - admins should not access user panel
        if (Auth::check() && Auth::user()->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Return React app view for SPA
        return view('user.react-app');
    }

    public function my_bookings()
    {
        // Check if user is authenticated and is admin - admins should not access user panel
        if (Auth::check() && Auth::user()->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

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

        // Return React app view for SPA
        return view('user.react-app');
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
            $ground = Ground::with(['images', 'features', 'slots'])
                ->findOrFail($id);

            // Determine the price to display
            $displayPrice = null;
            if ($ground->slots->count() > 0) {
                if ($ground->slots->count() == 1) {
                    // If only one slot, show that slot's price
                    $displayPrice = $ground->slots->first()->price_per_slot;
                } else {
                    // If multiple slots, show a random slot's price
                    $randomSlot = $ground->slots->random();
                    $displayPrice = $randomSlot->price_per_slot;
                }
            }

            // Add the display price to the ground object
            $ground->display_price = $displayPrice;

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
     * @param int $groundId The ground ID
     * @param string $date The date in Y-m-d format
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroundSlots($groundId, $date)
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
                            'price' => round(50 * $interval, 2), // Default price for generated slots, rounded to 2 decimal places
                            'hours' => $interval,
                            'available' => true // All generated slots are available by default
                        ];
                    }

                    $currentTime->addHours($interval);
                }

                Log::info("Generated " . count($slots) . " slots");
            } else {
                Log::info("Using slots from database");

                // Get the day of the week for the selected date
                $selectedDayOfWeek = strtolower(\Carbon\Carbon::parse($date)->format('l'));
                Log::info("Selected day of week: {$selectedDayOfWeek}");

                // Use slots from database, filtered by day of week
                foreach ($ground->slots as $slot) {
                    // Skip slots that don't match the selected day of week
                    // If slot has no day_of_week set, it's available for all days
                    if ($slot->day_of_week && $slot->day_of_week !== $selectedDayOfWeek) {
                        Log::info("Skipping slot {$slot->id} - day mismatch: {$slot->day_of_week} != {$selectedDayOfWeek}");
                        continue;
                    }

                    Log::info("Including slot {$slot->id} - day match: " . ($slot->day_of_week ?: 'no day restriction'));
                    // Calculate the actual duration based on start_time and end_time
                    $duration = 2; // Default duration if we can't calculate

                    if ($slot->start_time && $slot->end_time) {
                        try {
                            $startTime = \Carbon\Carbon::parse($slot->start_time);
                            $endTime = \Carbon\Carbon::parse($slot->end_time);

                            // Handle slots that cross midnight
                            if ($endTime < $startTime) {
                                $endTime->addDay();
                            }

                            $duration = $endTime->diffInHours($startTime);

                            // Ensure we always have a positive duration
                            $duration = abs($duration);

                            // Round to 2 decimal places for cleaner display
                            $duration = round($duration, 2);

                            // If duration is 0 (maybe less than an hour), set minimum to 1
                            $duration = max(1, $duration);
                        } catch (\Exception $e) {
                            // If there's an error parsing the time, use default
                            Log::warning("Error calculating slot duration: " . $e->getMessage());
                        }
                    } else {
                        // Fallback to old method with slot_name
                        $slotTime = explode('-', $slot->slot_name);
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

                                // Round to 2 decimal places for cleaner display
                                $duration = round($duration, 2);

                                // If duration is 0 (maybe less than an hour), set minimum to 1
                                $duration = max(1, $duration);
                            } catch (\Exception $e) {
                                // If there's an error parsing the time, use default
                                Log::warning("Error calculating slot duration: " . $e->getMessage());
                            }
                        }
                    }

                    $isAvailable = !in_array($slot->id, $bookedSlotIds) && $slot->slot_status === 'active';

                    Log::info("Slot {$slot->id} ({$slot->time_range}): available = " . ($isAvailable ? 'true' : 'false') .
                        ", status = {$slot->slot_status}");

                    $slots[] = [
                        'id' => $slot->id,
                        'time' => $slot->time_range,
                        'day_of_week' => $slot->day_of_week,
                        'price' => $slot->price_per_slot, // Show exact slot price as set in database
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
                'amount' => $totalAmount,
                'booking_status' => 'pending'
            ]);

            $booking->save();
            Log::info("Created booking ID: {$booking->id} with SKU: {$bookingSku}");

            // Create booking details for each selected slot
            foreach ($validated['time_slots'] as $index => $timeSlot) {
                $slotId = $validated['slot_ids'][$index] ?? null;

                // Calculate duration for this specific slot
                $times = explode('-', $timeSlot);
                $startTime = trim($times[0]);
                $endTime = trim($times[1]);
                $startTimeObj = \Carbon\Carbon::parse($startTime);
                $endTimeObj = \Carbon\Carbon::parse($endTime);

                if ($endTimeObj < $startTimeObj) {
                    $endTimeObj->addDay();
                }

                // Ensure duration saved is always positive and at least 1 hour
                $slotDuration = max(1, abs($endTimeObj->diffInHours($startTimeObj)));
                $slotPrice = $totalAmount / count($validated['time_slots']);

                // Create booking detail with ground and slot info
                $bookingDetail = new BookingDetail([
                    'booking_id' => $booking->id,
                    'ground_id' => $validated['ground_id'],
                    'slot_id' => $slotId,
                    'booking_time' => $timeSlot,
                    'duration' => $slotDuration,
                    'price' => $slotPrice,
                    'time_slot' => $timeSlot
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
        $booking = Booking::with(['details.ground.images', 'details.ground.features', 'details.slot', 'payment', 'user'])
            ->where('booking_sku', $bookingSku)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Return React app view for SPA
        return view('user.react-app');
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
        $bookings = Booking::with(['details.ground.images', 'details.ground.features', 'details.slot', 'payment'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get the executed query
        $query = DB::getQueryLog();

        // Show all bookings in database regardless of user
        $allBookings = Booking::with(['payment'])->get();

        return response()->json([
            'user_id' => $user->id,
            'booking_count' => $bookings->count(),
            'bookings' => $bookings->map(function ($booking) {
                return [
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
                        'amount' => $booking->payment->amount
                    ] : null,
                    'details_count' => $booking->details->count(),
                    'ground_info' => $booking->details->first() ? [
                        'ground_id' => $booking->details->first()->ground_id,
                        'ground_name' => $booking->details->first()->ground ? $booking->details->first()->ground->name : 'N/A'
                    ] : null
                ];
            }),
            'query' => $query,
            'all_bookings_count' => $allBookings->count(),
            'all_bookings' => $allBookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_sku' => $booking->booking_sku,
                    'payment_id' => $booking->payment_id,
                    'payment' => $booking->payment ? [
                        'id' => $booking->payment->id,
                        'payment_method' => $booking->payment->payment_method,
                        'payment_status' => $booking->payment->payment_status
                    ] : null
                ];
            })
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
        $booking = Booking::with(['details.ground.images', 'details.ground.features', 'details.slot', 'payment', 'user'])
            ->where('booking_sku', $bookingSku)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Generate PDF using inline HTML since blade file is removed
        $html = $this->generateInvoiceHTML($booking);
        $pdf = PDF::loadHTML($html);

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

    public function apiMyBookings()
    {
        $user = Auth::user();

        $bookings = Booking::with([
            'details.ground.images',
            'details.ground.features',
            'details.slot',
            'payment'
        ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $bookings = $bookings->map(function ($booking) {
            $groundDetails = $booking->details->first();
            $ground = $groundDetails ? $groundDetails->ground : null;
            $slot = $groundDetails ? $groundDetails->slot : null;

            return [
                'id' => $booking->id,
                'booking_sku' => $booking->booking_sku,
                'booking_date' => $booking->booking_date,
                'amount' => $booking->amount,
                'booking_status' => $booking->booking_status,
                'payment_status' => $booking->payment_status,
                'created_at' => $booking->created_at,
                'ground' => $ground ? [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'images' => $ground->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image_url' => $img->image_url,
                        ];
                    })->take(1),
                ] : null,
                'slot' => $slot ? [
                    'id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'time_range' => $slot->time_range,
                ] : null,
                'details' => $booking->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'slot_id' => $detail->slot_id,
                        'ground_id' => $detail->ground_id,
                        'time_slot' => $detail->time_slot ?? $detail->booking_time,
                        'booking_time' => $detail->booking_time,
                        'duration' => $detail->duration,
                        'price' => $detail->price,
                    ];
                }),
                'payment' => $booking->payment ? [
                    'id' => $booking->payment->id,
                    'payment_method' => $booking->payment->payment_method,
                    'payment_status' => $booking->payment->payment_status,
                    'payment_type' => $booking->payment->payment_type,
                    'transaction_id' => $booking->payment->transaction_id,
                    'amount' => $booking->payment->amount,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'bookings' => $bookings,
        ]);
    }

    public function apiBookingDetails($id)
    {
        $user = Auth::user();

        $booking = Booking::with([
            'details.ground.images',
            'details.ground.features',
            'details.slot',
            'payment'
        ])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $groundDetails = $booking->details->first();
        $ground = $groundDetails ? $groundDetails->ground : null;

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'booking_sku' => $booking->booking_sku,
                'booking_date' => $booking->booking_date,
                'amount' => $booking->amount,
                'booking_status' => $booking->booking_status,
                'payment_status' => $booking->payment_status,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at,
                'ground' => $ground ? [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'description' => $ground->description,
                    'images' => $ground->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image_url' => $img->image_url,
                        ];
                    }),
                    'features' => $ground->features->pluck('feature_name'),
                ] : null,
                'details' => $booking->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'slot_id' => $detail->slot_id,
                        'ground_id' => $detail->ground_id,
                        'time_slot' => $detail->time_slot ?? $detail->booking_time,
                        'booking_time' => $detail->booking_time,
                        'duration' => $detail->duration,
                        'price' => $detail->price,
                        'slot' => $detail->slot ? [
                            'id' => $detail->slot->id,
                            'slot_name' => $detail->slot->slot_name,
                            'time_range' => $detail->slot->time_range,
                            'price_per_slot' => $detail->slot->price_per_slot,
                        ] : null,
                    ];
                }),
                'payment' => $booking->payment ? [
                    'id' => $booking->payment->id,
                    'payment_method' => $booking->payment->payment_method,
                    'payment_status' => $booking->payment->payment_status,
                    'transaction_id' => $booking->payment->transaction_id,
                    'amount' => $booking->payment->amount,
                    'created_at' => $booking->payment->created_at,
                ] : null,
            ],
        ]);
    }

    /**
     * Get booking details by booking SKU
     *
     * @param string $bookingSku
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiBookingDetailsBySku($bookingSku)
    {
        $user = Auth::user();

        $booking = Booking::with([
            'details.ground.images',
            'details.ground.features',
            'details.slot',
            'payment',
            'user'
        ])
            ->where('user_id', $user->id)
            ->where('booking_sku', $bookingSku)
            ->firstOrFail();

        $groundDetails = $booking->details->first();
        $ground = $groundDetails ? $groundDetails->ground : null;

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'booking_sku' => $booking->booking_sku,
                'booking_date' => $booking->booking_date,
                'amount' => $booking->amount,
                'booking_status' => $booking->booking_status,
                'payment_status' => $booking->payment_status,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at,
                'ground' => $ground ? [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'description' => $ground->description,
                    'images' => $ground->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image_url' => $img->image_url,
                        ];
                    }),
                    'features' => $ground->features->pluck('feature_name'),
                ] : null,
                'details' => $booking->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'slot_id' => $detail->slot_id,
                        'ground_id' => $detail->ground_id,
                        'time_slot' => $detail->time_slot ?? $detail->booking_time,
                        'booking_time' => $detail->booking_time,
                        'duration' => $detail->duration,
                        'price' => $detail->price,
                        'slot' => $detail->slot ? [
                            'id' => $detail->slot->id,
                            'slot_name' => $detail->slot->slot_name,
                            'time_range' => $detail->slot->time_range,
                            'price_per_slot' => $detail->slot->price_per_slot,
                        ] : null,
                    ];
                }),
                'payment' => $booking->payment ? [
                    'id' => $booking->payment->id,
                    'payment_method' => $booking->payment->payment_method,
                    'payment_status' => $booking->payment->payment_status,
                    'transaction_id' => $booking->payment->transaction_id,
                    'amount' => $booking->payment->amount,
                    'created_at' => $booking->payment->created_at,
                ] : null,
            ],
        ]);
    }

    /**
     * Generate invoice HTML for PDF
     */
    private function generateInvoiceHTML($booking)
    {
        $bookingDate = \Carbon\Carbon::parse($booking->booking_date)->format('d M Y');
        $invoiceDate = \Carbon\Carbon::parse($booking->created_at)->format('d M Y');
        $totalDuration = $booking->details->sum('duration');
        $timeSlots = $booking->details->pluck('time_slot')->filter()->implode(', ');
        $ground = $booking->details->first()->ground ?? null;
        
        $basePrice = $booking->amount * 0.8;
        $gst = $booking->amount * 0.18;
        $platformFee = $booking->amount * 0.02;
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - ' . htmlspecialchars($booking->booking_sku) . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
        .invoice-header { text-align: center; margin-bottom: 30px; }
        .invoice-title { font-size: 24px; color: #2B6CB0; margin-bottom: 10px; }
        .invoice-details { margin-bottom: 30px; }
        .invoice-details table { width: 100%; border-collapse: collapse; }
        .invoice-details th, .invoice-details td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .invoice-details th { background-color: #f8f9fa; }
        .booking-details { margin-bottom: 30px; }
        .booking-details h3 { color: #2B6CB0; margin-bottom: 15px; }
        .total-amount { text-align: right; font-size: 18px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1 class="invoice-title">Booking Invoice</h1>
        <p>Invoice #' . htmlspecialchars($booking->booking_sku) . '</p>
        <p>Date: ' . $invoiceDate . '</p>
    </div>
    
    <div class="invoice-details">
        <table>
            <tr>
                <th>Customer Details</th>
                <th>Booking Details</th>
            </tr>
            <tr>
                <td>
                    <strong>Name:</strong> ' . htmlspecialchars($booking->user->name) . '<br>
                    <strong>Email:</strong> ' . htmlspecialchars($booking->user->email) . '<br>
                    <strong>Phone:</strong> ' . htmlspecialchars($booking->user->phone ?? 'N/A') . '
                </td>
                <td>
                    <strong>Booking Date:</strong> ' . $bookingDate . '<br>
                    <strong>Time Slots:</strong> ' . htmlspecialchars($timeSlots ?: 'N/A') . '<br>
                    <strong>Duration:</strong> ' . $totalDuration . ' hour' . ($totalDuration > 1 ? 's' : '') . '
                </td>
            </tr>
        </table>
    </div>';
        
        if ($booking->details->isNotEmpty()) {
            $html .= '<div class="booking-details">
                <h3>Time Slots Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Time Slot</th>
                            <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Duration</th>
                            <th style="padding: 10px; text-align: right; border-bottom: 2px solid #ddd;">Price</th>
                        </tr>
                    </thead>
                    <tbody>';
            foreach ($booking->details as $index => $detail) {
                $dayOfWeek = $detail->slot && $detail->slot->day_of_week ? ' (' . ucfirst($detail->slot->day_of_week) . ')' : '';
                // Use slot's actual price_per_slot, fallback to detail price if slot not available
                $slotPrice = $detail->slot && $detail->slot->price_per_slot 
                    ? $detail->slot->price_per_slot 
                    : $detail->price;
                $timeSlot = htmlspecialchars($detail->time_slot ?? $detail->booking_time ?? 'N/A');
                $html .= '<tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;">' . $timeSlot . $dayOfWeek . '</td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;">' . $detail->duration . ' hour' . ($detail->duration > 1 ? 's' : '') . '</td>
                    <td style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">₹' . number_format($slotPrice, 2) . '</td>
                </tr>';
            }
            $html .= '<tr style="border-top: 2px solid #333; font-weight: bold;">
                    <td style="padding: 10px;" colspan="2">Total Duration</td>
                    <td style="padding: 10px; text-align: right;">' . $totalDuration . ' hour' . ($totalDuration > 1 ? 's' : '') . '</td>
                </tr>
            </tbody>
            </table>
        </div>';
        }
        
        if ($ground) {
            $features = $ground->features->isNotEmpty() 
                ? $ground->features->pluck('feature_name')->implode(', ') 
                : 'None';
            
            $html .= '<div class="booking-details">
                <h3>Ground Details</h3>
                <table>
                    <tr><th>Ground Name</th><td>' . htmlspecialchars($ground->name) . '</td></tr>
                    <tr><th>Location</th><td>' . htmlspecialchars($ground->location) . '</td></tr>
                    <tr><th>Contact</th><td>' . htmlspecialchars($ground->phone ?? 'N/A') . '</td></tr>
                    <tr><th>Additional Services</th><td>' . htmlspecialchars($features) . '</td></tr>
                </table>
            </div>';
        }
        
        $paymentMethod = $booking->payment ? ucfirst($booking->payment->payment_method) : 'N/A';
        $paymentStatus = $booking->payment ? ucfirst($booking->payment->payment_status) : 'N/A';
        $paymentDate = $booking->payment && $booking->payment->date 
            ? \Carbon\Carbon::parse($booking->payment->date)->format('d M Y') 
            : 'N/A';
        $transactionId = $booking->payment && $booking->payment->transaction_id 
            ? htmlspecialchars($booking->payment->transaction_id) 
            : 'N/A';
        
        $html .= '<div class="booking-details">
            <h3>Payment Information</h3>
            <table>
                <tr><th>Payment Method</th><td>' . htmlspecialchars($paymentMethod) . '</td></tr>
                <tr><th>Payment Status</th><td>' . htmlspecialchars($paymentStatus) . '</td></tr>
                <tr><th>Payment Date</th><td>' . $paymentDate . '</td></tr>
                <tr><th>Transaction ID</th><td>' . $transactionId . '</td></tr>
            </table>
        </div>
        
        <div class="booking-details">
            <h3>Price Breakdown</h3>
            <table>
                <tr><th>Base Price</th><td>₹' . number_format($basePrice, 2) . '</td></tr>
                <tr><th>GST (18%)</th><td>₹' . number_format($gst, 2) . '</td></tr>
                <tr><th>Platform Fee</th><td>₹' . number_format($platformFee, 2) . '</td></tr>
                <tr style="border-top: 2px solid #333; font-weight: bold;">
                    <th>Total Amount</th><td>₹' . number_format($booking->amount, 2) . '</td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <p>Thank you for choosing our service!</p>
            <p>This is a computer-generated invoice, no signature required.</p>
        </div>
</body>
</html>';
        
        return $html;
    }
}
