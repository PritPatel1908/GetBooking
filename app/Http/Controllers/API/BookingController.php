<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Ground;
use App\Models\GroundSlot;
use App\Models\Payment;
use App\Models\User;
use App\Models\GroundImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Get all bookings for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            // Get pagination parameters
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $status = $request->get('status'); // Filter by booking_status

            // Build query with all relationships - load all fields to ensure relationships work
            $query = Booking::with([
                'user',
                'slot.ground.images',
                'slot.ground.features',
                'details.ground.images',
                'details.ground.features',
                'details.slot',
                'ground.images',
                'ground.features',
                'payment',
                'paymentByBookingId'
            ])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Apply status filter if provided
            if ($status) {
                $query->where('booking_status', $status);
            }

            // Paginate results
            $bookings = $query->paginate($perPage, ['*'], 'page', $page);

            // Format bookings data
            $formattedBookings = $bookings->map(function ($booking) {
                // Get first detail for reference
                $firstDetail = $booking->details->first();

                // Get ground from relationship (hasOneThrough) or from first detail
                $ground = $booking->ground;

                // If ground relationship not loaded, try from first detail
                if (!$ground && $firstDetail) {
                    $ground = $firstDetail->ground;
                }

                // If still no ground, try from slot
                // First ensure slot is loaded if slot_id exists
                if (!$ground && $booking->slot_id) {
                    if (!$booking->slot) {
                        $booking->load('slot.ground.images', 'slot.ground.features');
                    }
                    if ($booking->slot && $booking->slot->ground) {
                        $ground = $booking->slot->ground;
                    }
                }

                // Calculate total duration from details
                $totalDuration = $booking->details->sum('duration');

                return [
                    // Booking basic info
                    'id' => $booking->id,
                    'booking_sku' => $booking->booking_sku,
                    'user_id' => $booking->user_id,
                    'booking_date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                    'booking_date_formatted' => $booking->booking_date ? $booking->booking_date->format('d M Y') : null,
                    'booking_time' => $booking->booking_time ?: ($firstDetail ? $firstDetail->time_slot : ''),
                    'duration' => $totalDuration ?: $booking->duration,
                    'amount' => (float) $booking->amount,
                    'amount_formatted' => '₹' . number_format($booking->amount, 2),
                    'booking_status' => $booking->booking_status,
                    'payment_status' => $booking->payment_status,
                    'notes' => $booking->notes,
                    'created_at' => $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : null,
                    'created_at_formatted' => $booking->created_at ? $booking->created_at->format('d M Y, h:i A') : null,
                    'updated_at' => $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i:s') : null,
                    'updated_at_formatted' => $booking->updated_at ? $booking->updated_at->format('d M Y, h:i A') : null,

                    // User info
                    'user' => $booking->user ? [
                        'id' => $booking->user->id,
                        'name' => $booking->user->name,
                        'email' => $booking->user->email,
                        'phone' => $booking->user->phone,
                        'address' => $booking->user->address,
                        'city' => $booking->user->city,
                        'state' => $booking->user->state,
                        'postal_code' => $booking->user->postal_code,
                    ] : null,

                    // Ground info (from first detail or slot)
                    'ground' => $this->formatGround($ground),

                    // Ground slot detail - slot details from booking details
                    'ground_slot_detail' => $this->getGroundSlotDetails($booking),

                    // Booking details (all slots) - if empty, create from booking slot
                    'details' => $this->getBookingDetails($booking),

                    // Payment info - try both payment_id and booking_id
                    'payment' => $this->getPaymentForBooking($booking),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Bookings retrieved successfully',
                'data' => $formattedBookings,
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'last_page' => $bookings->lastPage(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve bookings',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get booking details by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $booking = Booking::with([
                'user',
                'slot.ground.images',
                'slot.ground.features',
                'details.ground.images',
                'details.ground.features',
                'details.slot',
                'ground.images',
                'ground.features',
                'payment',
                'paymentByBookingId'
            ])
                ->where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            // Get ground from relationship (hasOneThrough) or from first detail
            $ground = $booking->ground;

            // If ground relationship not loaded, try from first detail
            if (!$ground && $booking->details->isNotEmpty()) {
                $ground = $booking->details->first()->ground;
            }

            // If still no ground, try from slot
            if (!$ground && $booking->slot) {
                $ground = $booking->slot->ground;
            }

            $formattedBooking = $this->formatBooking($booking, $ground);

            return response()->json([
                'success' => true,
                'message' => 'Booking retrieved successfully',
                'data' => $formattedBooking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve booking',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get booking details with ground and slot information
     *
     * @param Booking $booking
     * @return array
     */
    private function getBookingDetails($booking)
    {
        // If booking has details, format and return them
        if ($booking->details && $booking->details->count() > 0) {
            return $booking->details->map(function ($detail) {
                // Ensure ground is loaded
                if (!$detail->ground && $detail->ground_id) {
                    $detail->setRelation('ground', Ground::with(['images', 'features'])->find($detail->ground_id));
                }

                // Ensure slot is loaded
                if (!$detail->slot && $detail->slot_id) {
                    $detail->setRelation('slot', GroundSlot::find($detail->slot_id));
                }

                return [
                    'id' => $detail->id,
                    'booking_id' => $detail->booking_id,
                    'ground_id' => $detail->ground_id,
                    'slot_id' => $detail->slot_id,
                    'booking_time' => $detail->booking_time,
                    'time_slot' => $detail->time_slot,
                    'duration' => $detail->duration,
                    'price' => $detail->price ? (float) $detail->price : null,
                    'created_at' => $detail->created_at ? $detail->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $detail->updated_at ? $detail->updated_at->format('Y-m-d H:i:s') : null,
                    'slot' => $this->formatSlot($detail->slot),
                    'ground' => $this->formatGround($detail->ground),
                ];
            })->values();
        }

        // If no details but booking has a slot, create detail from slot
        if ($booking->slot_id) {
            $slot = null;

            // Load slot with ground if not already loaded
            if (!$booking->slot) {
                $slot = GroundSlot::with(['ground.images', 'ground.features'])->find($booking->slot_id);
                if ($slot) {
                    $booking->setRelation('slot', $slot);
                }
            } else {
                $slot = $booking->slot;
                // Ensure ground is loaded for slot
                if (!$slot->relationLoaded('ground') && $slot->ground_id) {
                    $slot->load('ground.images', 'ground.features');
                }
            }

            if ($slot) {
                $ground = $slot->ground;

                // If ground still not loaded, load it directly
                if (!$ground && $slot->ground_id) {
                    $ground = Ground::with(['images', 'features'])->find($slot->ground_id);
                }

                return [[
                    'id' => null,
                    'booking_id' => $booking->id,
                    'ground_id' => $ground ? $ground->id : ($slot->ground_id ?? null),
                    'slot_id' => $slot->id,
                    'booking_time' => $booking->booking_time,
                    'time_slot' => $booking->booking_time ?: ($slot->start_time && $slot->end_time ? $slot->start_time . ' - ' . $slot->end_time : $slot->slot_name),
                    'duration' => $booking->duration,
                    'price' => $booking->amount ? (float) $booking->amount : ($slot->price_per_slot ? (float) $slot->price_per_slot : null),
                    'created_at' => $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i:s') : null,
                    'slot' => $this->formatSlot($slot),
                    'ground' => $this->formatGround($ground),
                ]];
            }
        }

        // Last resort: try to get ground from booking_details table even if empty
        if ($booking->id) {
            $firstDetail = BookingDetail::where('booking_id', $booking->id)
                ->with(['ground.images', 'ground.features', 'slot'])
                ->first();

            if ($firstDetail) {
                // Ensure ground is loaded
                if (!$firstDetail->ground && $firstDetail->ground_id) {
                    $firstDetail->ground = Ground::with(['images', 'features'])->find($firstDetail->ground_id);
                }

                // Ensure slot is loaded
                if (!$firstDetail->slot && $firstDetail->slot_id) {
                    $firstDetail->slot = GroundSlot::find($firstDetail->slot_id);
                }

                return [[
                    'id' => $firstDetail->id,
                    'booking_id' => $firstDetail->booking_id,
                    'ground_id' => $firstDetail->ground_id,
                    'slot_id' => $firstDetail->slot_id,
                    'booking_time' => $firstDetail->booking_time,
                    'time_slot' => $firstDetail->time_slot,
                    'duration' => $firstDetail->duration,
                    'price' => $firstDetail->price ? (float) $firstDetail->price : null,
                    'created_at' => $firstDetail->created_at ? $firstDetail->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $firstDetail->updated_at ? $firstDetail->updated_at->format('Y-m-d H:i:s') : null,
                    'slot' => $this->formatSlot($firstDetail->slot),
                    'ground' => $this->formatGround($firstDetail->ground),
                ]];
            }
        }

        return [];
    }

    /**
     * Get ground slot details from booking details
     *
     * @param Booking $booking
     * @return array
     */
    private function getGroundSlotDetails($booking)
    {
        $slotDetails = [];

        // Get slot details from booking details
        if ($booking->details && $booking->details->count() > 0) {
            foreach ($booking->details as $detail) {
                if ($detail->slot) {
                    $slotDetails[] = $this->formatSlot($detail->slot);
                } elseif ($detail->slot_id) {
                    // If slot relationship not loaded but slot_id exists, load it
                    $slot = GroundSlot::find($detail->slot_id);
                    if ($slot) {
                        $detail->setRelation('slot', $slot);
                        $slotDetails[] = $this->formatSlot($slot);
                    }
                }
            }
        }

        // Fallback: If no details but booking has a slot, use that
        if (empty($slotDetails) && $booking->slot_id) {
            if (!$booking->slot) {
                $slot = GroundSlot::find($booking->slot_id);
                if ($slot) {
                    $booking->setRelation('slot', $slot);
                }
            }
            if ($booking->slot) {
                $slotDetails[] = $this->formatSlot($booking->slot);
            }
        }

        // Return first slot detail or null
        return !empty($slotDetails) ? $slotDetails[0] : null;
    }

    /**
     * Format slot data for API response
     *
     * @param GroundSlot|null $slot
     * @return array|null
     */
    private function formatSlot($slot)
    {
        if (!$slot) {
            return null;
        }

        return [
            'id' => $slot->id,
            'ground_id' => $slot->ground_id,
            'slot_name' => $slot->slot_name,
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'slot_type' => $slot->slot_type,
            'day_of_week' => $slot->day_of_week,
            'slot_status' => $slot->slot_status,
            'price_per_slot' => $slot->price_per_slot ? (float) $slot->price_per_slot : null,
            'time_range' => $slot->time_range ?? ($slot->start_time && $slot->end_time ? $slot->start_time . ' - ' . $slot->end_time : $slot->slot_name),
        ];
    }

    /**
     * Format ground data for API response
     *
     * @param Ground|null $ground
     * @return array|null
     */
    private function formatGround($ground)
    {
        if (!$ground) {
            return null;
        }

        return [
            'id' => $ground->id,
            'name' => $ground->name,
            'location' => $ground->location,
            'description' => $ground->description,
            'ground_image' => $ground->ground_image ? asset($ground->ground_image) : null,
            'phone' => $ground->phone,
            'email' => $ground->email,
            'capacity' => $ground->capacity,
            'ground_type' => $ground->ground_type,
            'ground_category' => $ground->ground_category,
            'opening_time' => $ground->opening_time,
            'closing_time' => $ground->closing_time,
            'status' => $ground->status,
            'rules' => $ground->rules,
            'images' => $ground->images ? $ground->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'ground_id' => $image->ground_id,
                    'image_path' => $image->image_path,
                    'image_url' => $image->image_url ?? asset($image->image_path),
                ];
            })->values() : [],
            'features' => $ground->features ? $ground->features->map(function ($feature) {
                return [
                    'id' => $feature->id,
                    'ground_id' => $feature->ground_id,
                    'feature_name' => $feature->feature_name,
                    'feature_type' => $feature->feature_type,
                    'feature_status' => $feature->feature_status,
                ];
            })->values() : [],
        ];
    }

    /**
     * Get payment for booking (handles both payment_id and booking_id relationships)
     *
     * @param Booking $booking
     * @return array|null
     */
    private function getPaymentForBooking($booking)
    {
        // Try payment via payment_id first
        $payment = $booking->payment;

        // If payment not loaded via payment_id, try via booking_id relationship
        if (!$payment) {
            $payment = $booking->paymentByBookingId;
        }

        // Last resort: direct query
        if (!$payment && $booking->id) {
            $payment = Payment::where('booking_id', $booking->id)->first();
        }

        if (!$payment) {
            return null;
        }

        return [
            'id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'user_id' => $payment->user_id,
            'transaction_id' => $payment->transaction_id,
            'payment_method' => $payment->payment_method,
            'payment_status' => $payment->payment_status,
            'payment_type' => $payment->payment_type,
            'amount' => (float) $payment->amount,
            'amount_formatted' => '₹' . number_format($payment->amount, 2),
            'date' => $payment->date ? $payment->date->format('Y-m-d') : null,
            'date_formatted' => $payment->date ? $payment->date->format('d M Y') : null,
            'payment_url' => $payment->payment_url,
            'payment_response' => $payment->payment_response,
            'payment_response_code' => $payment->payment_response_code,
            'payment_response_message' => $payment->payment_response_message,
            'created_at' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : null,
            'created_at_formatted' => $payment->created_at ? $payment->created_at->format('d M Y, h:i A') : null,
        ];
    }

    /**
     * Format booking data for API response
     *
     * @param Booking $booking
     * @param Ground|null $ground
     * @return array
     */
    private function formatBooking($booking, $ground)
    {
        return [
            // Booking basic info
            'id' => $booking->id,
            'booking_sku' => $booking->booking_sku,
            'user_id' => $booking->user_id,
            'booking_date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
            'booking_date_formatted' => $booking->booking_date ? $booking->booking_date->format('d M Y') : null,
            'booking_time' => $booking->booking_time ?: ($booking->details->first() ? $booking->details->first()->time_slot : ''),
            'duration' => $booking->details->sum('duration') ?: $booking->duration,
            'amount' => (float) $booking->amount,
            'amount_formatted' => '₹' . number_format($booking->amount, 2),
            'booking_status' => $booking->booking_status,
            'payment_status' => $booking->payment_status,
            'notes' => $booking->notes,
            'created_at' => $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : null,
            'created_at_formatted' => $booking->created_at ? $booking->created_at->format('d M Y, h:i A') : null,
            'updated_at' => $booking->updated_at ? $booking->updated_at->format('Y-m-d H:i:s') : null,
            'updated_at_formatted' => $booking->updated_at ? $booking->updated_at->format('d M Y, h:i A') : null,

            // User info
            'user' => $booking->user ? [
                'id' => $booking->user->id,
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone,
                'address' => $booking->user->address,
                'city' => $booking->user->city,
                'state' => $booking->user->state,
                'postal_code' => $booking->user->postal_code,
            ] : null,

            // Ground info (from first detail or slot)
            'ground' => $this->formatGround($ground),

            // Ground slot detail - slot details from booking details
            'ground_slot_detail' => $this->getGroundSlotDetails($booking),

            // Booking details (all slots) - if empty, create from booking slot
            'details' => $this->getBookingDetails($booking),

            // Payment info
            'payment' => $this->getPaymentForBooking($booking),
        ];
    }

    /**
     * Get booking details by SKU
     *
     * @param string $sku
     * @return \Illuminate\Http\JsonResponse
     */
    public function showBySku($sku)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $booking = Booking::with([
                'user',
                'slot.ground.images',
                'slot.ground.features',
                'details.ground.images',
                'details.ground.features',
                'details.slot',
                'ground.images',
                'ground.features',
                'payment',
                'paymentByBookingId'
            ])
                ->where('user_id', $user->id)
                ->where('booking_sku', $sku)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            // Get ground from relationship (hasOneThrough) or from first detail
            $ground = $booking->ground;

            // If ground relationship not loaded, try from first detail
            if (!$ground && $booking->details->isNotEmpty()) {
                $ground = $booking->details->first()->ground;
            }

            // If still no ground, try from slot
            if (!$ground && $booking->slot) {
                $ground = $booking->slot->ground;
            }

            $formattedBooking = $this->formatBooking($booking, $ground);

            return response()->json([
                'success' => true,
                'message' => 'Booking retrieved successfully',
                'data' => $formattedBooking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve booking',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Debug endpoint to check booking data
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function debug($id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $booking = Booking::find($id);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found',
                ], 404);
            }

            // Get all related data
            $bookingDetails = BookingDetail::where('booking_id', $id)->get();
            $slot = $booking->slot_id ? GroundSlot::find($booking->slot_id) : null;
            $payment = Payment::where('booking_id', $id)->first();

            $debugData = [
                'booking' => [
                    'id' => $booking->id,
                    'booking_sku' => $booking->booking_sku,
                    'user_id' => $booking->user_id,
                    'slot_id' => $booking->slot_id,
                    'booking_date' => $booking->booking_date,
                    'amount' => $booking->amount,
                    'booking_status' => $booking->booking_status,
                ],
                'booking_details_count' => $bookingDetails->count(),
                'booking_details' => $bookingDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'booking_id' => $detail->booking_id,
                        'ground_id' => $detail->ground_id,
                        'slot_id' => $detail->slot_id,
                        'booking_time' => $detail->booking_time,
                        'time_slot' => $detail->time_slot,
                        'duration' => $detail->duration,
                        'price' => $detail->price,
                    ];
                }),
                'slot_from_booking' => $slot ? [
                    'id' => $slot->id,
                    'ground_id' => $slot->ground_id,
                    'slot_name' => $slot->slot_name,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                ] : null,
                'ground_from_slot' => $slot && $slot->ground_id ? Ground::with(['images', 'features'])->find($slot->ground_id) : null,
                'ground_from_details' => $bookingDetails->first() && $bookingDetails->first()->ground_id
                    ? Ground::with(['images', 'features'])->find($bookingDetails->first()->ground_id)
                    : null,
                'payment' => $payment ? [
                    'id' => $payment->id,
                    'booking_id' => $payment->booking_id,
                    'amount' => $payment->amount,
                    'payment_status' => $payment->payment_status,
                ] : null,
                'relationships_loaded' => [
                    'has_slot' => $booking->relationLoaded('slot'),
                    'has_details' => $booking->relationLoaded('details'),
                    'has_payment' => $booking->relationLoaded('payment'),
                ],
            ];

            return response()->json([
                'success' => true,
                'message' => 'Debug data retrieved',
                'data' => $debugData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve debug data',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }
}
