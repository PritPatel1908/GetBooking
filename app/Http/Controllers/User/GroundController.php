<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ground;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class GroundController extends Controller
{
    /**
     * Display all grounds with optional filtering
     */
    public function allGrounds(Request $request)
    {
        // Get filtering parameters
        $category = $request->query('category', 'allgrounds');
        $search = $request->query('search');
        $price = $request->query('price');

        $query = Ground::where('status', 'active');

        // Apply category filter if not 'allgrounds'
        if ($category !== 'allgrounds') {
            $query->where('ground_category', $category);
        }

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        // Apply price filter if provided
        if ($price) {
            $priceRange = explode('-', $price);
            // Note: Price filtering now needs to be done through ground_slots table
            // This would require a more complex query with joins
            // For now, we'll skip price filtering or implement it differently
        }

        $grounds = $query->with('images')->orderBy('created_at', 'desc')->get();

        // Return React app view for SPA
        return view('user.react-app');
    }

    /**
     * Display a specific ground's details
     */
    public function viewGround($id)
    {
        $ground = Ground::with(['images', 'slots', 'features'])->findOrFail($id);

        // Return React app view for SPA
        return view('user.react-app');
    }

    public function apiAllGrounds(Request $request)
    {
        $query = Ground::where('status', 'active')->with(['images', 'features', 'slots']);

        $category = $request->query('category');
        if ($category && $category !== 'all') {
            $query->where('ground_category', $category);
        }

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        // Filter by city
        $city = $request->query('city');
        if ($city && $city !== '' && $city !== 'all') {
            $query->where('location', 'LIKE', "%{$city}%");
        }

        $grounds = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->with('reviews')
            ->get()
            ->map(function ($ground) {
                $displayPrice = null;
                if ($ground->slots && $ground->slots->count() > 0) {
                    $displayPrice = $ground->slots->first()->price_per_slot;
                }

                // Calculate reviews count and average rating
                $reviewsCount = $ground->reviews ? $ground->reviews->count() : 0;
                $averageRating = $reviewsCount > 0
                    ? round($ground->reviews->avg('rating'), 1)
                    : 0;

                return [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'description' => $ground->description,
                    'capacity' => $ground->capacity,
                    'ground_type' => $ground->ground_type,
                    'ground_category' => $ground->ground_category,
                    'images' => $ground->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image_url' => $img->image_url,
                        ];
                    }),
                    'features' => $ground->features->pluck('feature_name'),
                    'display_price' => $displayPrice,
                    'is_featured' => $ground->is_featured,
                    'reviews_count' => $reviewsCount,
                    'average_rating' => $averageRating,
                ];
            });

        return response()->json([
            'success' => true,
            'grounds' => $grounds,
        ]);
    }

    public function apiFeaturedGrounds(Request $request)
    {
        // Filter by city if provided
        $city = $request->query('city');

        $query = Ground::where('status', 'active');

        if ($city && $city !== '' && $city !== 'all') {
            $query->where('location', 'LIKE', "%{$city}%");
        }

        // First get the total count of all active grounds (with city filter if applied)
        $totalCount = $query->count();

        // Get only 3 active grounds for home page (prioritize featured ones first)
        $grounds = $query->with(['images', 'features', 'slots', 'reviews'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($ground) {
                // Calculate reviews count and average rating
                $reviewsCount = $ground->reviews ? $ground->reviews->count() : 0;
                $averageRating = $reviewsCount > 0
                    ? round($ground->reviews->avg('rating'), 1)
                    : 0;

                return [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'description' => $ground->description,
                    'capacity' => $ground->capacity,
                    'ground_type' => $ground->ground_type,
                    'ground_category' => $ground->ground_category,
                    'reviews_count' => $reviewsCount,
                    'average_rating' => $averageRating,
                    'images' => $ground->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image_url' => $img->image_url,
                        ];
                    }),
                    'slots' => $ground->slots->map(function ($slot) {
                        // Format time values properly
                        $startTime = null;
                        $endTime = null;

                        if ($slot->start_time) {
                            // If it's already a Carbon instance or datetime
                            if (is_object($slot->start_time)) {
                                $startTime = $slot->start_time->format('H:i');
                            } elseif (is_string($slot->start_time)) {
                                // Parse string and format
                                $startTime = date('H:i', strtotime($slot->start_time));
                            } else {
                                $startTime = $slot->start_time;
                            }
                        }

                        if ($slot->end_time) {
                            // If it's already a Carbon instance or datetime
                            if (is_object($slot->end_time)) {
                                $endTime = $slot->end_time->format('H:i');
                            } elseif (is_string($slot->end_time)) {
                                // Parse string and format
                                $endTime = date('H:i', strtotime($slot->end_time));
                            } else {
                                $endTime = $slot->end_time;
                            }
                        }

                        return [
                            'id' => $slot->id,
                            'slot_name' => $slot->slot_name,
                            'price_per_slot' => $slot->price_per_slot,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                        ];
                    }),
                    'features' => $ground->features->pluck('feature_name'),
                    'is_featured' => $ground->is_featured,
                    'reviews_count' => $reviewsCount,
                    'average_rating' => $averageRating,
                ];
            });

        return response()->json([
            'success' => true,
            'grounds' => $grounds,
            'total_count' => $totalCount,
            'has_more' => $totalCount > 3,
        ]);
    }

    public function apiGroundDetails($id)
    {
        try {
            $ground = Ground::with(['images', 'features', 'slots'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'ground' => [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'description' => $ground->description,
                    'capacity' => $ground->capacity,
                    'ground_type' => $ground->ground_type,
                    'ground_category' => $ground->ground_category,
                    'rules' => $ground->rules,
                    'opening_time' => $ground->opening_time,
                    'closing_time' => $ground->closing_time,
                    'phone' => $ground->phone,
                    'email' => $ground->email,
                    'images' => $ground->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image_url' => $img->image_url,
                        ];
                    }),
                    'features' => $ground->features->map(function ($feature) {
                        return [
                            'id' => $feature->id,
                            'feature_name' => $feature->feature_name,
                        ];
                    }),
                    'slots' => $ground->slots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'slot_name' => $slot->slot_name,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'price_per_slot' => $slot->price_per_slot,
                            'slot_type' => $slot->slot_type,
                            'slot_status' => $slot->slot_status,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ground not found',
            ], 404);
        }
    }

    public function apiStatistics()
    {
        $totalGrounds = Ground::where('status', 'active')->count();
        $totalBookings = Booking::count();
        $completedBookings = Booking::where('booking_status', 'completed')->count();
        $totalUsers = User::where('user_type', 'user')->count();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_grounds' => $totalGrounds,
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'total_users' => $totalUsers,
            ],
        ]);
    }

    /**
     * Get all unique cities from grounds
     */
    public function apiCities()
    {
        $locations = Ground::where('status', 'active')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->pluck('location')
            ->toArray();

        // Extract cities from locations
        // Assuming location format: "Address, City, State" or similar
        $cities = [];
        foreach ($locations as $location) {
            // Try to extract city - usually the second last part before state
            $parts = array_map('trim', explode(',', $location));
            if (count($parts) >= 2) {
                // Usually city is second last part
                $city = $parts[count($parts) - 2];
            } elseif (count($parts) == 1) {
                // If only one part, use it as city
                $city = $parts[0];
            } else {
                // Last part could also be city
                $city = end($parts);
            }

            // Clean city name
            $city = trim($city);
            if (!empty($city) && !in_array($city, $cities)) {
                $cities[] = $city;
            }
        }

        // Sort cities alphabetically
        sort($cities);

        return response()->json([
            'success' => true,
            'cities' => $cities,
        ]);
    }
}
