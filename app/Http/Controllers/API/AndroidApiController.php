<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ground;
use App\Models\Booking;
use App\Models\GroundSlot;
use App\Models\Slot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AndroidApiController extends Controller
{
    /**
     * User login API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // For now, we'll return a simple success response without token
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * User registration API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'client'
        ]);

        // For now, we'll return a simple success response without token
        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone
            ]
        ]);
    }

    /**
     * Get all active grounds
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllGrounds()
    {
        $grounds = Ground::where('status', 'active')
            ->with(['images'])
            ->get();

        $formattedGrounds = $grounds->map(function ($ground) {
            return [
                'id' => $ground->id,
                'name' => $ground->name,
                'location' => $ground->location,
                'price_per_hour' => $ground->price_per_hour,
                'capacity' => $ground->capacity,
                'ground_type' => $ground->ground_type,
                'ground_category' => $ground->ground_category,
                'description' => $ground->description,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->getImageUrl(),
                'images' => $ground->images->map(function ($image) {
                    return asset($image->image_path);
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'grounds' => $formattedGrounds,
            'count' => $formattedGrounds->count()
        ]);
    }

    /**
     * Get ground details by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroundDetails($id)
    {
        $ground = Ground::with(['images', 'features', 'slots'])->find($id);

        if (!$ground) {
            return response()->json([
                'success' => false,
                'message' => 'Ground not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ground' => [
                'id' => $ground->id,
                'name' => $ground->name,
                'location' => $ground->location,
                'price_per_hour' => $ground->price_per_hour,
                'capacity' => $ground->capacity,
                'ground_type' => $ground->ground_type,
                'ground_category' => $ground->ground_category,
                'description' => $ground->description,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->getImageUrl(),
                'images' => $ground->images->map(function ($image) {
                    return asset($image->image_path);
                }),
                'features' => $ground->features->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'name' => $feature->name
                    ];
                }),
                'slots' => $ground->slots->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time
                    ];
                })
            ]
        ]);
    }

    /**
     * Get available slots for a ground on a specific date
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ground_id' => 'required|exists:grounds,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $ground = Ground::find($request->ground_id);
        $date = $request->date;

        // Get all slots for the ground
        $allSlots = Slot::whereHas('grounds', function ($query) use ($ground) {
            $query->where('ground_id', $ground->id);
        })->get();

        // Get booked slots for the given date
        $bookedSlotIds = Booking::where('ground_id', $ground->id)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('slot_id')
            ->toArray();

        // Filter available slots
        $availableSlots = $allSlots->filter(function ($slot) use ($bookedSlotIds) {
            return !in_array($slot->id, $bookedSlotIds);
        })->values();

        return response()->json([
            'success' => true,
            'available_slots' => $availableSlots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time
                ];
            })
        ]);
    }

    /**
     * Create a new booking
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ground_id' => 'required|exists:grounds,id',
            'slot_id' => 'required|exists:slots,id',
            'booking_date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if slot is available for the date
        $isSlotBooked = Booking::where('ground_id', $request->ground_id)
            ->where('slot_id', $request->slot_id)
            ->where('booking_date', $request->booking_date)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($isSlotBooked) {
            return response()->json([
                'success' => false,
                'message' => 'This slot is already booked for the selected date'
            ], 400);
        }

        $user = Auth::user();
        $ground = Ground::find($request->ground_id);
        $slot = Slot::find($request->slot_id);

        // Calculate booking amount
        $startTime = Carbon::parse($slot->start_time);
        $endTime = Carbon::parse($slot->end_time);
        $hoursDifference = $endTime->diffInHours($startTime);
        $totalAmount = $hoursDifference * $ground->price_per_hour;

        // Create booking
        $booking = Booking::create([
            'user_id' => $user->id,
            'ground_id' => $request->ground_id,
            'slot_id' => $request->slot_id,
            'booking_date' => $request->booking_date,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'booking_id' => 'BK' . time() . rand(100, 999)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => [
                'id' => $booking->id,
                'booking_id' => $booking->booking_id,
                'ground_name' => $ground->name,
                'booking_date' => $booking->booking_date,
                'slot_time' => $slot->start_time . ' - ' . $slot->end_time,
                'total_amount' => $booking->total_amount,
                'status' => $booking->status
            ]
        ]);
    }

    /**
     * Get user bookings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserBookings()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)
            ->with(['ground', 'slot'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_id' => $booking->booking_id,
                    'ground_name' => $booking->ground->name,
                    'ground_image' => $booking->ground->getImageUrl(),
                    'booking_date' => $booking->booking_date,
                    'slot_time' => $booking->slot->start_time . ' - ' . $booking->slot->end_time,
                    'total_amount' => $booking->total_amount,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }

    /**
     * Cancel booking
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelBooking($id)
    {
        $user = Auth::user();
        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Booking is already cancelled'
            ], 400);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }

    /**
     * Update user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Update user data
        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        User::where('id', $user->id)->update($updateData);

        // Get updated user data
        $updatedUser = User::find($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $updatedUser->id,
                'name' => $updatedUser->name,
                'email' => $updatedUser->email,
                'phone' => $updatedUser->phone
            ]
        ]);
    }

    /**
     * Get featured grounds
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFeaturedGrounds()
    {
        $grounds = Ground::where('status', 'active')
            ->where('is_featured', true)
            ->with(['images'])
            ->get();

        $formattedGrounds = $grounds->map(function ($ground) {
            return [
                'id' => $ground->id,
                'name' => $ground->name,
                'location' => $ground->location,
                'price_per_hour' => $ground->price_per_hour,
                'capacity' => $ground->capacity,
                'ground_type' => $ground->ground_type,
                'ground_category' => $ground->ground_category,
                'description' => $ground->description,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->getImageUrl(),
                'images' => $ground->images->map(function ($image) {
                    return asset($image->image_path);
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'grounds' => $formattedGrounds,
            'count' => $formattedGrounds->count()
        ]);
    }

    /**
     * Get new grounds
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewGrounds()
    {
        $grounds = Ground::where('status', 'active')
            ->where('is_new', true)
            ->with(['images'])
            ->get();

        $formattedGrounds = $grounds->map(function ($ground) {
            return [
                'id' => $ground->id,
                'name' => $ground->name,
                'location' => $ground->location,
                'price_per_hour' => $ground->price_per_hour,
                'capacity' => $ground->capacity,
                'ground_type' => $ground->ground_type,
                'ground_category' => $ground->ground_category,
                'description' => $ground->description,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->getImageUrl(),
                'images' => $ground->images->map(function ($image) {
                    return asset($image->image_path);
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'grounds' => $formattedGrounds,
            'count' => $formattedGrounds->count()
        ]);
    }

    /**
     * Search grounds by name or location
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchGrounds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $searchTerm = $request->search;

        $grounds = Ground::where('status', 'active')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('location', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            })
            ->with(['images'])
            ->get();

        $formattedGrounds = $grounds->map(function ($ground) {
            return [
                'id' => $ground->id,
                'name' => $ground->name,
                'location' => $ground->location,
                'price_per_hour' => $ground->price_per_hour,
                'capacity' => $ground->capacity,
                'ground_type' => $ground->ground_type,
                'ground_category' => $ground->ground_category,
                'description' => $ground->description,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->getImageUrl(),
                'images' => $ground->images->map(function ($image) {
                    return asset($image->image_path);
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'grounds' => $formattedGrounds,
            'count' => $formattedGrounds->count()
        ]);
    }

    /**
     * Issue mobile API token for authentication
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function issueToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // If you're using the mobile approach with plainTextToken
        if (method_exists($user, 'createToken')) {
            $token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token issued successfully',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ]);
        }

        // Fallback response if token creation is not available
        return response()->json([
            'success' => true,
            'message' => 'Authentication successful, but token creation is not available',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone
            ]
        ]);
    }

    /**
     * Logout user and revoke token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Check if user has tokens method
        if (method_exists($request->user(), 'tokens')) {
            // Revoke all tokens
            $request->user()->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }
}
