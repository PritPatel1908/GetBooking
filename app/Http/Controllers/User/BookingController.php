<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ground;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\GroundSlot;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\Error as RazorpayError;
use Illuminate\Support\Facades\Config;

class BookingController extends Controller
{
    private $razorpay;

    public function __construct()
    {
        try {
            $razorpayKey = Config::get('services.razorpay.key');
            $razorpaySecret = Config::get('services.razorpay.secret');
            
            if (!empty($razorpayKey) && !empty($razorpaySecret)) {
                $this->razorpay = new Api($razorpayKey, $razorpaySecret);
            } else {
                Log::warning('Razorpay credentials not configured');
                $this->razorpay = null;
            }
        } catch (\Exception $e) {
            Log::error('Error initializing Razorpay API: ' . $e->getMessage());
            $this->razorpay = null;
        }
    }

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
                            'price' => round(50 * $hours), // Default price for generated slots
                            'hours' => $hours,
                            'available' => true // Default is available, we'll check bookings later
                        ];
                    }
                }
            } else {
                // Use slots from database
                foreach ($groundSlots as $slot) {
                    // Calculate the actual duration based on start_time and end_time
                    $hours = 2; // Default

                    if ($slot->start_time && $slot->end_time) {
                        try {
                            $startTime = Carbon::parse($slot->start_time);
                            $endTime = Carbon::parse($slot->end_time);

                            // Handle slots that cross midnight
                            if ($endTime < $startTime) {
                                $endTime->addDay();
                            }

                            $hours = $endTime->diffInHours($startTime);
                            $hours = max(1, abs($hours)); // Ensure positive and at least 1 hour
                        } catch (\Exception $e) {
                            // If time parsing fails, use default
                        }
                    } else {
                        // Fallback to old method with slot_name
                        $slotTime = explode('-', $slot->slot_name);
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
                    }

                    $slots[] = [
                        'id' => $slot->id,
                        'time' => $slot->time_range, // Use the accessor
                        'day_of_week' => $slot->day_of_week,
                        'price' => round($slot->price_per_slot * $hours),
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

            // Determine the earliest start time from booking details (handles multiple slots)
            $earliestStartTime = null;
            if ($booking->relationLoaded('details')) {
                $details = $booking->details;
            } else {
                $details = $booking->details()->get();
            }

            if ($details && $details->isNotEmpty()) {
                foreach ($details as $detail) {
                    // Prefer explicit time_slot like "10:00 - 11:00"
                    $timeSlot = $detail->time_slot ?: $detail->booking_time;
                    if (is_string($timeSlot) && strpos($timeSlot, '-') !== false) {
                        [$startStr, $endStr] = array_map('trim', explode('-', $timeSlot, 2));
                        try {
                            $start = Carbon::parse($startStr);
                            if (!$earliestStartTime || $start->lt($earliestStartTime)) {
                                $earliestStartTime = $start;
                            }
                        } catch (\Exception $e) {
                            // Ignore parse errors and continue
                        }
                    } elseif ($detail->slot && $detail->slot->start_time) {
                        try {
                            $start = Carbon::parse($detail->slot->start_time);
                            if (!$earliestStartTime || $start->lt($earliestStartTime)) {
                                $earliestStartTime = $start;
                            }
                        } catch (\Exception $e) {
                            // Ignore parse errors and continue
                        }
                    }
                }
            }

            // Fallbacks if we couldn't determine start time
            if (!$earliestStartTime) {
                // Try legacy single booking_time if present and valid
                try {
                    if (!empty($booking->booking_time)) {
                        // If booking_time contains multiple entries, pick the first range part
                        $firstPart = is_string($booking->booking_time) ? explode(',', $booking->booking_time)[0] : null;
                        if ($firstPart && strpos($firstPart, '-') !== false) {
                            [$startStr] = array_map('trim', explode('-', $firstPart, 2));
                            $earliestStartTime = Carbon::parse($startStr);
                        } else {
                            $earliestStartTime = Carbon::parse($booking->booking_time);
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore and use default below
                }
            }

            // Final fallback to 08:00 if still not determined
            $startTimeFormatted = ($earliestStartTime ? $earliestStartTime->format('H:i:s') : '08:00:00');
            $bookingDateTime = Carbon::parse($bookingDate . ' ' . $startTimeFormatted);
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

            // If there's a payment, update its status to cancelled
            if ($booking->payment) {
                $booking->payment->payment_status = 'cancelled';
                // Use existing columns instead of non-existent 'notes'
                $booking->payment->payment_response_message = 'Payment cancelled due to booking cancellation.';
                $booking->payment->payment_response_data_json = json_encode([
                    'refund_percentage' => $refundPercentage,
                    'refund_amount' => $refundAmount,
                    'reason' => 'booking_cancelled'
                ]);
                $booking->payment->save();
            }

            // You might want to send an email notification here

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully.',
                'refundPercentage' => $refundPercentage,
                'refundAmount' => $refundAmount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in cancelBooking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new booking
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'ground_id' => 'required|exists:grounds,id',
                'date' => 'required|date|after_or_equal:today',
                'slot_ids' => 'required|array',
                'slot_ids.*' => 'required|exists:ground_slots,id',
                'time_slots' => 'required|array',
                'time_slots.*' => 'required|string',
                'total_price' => 'required|numeric|min:0'
            ]);

            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to make a booking.'
                ], 401);
            }

            // Get the ground
            $ground = Ground::findOrFail($request->ground_id);

            // Check if slots are still available
            $bookedSlots = BookingDetail::where('ground_id', $ground->id)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('booking_date', $request->date)
                        ->where('booking_status', '!=', 'cancelled');
                })
                ->pluck('slot_id')
                ->toArray();

            $requestedSlots = $request->slot_ids;
            $unavailableSlots = array_intersect($requestedSlots, $bookedSlots);

            if (!empty($unavailableSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some selected slots are no longer available. Please refresh and try again.'
                ], 400);
            }

            // Generate unique booking SKU (ensure uniqueness)
            $maxAttempts = 10;
            $attempt = 0;
            $bookingSku = null;
            
            do {
                $bookingSku = 'BK' . strtoupper(uniqid('', true)) . rand(1000, 9999);
                $exists = Booking::where('booking_sku', $bookingSku)->exists();
                $attempt++;
            } while ($exists && $attempt < $maxAttempts);
            
            if ($exists) {
                Log::error('Failed to generate unique booking SKU after ' . $maxAttempts . ' attempts');
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating booking ID. Please try again.'
                ], 500);
            }

            // Create pending booking record first (so it appears in booking list even if payment is not completed)
            try {
                $booking = new Booking();
                $booking->user_id = Auth::id();
                $booking->booking_date = $request->date;
                $booking->amount = $request->total_price;
                $booking->booking_status = 'pending'; // Pending until payment is completed
                $booking->booking_sku = $bookingSku;
                $booking->notes = 'Payment pending - Order created';
                $booking->save();
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error creating booking: ' . $e->getMessage(), [
                    'code' => $e->getCode(),
                    'sql_state' => $e->getSqlState() ?? 'N/A',
                    'error_info' => $e->errorInfo ?? []
                ]);
                
                // Check if it's a unique constraint violation
                if ($e->getCode() == '23000' || (isset($e->errorInfo[0]) && $e->errorInfo[0] == '23000')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking ID conflict. Please try again.',
                        'debug' => config('app.debug') ? 'Unique constraint violation on booking_sku' : null
                    ], 500);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Database error while creating booking. Please try again.',
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            // Create booking details for each slot
            try {
                foreach ($request->slot_ids as $index => $slotId) {
                    $timeSlot = isset($request->time_slots[$index]) ? $request->time_slots[$index] : null;
                    
                    // Calculate duration for this specific slot
                    $slotDuration = 2; // Default
                    if ($timeSlot) {
                        $times = explode('-', $timeSlot);
                        if (count($times) == 2) {
                            $startTime = Carbon::parse(trim($times[0]));
                            $endTime = Carbon::parse(trim($times[1]));
                            
                            if ($endTime < $startTime) {
                                $endTime->addDay();
                            }
                            
                            $slotDuration = max(1, abs($endTime->diffInHours($startTime)));
                        }
                    }

                    $slotPrice = $request->total_price / count($request->slot_ids);

                    $bookingDetail = new BookingDetail();
                    $bookingDetail->booking_id = $booking->id;
                    $bookingDetail->ground_id = $ground->id;
                    $bookingDetail->slot_id = $slotId;
                    $bookingDetail->booking_time = $timeSlot;
                    $bookingDetail->time_slot = $timeSlot;
                    $bookingDetail->duration = $slotDuration;
                    $bookingDetail->price = $slotPrice;
                    $bookingDetail->save();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                // Clean up booking if details creation fails
                $booking->delete();
                Log::error('Database error creating booking details: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating booking details. Please try again.',
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            // Create pending payment record
            try {
                $payment = new Payment();
                $payment->booking_id = $booking->id;
                $payment->user_id = Auth::id();
                $payment->date = now();
                $payment->amount = $request->total_price;
                $payment->payment_status = 'pending';
                $payment->payment_method = 'online';
                $payment->payment_type = 'razorpay';
                $payment->payment_response = json_encode(['status' => 'pending', 'message' => 'Order created, awaiting payment']); // Required field - cannot be NULL
                $payment->payment_response_data = json_encode(['status' => 'pending', 'order_created' => true]); // Required field - cannot be NULL
                $payment->payment_response_message = 'Payment pending - Order created';
                $payment->save();
            } catch (\Illuminate\Database\QueryException $e) {
                // Clean up booking and details if payment creation fails
                $booking->details()->delete();
                $booking->delete();
                Log::error('Database error creating payment: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating payment record. Please try again.',
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            // Update booking with payment ID
            $booking->payment_id = $payment->id;
            $booking->save();

            // Validate Razorpay API instance
            if ($this->razorpay === null) {
                Log::error('Razorpay API is not initialized in store method');
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway configuration error. Please contact support.',
                    'debug' => config('app.debug') ? 'Razorpay API instance is null' : null
                ], 500);
            }

            // Validate Razorpay credentials
            $razorpayKey = Config::get('services.razorpay.key');
            $razorpaySecret = Config::get('services.razorpay.secret');
            
            if (empty($razorpayKey) || empty($razorpaySecret)) {
                Log::error('Razorpay credentials are missing', [
                    'key_exists' => !empty($razorpayKey),
                    'secret_exists' => !empty($razorpaySecret)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway configuration error. Please contact support.',
                    'debug' => config('app.debug') ? 'Razorpay credentials missing' : null
                ], 500);
            }

            // Validate amount (must be at least 1 rupee = 100 paise)
            $amountInPaise = $request->total_price * 100;
            if ($amountInPaise < 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum booking amount is ₹1.00'
                ], 400);
            }

            // Create Razorpay Order
            $orderData = [
                'receipt'         => $bookingSku,
                'amount'          => (int) $amountInPaise, // Razorpay expects amount in paise as integer
                'currency'        => 'INR',
                'payment_capture' => 1,
                'notes'           => [
                    'booking_id' => $booking->id,
                    'booking_sku' => $bookingSku
                ]
            ];

            Log::info('Attempting to create Razorpay order', [
                'orderData' => $orderData,
                'razorpayKey' => substr($razorpayKey, 0, 10) . '...' // Log partial key for debugging
            ]);

            try {
                $razorpayOrder = $this->razorpay->order->create($orderData);
                Log::info('Razorpay order created successfully', [
                    'order_id' => $razorpayOrder['id'] ?? 'N/A'
                ]);
            } catch (RazorpayError $e) {
                $errorDetails = [
                    'code' => $e->getCode(),
                    'description' => $e->getDescription(),
                    'field' => $e->getField(),
                    'source' => $e->getSource(),
                    'step' => $e->getStep(),
                    'reason' => $e->getReason(),
                    'metadata' => $e->getMetadata(),
                    'orderData' => $orderData,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
                
                Log::error('Razorpay API Error: ' . $e->getMessage(), $errorDetails);
                
                // Clean up the booking, payment, and booking details records
                try {
                    if ($booking->details) {
                        $booking->details()->delete();
                    }
                    $payment->delete();
                    $booking->delete();
                } catch (\Exception $cleanupError) {
                    Log::error('Error cleaning up booking records: ' . $cleanupError->getMessage());
                }
                
                $errorMessage = $e->getDescription() ?: $e->getMessage() ?: 'Unknown Razorpay error';
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error: ' . $errorMessage . '. Please try again or contact support.',
                    'debug' => config('app.debug') ? [
                        'razorpay_error_code' => $e->getCode(),
                        'razorpay_error_description' => $e->getDescription(),
                        'razorpay_error_field' => $e->getField(),
                    ] : null
                ], 500);
            } catch (\Exception $e) {
                $errorDetails = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'orderData' => $orderData,
                    'exception_class' => get_class($e)
                ];
                
                Log::error('Unexpected error creating Razorpay order: ' . $e->getMessage(), $errorDetails);
                
                // Clean up the booking, payment, and booking details records
                try {
                    if ($booking->details) {
                        $booking->details()->delete();
                    }
                    $payment->delete();
                    $booking->delete();
                } catch (\Exception $cleanupError) {
                    Log::error('Error cleaning up booking records: ' . $cleanupError->getMessage());
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error initializing payment. Please try again.',
                    'debug' => config('app.debug') ? [
                        'error_message' => $e->getMessage(),
                        'error_file' => $e->getFile(),
                        'error_line' => $e->getLine(),
                        'exception_class' => get_class($e)
                    ] : null
                ], 500);
            }

            // Update payment record with Razorpay order ID
            $payment->transaction_id = $razorpayOrder['id']; // Store order ID temporarily
            $payment->payment_response_data = json_encode([
                'razorpay_order_id' => $razorpayOrder['id']
            ]);
            $payment->save();

            // Store booking data in session for later use
            session([
                'pending_booking' => [
                    'booking_id' => $booking->id,
                    'ground_id' => $ground->id,
                    'date' => $request->date,
                    'slot_ids' => $request->slot_ids,
                    'time_slots' => $request->time_slots,
                    'total_price' => $request->total_price,
                    'booking_sku' => $bookingSku,
                    'razorpay_order_id' => $razorpayOrder['id']
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Razorpay order created successfully!',
                'order_id' => $razorpayOrder['id'],
                'amount' => $request->total_price * 100,
                'currency' => 'INR',
                'booking_sku' => $bookingSku,
                'key' => Config::get('services.razorpay.key') // Return Razorpay key for frontend
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in store booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (RazorpayError $e) {
            Log::error('Razorpay error in store booking: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'description' => $e->getDescription(),
                'field' => $e->getField(),
                'source' => $e->getSource(),
                'step' => $e->getStep(),
                'reason' => $e->getReason(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getDescription() . '. Please try again or contact support.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in store booking: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Error creating booking: ' . $e->getMessage() : 'Error creating booking. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * Store a booking with offline payment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeOffline(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'ground_id' => 'required|exists:grounds,id',
                'date' => 'required|date|after_or_equal:today',
                'slot_ids' => 'required|array',
                'slot_ids.*' => 'nullable', // Allow null for dynamically generated slots
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

            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to make a booking.'
                ], 401);
            }

            // Get the ground
            $ground = Ground::findOrFail($request->ground_id);

            // Verify the selected date is not in the past
            $bookingDate = Carbon::parse($request->date);
            $today = Carbon::today();

            if ($bookingDate->lt($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot book for past dates.'
                ], 400);
            }

            // Get existing bookings for this ground and date through booking details
            $existingBookingDetails = BookingDetail::where('ground_id', $ground->id)
                ->whereHas('booking', function ($query) use ($request) {
                    $query->where('booking_date', $request->date)
                        ->where('booking_status', '!=', 'cancelled');
                })
                ->with('booking')
                ->get();

            // Check if any of the selected slots are already booked by time overlap
            foreach ($request->time_slots as $index => $timeSlot) {
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
                $startTimeObj = Carbon::parse($startTime);
                $endTimeObj = Carbon::parse($endTime);

                // Handle slots that cross midnight
                if ($endTimeObj < $startTimeObj) {
                    $endTimeObj->addDay();
                }

                $duration = $endTimeObj->diffInHours($startTimeObj);
                $duration = max(1, abs($duration)); // Ensure positive and at least 1 hour

                // Check for overlapping bookings
                foreach ($existingBookingDetails as $detail) {
                    $detailTimeSlot = $detail->time_slot ?? $detail->booking_time;
                    if (!$detailTimeSlot) continue;

                    $detailTimes = explode('-', $detailTimeSlot);
                    if (count($detailTimes) != 2) continue;

                    $detailStart = Carbon::parse(trim($detailTimes[0]));
                    $detailEnd = Carbon::parse(trim($detailTimes[1]));

                    if ($detailEnd < $detailStart) {
                        $detailEnd->addDay();
                    }

                    $detailDuration = max(1, abs($detailEnd->diffInHours($detailStart)));

                    // Check if there's an overlap
                    if (
                        ($startTimeObj >= $detailStart && $startTimeObj < $detailEnd) ||
                        ($endTimeObj > $detailStart && $endTimeObj <= $detailEnd) ||
                        ($startTimeObj <= $detailStart && $endTimeObj >= $detailEnd)
                    ) {
                        return response()->json([
                            'success' => false,
                            'message' => "The time slot $startTime-$endTime is already booked. Please refresh and try again."
                        ], 409); // 409 Conflict
                    }
                }
            }

            // Generate unique booking SKU (ensure uniqueness)
            $maxAttempts = 10;
            $attempt = 0;
            $bookingSku = null;
            
            do {
                $bookingSku = 'BK' . strtoupper(uniqid('', true)) . rand(1000, 9999);
                $exists = Booking::where('booking_sku', $bookingSku)->exists();
                $attempt++;
            } while ($exists && $attempt < $maxAttempts);
            
            if ($exists) {
                Log::error('Failed to generate unique booking SKU after ' . $maxAttempts . ' attempts');
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating booking ID. Please try again.'
                ], 500);
            }

            // Create booking with offline payment status
            $booking = new Booking();
            $booking->user_id = Auth::id();
            $booking->booking_date = $request->date;
            $booking->amount = $request->total_price;
            $booking->booking_status = 'pending'; // Pending until payment is verified
            $booking->booking_sku = $bookingSku;
            $booking->notes = 'Offline payment - Payment to be made at ground';
            $booking->save();

            // Create booking details for each slot
            foreach ($request->time_slots as $index => $timeSlot) {
                $slotId = isset($request->slot_ids[$index]) ? $request->slot_ids[$index] : null;
                
                // Calculate duration for this specific slot
                $times = explode('-', $timeSlot);
                $startTime = trim($times[0]);
                $endTime = trim($times[1]);
                $startTimeObj = Carbon::parse($startTime);
                $endTimeObj = Carbon::parse($endTime);

                if ($endTimeObj < $startTimeObj) {
                    $endTimeObj->addDay();
                }

                // Ensure duration saved is always positive and at least 1 hour
                $slotDuration = max(1, abs($endTimeObj->diffInHours($startTimeObj)));
                $slotPrice = $request->total_price / count($request->time_slots);

                // Create booking detail with ground and slot info
                $bookingDetail = new BookingDetail();
                $bookingDetail->booking_id = $booking->id;
                $bookingDetail->ground_id = $ground->id;
                $bookingDetail->slot_id = $slotId;
                $bookingDetail->booking_time = $timeSlot;
                $bookingDetail->time_slot = $timeSlot;
                $bookingDetail->duration = $slotDuration;
                $bookingDetail->price = $slotPrice;
                $bookingDetail->save();
            }

            // Create payment record for offline payment
            $payment = new Payment();
            $payment->booking_id = $booking->id;
            $payment->user_id = Auth::id();
            $payment->date = now();
            $payment->amount = $request->total_price;
            $payment->payment_status = 'pending';
            $payment->payment_method = 'offline';
            $payment->payment_type = 'offline';
            $payment->payment_response = json_encode(['status' => 'pending', 'message' => 'Offline payment - Payment pending']); // Required field - cannot be NULL
            $payment->payment_response_data = json_encode(['status' => 'pending', 'payment_method' => 'offline']); // Required field - cannot be NULL
            $payment->payment_response_message = 'Offline payment - Payment pending';
            $payment->save();

            // Update booking with payment ID
            $booking->payment_id = $payment->id;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully. Please pay at the ground on your booking date.',
                'booking_sku' => $bookingSku,
                'booking_id' => $booking->id,
                'booking' => [
                    'id' => $booking->id,
                    'booking_sku' => $booking->booking_sku,
                    'booking_date' => $booking->booking_date,
                    'amount' => $booking->amount,
                    'booking_status' => $booking->booking_status,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in storeOffline booking: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Please check your booking details.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in storeOffline booking: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Error creating booking: ' . $e->getMessage() : 'Error creating booking. Please try again or contact support.'
            ], 500);
        }
    }

    public function handlePaymentCallback(Request $request)
    {
        try {
            $paymentId = $request->razorpay_payment_id;
            $orderId = $request->razorpay_order_id;
            $signature = $request->razorpay_signature;

            // Validate required parameters
            if (empty($paymentId) || empty($orderId) || empty($signature)) {
                Log::error('Payment callback missing required parameters', [
                    'payment_id' => $paymentId ? 'present' : 'missing',
                    'order_id' => $orderId ? 'present' : 'missing',
                    'signature' => $signature ? 'present' : 'missing'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment callback data. Missing required parameters.'
                ], 400);
            }

            // Verify signature
            $attributes = [
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $orderId,
                'razorpay_signature' => $signature
            ];

            try {
                $this->razorpay->utility->verifyPaymentSignature($attributes);
            } catch (RazorpayError $e) {
                Log::error('Payment signature verification failed', [
                    'error' => $e->getMessage(),
                    'payment_id' => $paymentId,
                    'order_id' => $orderId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment signature verification failed. Please contact support.'
                ], 400);
            }

            // Get payment details from Razorpay
            try {
                $payment = $this->razorpay->payment->fetch($paymentId);
            } catch (RazorpayError $e) {
                Log::error('Failed to fetch payment from Razorpay', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'description' => $e->getDescription(),
                    'payment_id' => $paymentId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify payment with Razorpay: ' . $e->getDescription()
                ], 400);
            }

            // Get pending booking data from session or request
            $pendingBooking = null;

            // Try to get data from session first (for authenticated users)
            if (session('pending_booking') && session('pending_booking')['razorpay_order_id'] == $orderId) {
                $pendingBooking = session('pending_booking');
                $userId = Auth::id();
            }
            // If no data in session, try to get from Razorpay payment metadata
            else {
                // Try to find the order in Razorpay
                try {
                    $order = $this->razorpay->order->fetch($orderId);
                } catch (RazorpayError $e) {
                    Log::error('Failed to fetch order from Razorpay', [
                        'error' => $e->getMessage(),
                        'order_id' => $orderId
                    ]);
                    throw new \Exception('Order not found in Razorpay: ' . $e->getDescription());
                }

                // Extract data from order notes or metadata
                $bookingSku = $order['receipt'] ?? null;
                if (!$bookingSku) {
                    throw new \Exception('Booking SKU not found in Razorpay order');
                }

                $pendingBooking = [
                    'razorpay_order_id' => $orderId,
                    'booking_sku' => $bookingSku
                ];

                // Find the booking data in database if possible
                $tempBooking = Booking::where('booking_sku', $bookingSku)->first();
                if ($tempBooking) {
                    $userId = $tempBooking->user_id;
                    $pendingBooking['booking_id'] = $tempBooking->id;
                    $pendingBooking['date'] = $tempBooking->booking_date;
                    $pendingBooking['total_price'] = $tempBooking->amount;
                    $pendingBooking['ground_id'] = $tempBooking->details->first()->ground_id ?? null;
                    $pendingBooking['slot_ids'] = $tempBooking->details->pluck('slot_id')->toArray();
                    $pendingBooking['time_slots'] = $tempBooking->details->pluck('time_slot')->toArray();
                } else {
                    throw new \Exception('No pending booking found for this order. Booking SKU: ' . $bookingSku);
                }
            }

            if (!$pendingBooking) {
                throw new \Exception('No pending booking data found');
            }

            // Find existing booking or create new one
            $booking = Booking::where('booking_sku', $pendingBooking['booking_sku'])->first();
            
            if (!$booking && isset($pendingBooking['booking_id'])) {
                $booking = Booking::find($pendingBooking['booking_id']);
            }
            
            if (!$booking) {
                // Create new booking if not found
                $booking = new Booking();
                $booking->user_id = $userId ?? null;
                $booking->booking_date = $pendingBooking['date'] ?? now()->format('Y-m-d');
                $booking->amount = $pendingBooking['total_price'] ?? ($payment->amount / 100);
                $booking->booking_sku = $pendingBooking['booking_sku'];
                $booking->booking_status = 'confirmed';
                $booking->notes = 'Booking confirmed after successful payment';
                $booking->save();
            } else {
                // Update existing booking
                $booking->booking_status = 'confirmed';
                $booking->notes = 'Booking confirmed after successful payment';
                $booking->save();
            }

            // Create booking details if we have the data and booking doesn't have details yet
            if (isset($pendingBooking['slot_ids']) && isset($pendingBooking['time_slots']) && $booking->details->isEmpty()) {
                foreach ($pendingBooking['slot_ids'] as $index => $slotId) {
                    $timeSlot = isset($pendingBooking['time_slots'][$index]) ? $pendingBooking['time_slots'][$index] : null;

                    // Calculate duration for this specific slot
                    $slotDuration = 2; // Default
                    if ($timeSlot) {
                        $times = explode('-', $timeSlot);
                        if (count($times) == 2) {
                            $startTime = \Carbon\Carbon::parse(trim($times[0]));
                            $endTime = \Carbon\Carbon::parse(trim($times[1]));

                            if ($endTime < $startTime) {
                                $endTime->addDay();
                            }

                            // Ensure duration saved is always positive and at least 1 hour
                            $slotDuration = max(1, abs($endTime->diffInHours($startTime)));
                        }
                    }

                    $slotPrice = ($pendingBooking['total_price'] ?? $payment->amount / 100) / count($pendingBooking['slot_ids']);

                    BookingDetail::updateOrCreate(
                        [
                            'booking_id' => $booking->id,
                            'slot_id' => $slotId
                        ],
                        [
                            'ground_id' => $pendingBooking['ground_id'] ?? null,
                            'booking_time' => $timeSlot,
                            'duration' => $slotDuration,
                            'price' => $slotPrice,
                            'time_slot' => $timeSlot
                        ]
                    );
                }
            }

            // Update or create payment record
            $paymentRecord = Payment::where('booking_id', $booking->id)->first();
            
            try {
                if ($paymentRecord) {
                    // Update existing payment record
                    $paymentRecord->transaction_id = $paymentId;
                    $paymentRecord->payment_status = 'completed';
                    $paymentRecord->payment_method = 'online';
                    $paymentRecord->payment_type = 'razorpay';
                    $paymentRecord->payment_response = json_encode($payment->toArray());
                    $paymentRecord->payment_response_code = $payment->status ?? 'captured';
                    $paymentRecord->payment_response_message = 'Payment successful';
                    $paymentRecord->payment_response_data = json_encode($attributes);
                    $paymentRecord->save();
                } else {
                    // Create new payment record
                    $paymentRecord = Payment::create([
                        'booking_id' => $booking->id,
                        'user_id' => $userId ?? $booking->user_id,
                        'date' => now(),
                        'amount' => $pendingBooking['total_price'] ?? ($payment->amount / 100),
                        'payment_status' => 'completed',
                        'payment_method' => 'online',
                        'payment_type' => 'razorpay',
                        'transaction_id' => $paymentId,
                        'payment_response' => json_encode($payment->toArray()),
                        'payment_response_code' => $payment->status ?? 'captured',
                        'payment_response_message' => 'Payment successful',
                        'payment_response_data' => json_encode($attributes)
                    ]);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database error updating payment record', [
                    'error' => $e->getMessage(),
                    'booking_id' => $booking->id,
                    'payment_id' => $paymentId
                ]);
                // Don't fail the whole process if payment record update fails
                // The booking is already confirmed
            }

            // Update booking with payment ID if payment record exists
            if ($paymentRecord) {
                $booking->payment_id = $paymentRecord->id;
                $booking->save();
            }

            // Clear the pending booking from session if it exists
            if (session()->has('pending_booking')) {
                session()->forget('pending_booking');
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment successful and booking confirmed!',
                'booking_id' => $booking->id,
                'booking_sku' => $booking->booking_sku
            ]);
        } catch (RazorpayError $e) {
            Log::error('Razorpay error in payment callback', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'description' => $e->getDescription(),
                'payment_id' => $request->razorpay_payment_id ?? 'N/A',
                'order_id' => $request->razorpay_order_id ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getDescription() . '. Please contact support with payment ID: ' . ($request->razorpay_payment_id ?? 'N/A')
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error in payment callback: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'payment_id' => $request->razorpay_payment_id ?? 'N/A',
                'order_id' => $request->razorpay_order_id ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Error processing payment: ' . $e->getMessage() : 'Error processing payment. Please contact support with payment ID: ' . ($request->razorpay_payment_id ?? 'N/A')
            ], 500);
        }
    }

    /**
     * Complete payment for an existing pending booking
     *
     * @param Request $request
     * @param int $bookingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function completePayment(Request $request, $bookingId)
    {
        try {
            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to complete payment.'
                ], 401);
            }

            // Find the booking
            $booking = Booking::with(['details', 'payment', 'ground'])->findOrFail($bookingId);

            // Verify the booking belongs to the current user
            if ($booking->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to complete this payment.'
                ], 403);
            }

            // Check if booking is in pending status
            if ($booking->booking_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This booking cannot be paid. Status: ' . $booking->booking_status
                ], 400);
            }

            // Check if payment already exists and has a Razorpay order
            $payment = $booking->payment;
            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found for this booking.'
                ], 404);
            }

            // If payment already has a Razorpay order ID, reuse it
            if (!empty($payment->transaction_id) && strpos($payment->transaction_id, 'order_') === 0) {
                $razorpayOrderId = $payment->transaction_id;
                
                // Verify the order still exists in Razorpay
                try {
                    $order = $this->razorpay->order->fetch($razorpayOrderId);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Razorpay order found!',
                        'order_id' => $razorpayOrderId,
                        'amount' => (int)($booking->amount * 100),
                        'currency' => 'INR',
                        'booking_sku' => $booking->booking_sku,
                        'key' => Config::get('services.razorpay.key')
                    ]);
                } catch (RazorpayError $e) {
                    // Order doesn't exist, create a new one
                    Log::info('Razorpay order not found, creating new one', [
                        'order_id' => $razorpayOrderId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Validate Razorpay API instance
            if ($this->razorpay === null) {
                Log::error('Razorpay API is not initialized in completePayment method');
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway configuration error. Please contact support.'
                ], 500);
            }

            // Validate amount (must be at least 1 rupee = 100 paise)
            $amountInPaise = $booking->amount * 100;
            if ($amountInPaise < 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum booking amount is ₹1.00'
                ], 400);
            }

            // Create Razorpay Order
            $orderData = [
                'receipt'         => $booking->booking_sku,
                'amount'          => (int) $amountInPaise,
                'currency'        => 'INR',
                'payment_capture' => 1,
                'notes'           => [
                    'booking_id' => $booking->id,
                    'booking_sku' => $booking->booking_sku
                ]
            ];

            Log::info('Creating Razorpay order for existing booking', [
                'booking_id' => $booking->id,
                'booking_sku' => $booking->booking_sku,
                'orderData' => $orderData
            ]);

            try {
                $razorpayOrder = $this->razorpay->order->create($orderData);
                Log::info('Razorpay order created successfully for existing booking', [
                    'order_id' => $razorpayOrder['id'] ?? 'N/A',
                    'booking_id' => $booking->id
                ]);
            } catch (RazorpayError $e) {
                Log::error('Razorpay API Error in completePayment: ' . $e->getMessage(), [
                    'code' => $e->getCode(),
                    'description' => $e->getDescription(),
                    'booking_id' => $booking->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error: ' . $e->getDescription() . '. Please try again or contact support.'
                ], 500);
            } catch (\Exception $e) {
                Log::error('Unexpected error creating Razorpay order in completePayment: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error initializing payment. Please try again.'
                ], 500);
            }

            // Update payment record with Razorpay order ID
            $payment->transaction_id = $razorpayOrder['id'];
            $payment->payment_response_data = json_encode([
                'razorpay_order_id' => $razorpayOrder['id']
            ]);
            $payment->save();

            // Store booking data in session for later use
            session([
                'pending_booking' => [
                    'booking_id' => $booking->id,
                    'ground_id' => $booking->details->first()->ground_id ?? null,
                    'date' => $booking->booking_date,
                    'slot_ids' => $booking->details->pluck('slot_id')->toArray(),
                    'time_slots' => $booking->details->pluck('time_slot')->toArray(),
                    'total_price' => $booking->amount,
                    'booking_sku' => $booking->booking_sku,
                    'razorpay_order_id' => $razorpayOrder['id']
                ]
            ]);

            // Ensure amount is an integer
            $amountInPaise = (int) $amountInPaise;
            
            return response()->json([
                'success' => true,
                'message' => 'Razorpay order created successfully!',
                'order_id' => $razorpayOrder['id'],
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'booking_sku' => $booking->booking_sku,
                'key' => Config::get('services.razorpay.key')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in completePayment: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Error completing payment: ' . $e->getMessage() : 'Error completing payment. Please try again or contact support.'
            ], 500);
        }
    }
}
