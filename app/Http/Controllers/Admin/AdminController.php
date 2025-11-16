<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Client;
use App\Models\Ground;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\GroundImage;
use App\Models\GroundFeature;
use App\Models\GroundSlot;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function admin_home()
    {
        // Check if user is not admin - only admins can access admin panel
        if (Auth::user()->user_type !== 'admin') {
            return redirect()->route('user.home');
        }

        // Return React app view for SPA
        return view('admin.react-app');
    }

    /**
     * Get dashboard statistics for admin panel
     */
    public function dashboardStats()
    {
        try {
            // Total Bookings
            $totalBookings = Booking::count();
            
            // Active Grounds
            $activeGrounds = Ground::where('status', 'active')->count();
            
            // Total Revenue (from completed payments)
            $totalRevenue = Payment::where('payment_status', 'completed')
                ->sum('amount');
            
            // Pending Payments
            $pendingPayments = Payment::where('payment_status', 'pending')->count();
            
            // Calculate changes (last month comparison)
            $lastMonth = now()->subMonth();
            
            $lastMonthBookings = Booking::where('created_at', '<', $lastMonth)->count();
            $bookingsChange = $lastMonthBookings > 0 
                ? round((($totalBookings - $lastMonthBookings) / $lastMonthBookings) * 100, 1)
                : 0;
            $bookingsChangeFormatted = $bookingsChange >= 0 ? "+{$bookingsChange}%" : "{$bookingsChange}%";
            
            $lastMonthGrounds = Ground::where('status', 'active')
                ->where('created_at', '<', $lastMonth)
                ->count();
            $groundsChange = $lastMonthGrounds > 0
                ? ($activeGrounds - $lastMonthGrounds)
                : 0;
            $groundsChangeFormatted = $groundsChange >= 0 ? "+{$groundsChange}" : "{$groundsChange}";
            
            $lastMonthRevenue = Payment::where('payment_status', 'completed')
                ->where('created_at', '<', $lastMonth)
                ->sum('amount');
            $revenueChange = $lastMonthRevenue > 0
                ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                : 0;
            $revenueChangeFormatted = $revenueChange >= 0 ? "+{$revenueChange}%" : "{$revenueChange}%";
            
            $lastMonthPendingPayments = Payment::where('payment_status', 'pending')
                ->where('created_at', '<', $lastMonth)
                ->count();
            $pendingPaymentsChange = $lastMonthPendingPayments - $pendingPayments;
            $pendingPaymentsChangeFormatted = $pendingPaymentsChange >= 0 ? "+{$pendingPaymentsChange}" : "{$pendingPaymentsChange}";
            
            // Format revenue with currency
            $revenueFormatted = '₹' . number_format($totalRevenue, 2);
            if ($totalRevenue >= 1000000) {
                $revenueFormatted = '₹' . number_format($totalRevenue / 1000000, 2) . 'M';
            } elseif ($totalRevenue >= 1000) {
                $revenueFormatted = '₹' . number_format($totalRevenue / 1000, 2) . 'K';
            }
            
            // Format numbers with Indian numbering system
            $formatNumber = function($num) {
                return number_format($num);
            };
            
            return response()->json([
                'status' => 'success',
                'stats' => [
                    'total_bookings' => $totalBookings,
                    'total_bookings_formatted' => $formatNumber($totalBookings),
                    'active_grounds' => $activeGrounds,
                    'active_grounds_formatted' => $formatNumber($activeGrounds),
                    'revenue' => $totalRevenue,
                    'revenue_formatted' => $revenueFormatted,
                    'pending_payments' => $pendingPayments,
                    'pending_payments_formatted' => $formatNumber($pendingPayments),
                    'changes' => [
                        'bookings' => $bookingsChangeFormatted,
                        'grounds' => $groundsChangeFormatted,
                        'revenue' => $revenueChangeFormatted,
                        'pending_payments' => $pendingPaymentsChangeFormatted,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load dashboard statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function admin_clients(Request $request)
    {
        $clients = Client::orderBy('created_at', 'desc')->paginate(10);

        // Enrich each client with bookings count (across their grounds) and last booking date
        foreach ($clients as $client) {
            // Get all ground IDs owned by the client
            $groundIds = Ground::where('client_id', $client->id)->pluck('id');

            if ($groundIds->isEmpty()) {
                $client->bookings_count = 0;
                $client->last_booking_date = 'No bookings';
                continue;
            }

            // Total distinct bookings that include any of the client's grounds
            $bookingsCount = Booking::whereHas('details', function ($query) use ($groundIds) {
                $query->whereIn('ground_id', $groundIds);
            })->count();

            // Most recent booking date
            $lastBookingCreated = Booking::whereHas('details', function ($query) use ($groundIds) {
                $query->whereIn('ground_id', $groundIds);
            })
                ->orderBy('created_at', 'desc')
                ->value('created_at');

            $client->bookings_count = $bookingsCount;
            $client->last_booking_date = $lastBookingCreated ? $lastBookingCreated->format('d M Y') : 'No bookings';
        }

        // Check if this is an AJAX request for table reload
        if ($request->ajax() || $request->has('ajax')) {
            // Format client data for JSON response
            $formattedClients = $clients->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'gender' => $client->gender,
                    'created_at' => $client->created_at->format('d M Y'),
                    'status' => $client->status,
                    'profile_picture' => $client->profile_picture ? asset($client->profile_picture) : null,
                    'bookings_count' => $client->bookings_count ?? 0,
                    'last_booking_date' => $client->last_booking_date ?? 'No bookings'
                ];
            });

            // Return JSON response
            return response()->json([
                'clients' => $formattedClients,
                'pagination' => [
                    'total' => $clients->total(),
                    'per_page' => $clients->perPage(),
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'from' => $clients->firstItem(),
                    'to' => $clients->lastItem()
                ]
            ]);
        }

        // Regular view response - return React app for SPA
        return view('admin.react-app');
    }

    public function client_create(Request $request, $id = null)
    {
        // Get client ID from route parameter (for PUT requests) or request body
        $clientId = $id ?? $request->client_id;

        // Different validation rules for create vs update
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required|string|in:male,female,other',
            'full_address' => 'nullable|string',
            'area' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'state' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive',
        ];

        // Add email validation only for new clients
        if (!$clientId) {
            $validationRules['email'] = 'required|email|unique:clients,email|max:255';
            $validationRules['password'] = 'required|string|min:8';
            $validationRules['password_confirmation'] = 'required|string|min:8|same:password';
        } else {
            // For updates, allow email to be unique except for current client
            $validationRules['email'] = 'nullable|email|unique:clients,email,' . $clientId . '|max:255';
        }

        // Validate the request data
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate full name
        $fullName = $request->first_name;
        if ($request->has('middle_name') && !empty($request->middle_name)) {
            $fullName .= ' ' . $request->middle_name;
        }
        $fullName .= ' ' . $request->last_name;

        // Create or update client
        $client = $clientId ? Client::find($clientId) : new Client();

        // Fill client data
        $client->first_name = $request->first_name;
        $client->last_name = $request->last_name;
        $client->name = $fullName;

        // Only set email for new clients, or update if provided in request
        if (!$clientId) {
            $client->email = $request->email;
        } elseif ($request->has('email') && !empty($request->email)) {
            $client->email = $request->email;
        }

        $client->phone = $request->phone;
        $client->gender = $request->gender;
        $client->full_address = $request->full_address;
        $client->area = $request->area;
        $client->city = $request->city;
        $client->pincode = $request->pincode;
        $client->state = $request->state;
        $client->status = $request->status;

        // Save the client
        $client->save();

        $userData = [
            'first_name' => $client->first_name,
            'middle_name' => $request->middle_name ?? null,
            'last_name' => $client->last_name,
            'name' => $client->name,
            'phone' => $client->phone,
            'address' => $client->full_address,
            'city' => $client->city,
            'state' => $client->state,
            'postal_code' => $client->pincode,
            'user_type' => 'client',
            'client_id' => $client->id,
        ];

        // Only update password if provided (for new clients or password change)
        if (!$clientId || ($request->has('password') && !empty($request->password))) {
            $userData['password'] = Hash::make($request->password ?? 'welcome@123');
        }

        User::updateOrCreate([
            'email' => $client->email,
        ], $userData);

        // Format client for response
        $formattedClient = [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'gender' => $client->gender,
            'registration_date' => $client->created_at->format('d M Y'),
            'status' => $client->status,
            'profile_picture' => $client->profile_picture ? asset($client->profile_picture) : null,
            // Add more fields as needed
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Client saved successfully!',
            'client' => $formattedClient
        ]);
    }

    public function client_delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $client = Client::find($id);

            if (!$client) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Client not found'
                ], 404);
            }

            // Check if client has grounds with active bookings
            $grounds = $client->grounds;
            foreach ($grounds as $ground) {
                $activeBookings = \App\Models\BookingDetail::where('ground_id', $ground->id)
                    ->whereHas('booking', function ($query) {
                        $query->whereIn('booking_status', ['pending', 'confirmed'])
                            ->where('booking_date', '>=', now()->format('Y-m-d'));
                    })
                    ->count();

                if ($activeBookings > 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot delete client with grounds that have active bookings. Please cancel or complete the bookings first.'
                    ], 422);
                }
            }

            // Delete all grounds and their related data first
            foreach ($grounds as $ground) {
                // Delete ground images (files and database records)
                $groundImages = \App\Models\GroundImage::where('ground_id', $ground->id)->get();
                foreach ($groundImages as $image) {
                    if (file_exists(public_path($image->image_path))) {
                        unlink(public_path($image->image_path));
                    }
                    $image->delete();
                }

                // Delete ground features
                \App\Models\GroundFeature::where('ground_id', $ground->id)->delete();

                // Delete ground slots
                \App\Models\GroundSlot::where('ground_id', $ground->id)->delete();

                // Delete booking details
                \App\Models\BookingDetail::where('ground_id', $ground->id)->delete();

                // Delete reviews
                \App\Models\Review::where('ground_id', $ground->id)->delete();

                // Delete the main ground image file if it exists
                if ($ground->ground_image && file_exists(public_path($ground->ground_image))) {
                    unlink(public_path($ground->ground_image));
                }

                // Delete the ground
                $ground->delete();
            }

            // Delete client profile picture if exists
            if ($client->profile_picture && file_exists(public_path($client->profile_picture))) {
                unlink(public_path($client->profile_picture));
            }

            // Update user type to 'user' instead of deleting
            $user = User::where('email', $client->email)->first();
            if ($user) {
                $user->user_type = 'user';
                $user->client_id = null;
                $user->save();
            }

            // Delete the client record
            $client->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Client deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting client: ' . $e->getMessage(), [
                'client_id' => $id,
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting client: ' . $e->getMessage()
            ], 500);
        }
    }

    public function client_edit(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'client' => $client
        ]);
    }

    public function client_view(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }

        // Get bookings if you have a Booking model with a relationship to Client
        // Uncomment and adjust the code below when you have implemented bookings
        /*
        $bookings = $client->bookings()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'date' => $booking->booking_date,
                    'time' => $booking->booking_time,
                    'service' => $booking->service_name,
                    'status' => $booking->status,
                    'amount' => $booking->amount
                ];
            });
        */

        // For now, return empty bookings array
        $bookings = [];

        return response()->json([
            'status' => 'success',
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'first_name' => $client->first_name,
                'last_name' => $client->last_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'gender' => $client->gender,
                'full_address' => $client->full_address,
                'area' => $client->area,
                'city' => $client->city,
                'pincode' => $client->pincode,
                'state' => $client->state,
                'country' => $client->country ?? 'India',
                'registration_date' => $client->created_at->format('d M Y'),
                'status' => $client->status,
                'profile_picture' => $client->profile_picture ? asset($client->profile_picture) : null,
            ],
            'bookings' => $bookings,
            'bookings_count' => count($bookings)
        ]);
    }

    public function client_view_page($id)
    {
        $client = Client::findOrFail($id);

        // Get grounds owned by this client
        $grounds = Ground::where('client_id', $client->id)
            ->with(['images'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get bookings for this client's grounds
        $bookings = Booking::whereHas('details', function ($query) use ($grounds) {
            $query->whereIn('ground_id', $grounds->pluck('id')->toArray());
        })
            ->with(['details.ground', 'details.slot', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $lastBooking = $bookings->first();

        // Return React app for SPA - detail pages will be handled by React Router
        return view('admin.react-app');
    }

    // Add this method to return just the pagination HTML
    public function clients_pagination(Request $request)
    {
        $page = $request->get('page', 1);
        $clients = Client::orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);

        // Render only the pagination section
        $paginationHtml = '
        <div class="flex items-center justify-between w-full">
            <div class="text-sm text-gray-600">
                Showing ' . ($clients->firstItem() ?? 0) . ' to ' . ($clients->lastItem() ?? 0) . ' of ' . ($clients->total() ?? 0) . ' entries
            </div>
            <div>
                ' . $clients->links()->toHtml() . '
            </div>
        </div>';

        return response($paginationHtml);
    }

    public function admin_grounds(Request $request)
    {
        $grounds = \App\Models\Ground::with(['slots', 'features', 'images'])->orderBy('created_at', 'desc')->paginate(10);

        // Check if this is an AJAX request for table reload
        if ($request->ajax() || $request->has('ajax')) {
            // Format ground data for JSON response
            $formattedGrounds = $grounds->map(function ($ground) {
                return [
                    'id' => $ground->id,
                    'name' => $ground->name,
                    'location' => $ground->location,
                    'capacity' => $ground->capacity,
                    'ground_type' => $ground->ground_type,
                    'ground_category' => $ground->ground_category,
                    'description' => $ground->description,
                    'rules' => $ground->rules,
                    'opening_time' => $ground->opening_time,
                    'closing_time' => $ground->closing_time,
                    'phone' => $ground->phone,
                    'email' => $ground->email,
                    'created_at' => $ground->created_at->format('d M Y'),
                    'status' => $ground->status,
                    'client_id' => $ground->client_id,
                    'is_new' => $ground->is_new,
                    'is_featured' => $ground->is_featured,
                    'ground_image' => $ground->ground_image ? asset($ground->ground_image) : null,
                    'bookings_count' => 0, // Update with actual count when you implement bookings
                    'last_booking_date' => 'No bookings', // Update with actual date when you implement bookings
                    // Include related data
                    'images' => $ground->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'image_path' => asset($image->image_path)
                        ];
                    }),
                    'features' => $ground->features->map(function ($feature) {
                        return [
                            'id' => $feature->id,
                            'feature_name' => $feature->feature_name,
                            'feature_type' => $feature->feature_type,
                            'feature_status' => $feature->feature_status
                        ];
                    }),
                    'slots' => $ground->slots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'slot_name' => $slot->slot_name,
                            'day_of_week' => $slot->day_of_week,
                            'slot_type' => $slot->slot_type,
                            'slot_status' => $slot->slot_status
                        ];
                    })
                ];
            });

            // Return JSON response
            return response()->json([
                'grounds' => $formattedGrounds,
                'pagination' => [
                    'total' => $grounds->total(),
                    'per_page' => $grounds->perPage(),
                    'current_page' => $grounds->currentPage(),
                    'last_page' => $grounds->lastPage(),
                    'from' => $grounds->firstItem(),
                    'to' => $grounds->lastItem()
                ]
            ]);
        }

        // Regular view response - return React app for SPA
        return view('admin.react-app');
    }

    public function ground_create(Request $request, $id = null)
    {
        // Log request data for debugging
        \Illuminate\Support\Facades\Log::info('Ground create/update request received', [
            'has_id' => $request->has('ground_id'),
            'has_files' => $request->hasFile('ground_images'),
            'file_count' => $request->hasFile('ground_images') ? count($request->file('ground_images')) : 0,
        ]);

        // Validate the request data
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'ground_type' => 'nullable|string|max:255',
            'ground_category' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'opening_time' => 'nullable|string',
            'closing_time' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'status' => 'required|string|in:active,inactive',
            'client_id' => 'required|exists:clients,id',
            'is_new' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'ground_images' => 'nullable|array',
            'ground_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'nullable|exists:ground_images,id',
            'feature_name.*' => 'nullable|string|max:255',
            'feature_type.*' => 'nullable|string|in:facility,equipment,service',
            'feature_status.*' => 'nullable|string|in:active,inactive',
            'slot_name.*' => 'nullable|string|max:255',
            'start_time.*' => 'nullable|date_format:H:i',
            'end_time.*' => 'nullable|date_format:H:i',
            'slot_type.*' => 'nullable|string|in:morning,afternoon,evening,night',
            'day_of_week.*' => 'nullable|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'slot_status.*' => 'nullable|string|in:active,inactive',
            'price_per_slot.*' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::error('Ground validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get ground ID from route parameter (for PUT requests) or request body
        $groundId = $id ?? $request->ground_id;
        
        // Create or update ground
        $ground = $groundId ? \App\Models\Ground::find($groundId) : new \App\Models\Ground();

        // Fill ground data
        $ground->name = $request->name;
        $ground->location = $request->location;
        $ground->capacity = $request->capacity;
        $ground->ground_type = $request->ground_type;
        $ground->ground_category = $request->ground_category;
        $ground->description = $request->description;
        $ground->rules = $request->rules;
        $ground->opening_time = $request->opening_time;
        $ground->closing_time = $request->closing_time;
        $ground->phone = $request->phone;
        $ground->email = $request->email;
        $ground->status = $request->status;
        $ground->client_id = $request->client_id;
        $ground->is_new = $request->is_new ? true : false;
        $ground->is_featured = $request->is_featured ? true : false;

        // Save the ground
        $ground->save();

        // Handle additional ground images
        if ($request->hasFile('ground_images')) {
            try {
                // Get all uploaded files - convert to an array if it's not already
                $images = $request->file('ground_images');
                $images = is_array($images) ? $images : [$images];

                \Illuminate\Support\Facades\Log::info("Processing " . count($images) . " images for ground ID {$ground->id}", [
                    'ground_id' => $ground->id,
                    'image_count' => count($images)
                ]);

                // Process each image
                foreach ($images as $index => $image) {
                    // Skip invalid files
                    if (!$image->isValid()) {
                        \Illuminate\Support\Facades\Log::warning("Invalid image at index {$index}");
                        continue;
                    }

                    // Create unique filename to avoid collisions
                    $extension = $image->getClientOriginalExtension();
                    $filename = 'ground_' . $ground->id . '_' . uniqid() . '_' . time() . '.' . $extension;

                    // Make sure upload directory exists
                    $uploadPath = public_path('uploads/grounds');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0777, true);
                    }

                    // Move the file
                    $image->move($uploadPath, $filename);
                    $imagePath = 'uploads/grounds/' . $filename;

                    // Create database record
                    $groundImage = new \App\Models\GroundImage();
                    $groundImage->ground_id = $ground->id;
                    $groundImage->image_path = $imagePath;
                    $groundImage->save();

                    \Illuminate\Support\Facades\Log::info("Saved image {$index}: {$filename}", [
                        'image_id' => $groundImage->id,
                        'ground_id' => $ground->id,
                        'file_size' => filesize($uploadPath . '/' . $filename)
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error uploading images: ' . $e->getMessage(), [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'stack_trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            \Illuminate\Support\Facades\Log::info('No images found in request for ground ID ' . $ground->id, [
                'has_file' => $request->hasFile('ground_images'),
                'all_files' => $request->allFiles()
            ]);
        }

        // Handle image deletion if any
        if ($request->has('delete_images') && is_array($request->delete_images)) {
            foreach ($request->delete_images as $imageId) {
                $image = \App\Models\GroundImage::find($imageId);
                if ($image && $image->ground_id == $ground->id) {
                    // Delete the image file if it exists
                    if (file_exists(public_path($image->image_path))) {
                        unlink(public_path($image->image_path));
                    }
                    // Delete the record
                    $image->delete();
                }
            }
        }

        // Handle ground features
        if ($request->has('feature_name') && is_array($request->feature_name)) {
            // Clear existing features if updating
            if ($groundId) {
                \App\Models\GroundFeature::where('ground_id', $ground->id)->delete();
            }

            foreach ($request->feature_name as $key => $featureName) {
                if (!empty($featureName)) {
                    \App\Models\GroundFeature::create([
                        'ground_id' => $ground->id,
                        'feature_name' => $featureName,
                        'feature_type' => $request->feature_type[$key] ?? 'facility',
                        'feature_status' => $request->feature_status[$key] ?? 'active'
                    ]);
                }
            }
        }

        // Handle ground slots
        if ($request->has('slot_name') && is_array($request->slot_name)) {
            // Clear existing slots if updating
            if ($groundId) {
                \App\Models\GroundSlot::where('ground_id', $ground->id)->delete();
            }

            foreach ($request->slot_name as $key => $slotName) {
                // Check if we have slot_name OR (start_time AND end_time)
                if (!empty($slotName) || (!empty($request->start_time[$key]) && !empty($request->end_time[$key]))) {
                    // Generate slot name if not provided but start_time and end_time are
                    if (empty($slotName) && !empty($request->start_time[$key]) && !empty($request->end_time[$key])) {
                        $slotName = date('H:i', strtotime($request->start_time[$key])) . ' - ' . date('H:i', strtotime($request->end_time[$key]));
                    } else if (!empty($request->start_time[$key]) && !empty($request->end_time[$key])) {
                        // Still update the name if both start_time and end_time are provided
                        $slotName = date('H:i', strtotime($request->start_time[$key])) . ' - ' . date('H:i', strtotime($request->end_time[$key]));
                    }

                    \App\Models\GroundSlot::create([
                        'ground_id' => $ground->id,
                        'slot_name' => $slotName,
                        'day_of_week' => $request->day_of_week[$key] ?? null,
                        'start_time' => !empty($request->start_time[$key]) ? $request->start_time[$key] : null,
                        'end_time' => !empty($request->end_time[$key]) ? $request->end_time[$key] : null,
                        'slot_type' => $request->slot_type[$key] ?? 'morning',
                        'slot_status' => $request->slot_status[$key] ?? 'active',
                        'price_per_slot' => $request->price_per_slot[$key] ?? 50.00 // Default price if not provided
                    ]);
                }
            }
        }

        // Reload the ground with relationships for response
        $ground = \App\Models\Ground::with(['slots', 'features', 'images'])->find($ground->id);

        // Format ground for response
        $formattedGround = [
            'id' => $ground->id,
            'name' => $ground->name,
            'location' => $ground->location,
            'capacity' => $ground->capacity,
            'ground_type' => $ground->ground_type,
            'ground_category' => $ground->ground_category,
            'description' => $ground->description,
            'rules' => $ground->rules,
            'opening_time' => $ground->opening_time,
            'closing_time' => $ground->closing_time,
            'phone' => $ground->phone,
            'email' => $ground->email,
            'status' => $ground->status,
            'client_id' => $ground->client_id,
            'ground_image' => $ground->ground_image ? asset($ground->ground_image) : null,
            'created_at' => $ground->created_at->format('d M Y'),
            // Include related data
            'images' => $ground->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => asset($image->image_path)
                ];
            }),
            'features' => $ground->features->map(function ($feature) {
                return [
                    'id' => $feature->id,
                    'feature_name' => $feature->feature_name,
                    'feature_type' => $feature->feature_type
                ];
            }),
            'slots' => $ground->slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'day_of_week' => $slot->day_of_week,
                    'start_time' => $slot->start_time ? date('H:i', strtotime($slot->start_time)) : null,
                    'end_time' => $slot->end_time ? date('H:i', strtotime($slot->end_time)) : null,
                    'slot_type' => $slot->slot_type,
                    'slot_status' => $slot->slot_status,
                    'price_per_slot' => $slot->price_per_slot
                ];
            }),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Ground saved successfully!',
            'ground' => $formattedGround
        ]);
    }

    public function ground_delete(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $ground = \App\Models\Ground::find($id);

            if (!$ground) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ground not found'
                ], 404);
            }

            // Check if ground has active bookings
            $activeBookings = \App\Models\BookingDetail::where('ground_id', $ground->id)
                ->whereHas('booking', function ($query) {
                    $query->whereIn('booking_status', ['pending', 'confirmed'])
                        ->where('booking_date', '>=', now()->format('Y-m-d'));
                })
                ->count();

            if ($activeBookings > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete ground with active bookings. Please cancel or complete the bookings first.'
                ], 422);
            }

            // Delete related records first
            // 1. Delete ground images (files and database records)
            $groundImages = \App\Models\GroundImage::where('ground_id', $ground->id)->get();
            foreach ($groundImages as $image) {
                if (file_exists(public_path($image->image_path))) {
                    unlink(public_path($image->image_path));
                }
                $image->delete();
            }

            // 2. Delete ground features
            \App\Models\GroundFeature::where('ground_id', $ground->id)->delete();

            // 3. Delete ground slots
            \App\Models\GroundSlot::where('ground_id', $ground->id)->delete();

            // 4. Delete booking details (this will cascade to related bookings if needed)
            \App\Models\BookingDetail::where('ground_id', $ground->id)->delete();

            // 5. Delete reviews
            \App\Models\Review::where('ground_id', $ground->id)->delete();

            // 6. Delete the main ground image file if it exists
            if ($ground->ground_image && file_exists(public_path($ground->ground_image))) {
                unlink(public_path($ground->ground_image));
            }

            // 7. Finally delete the ground record
            $ground->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Ground deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting ground: ' . $e->getMessage(), [
                'ground_id' => $id,
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting ground: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ground_edit(Request $request, $id)
    {
        $ground = \App\Models\Ground::with(['slots', 'features', 'images'])->find($id);

        if (!$ground) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ground not found'
            ], 404);
        }

        // Format ground data including relationships
        $formattedGround = [
            'id' => $ground->id,
            'name' => $ground->name,
            'location' => $ground->location,
            'capacity' => $ground->capacity,
            'ground_type' => $ground->ground_type,
            'ground_category' => $ground->ground_category,
            'description' => $ground->description,
            'rules' => $ground->rules,
            'opening_time' => $ground->opening_time,
            'closing_time' => $ground->closing_time,
            'phone' => $ground->phone,
            'email' => $ground->email,
            'status' => $ground->status,
            'client_id' => $ground->client_id,
            'is_new' => $ground->is_new,
            'is_featured' => $ground->is_featured,
            'ground_image' => $ground->ground_image ? asset($ground->ground_image) : null,
            'slots' => $ground->slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'day_of_week' => $slot->day_of_week,
                    'start_time' => $slot->start_time ? date('H:i', strtotime($slot->start_time)) : null,
                    'end_time' => $slot->end_time ? date('H:i', strtotime($slot->end_time)) : null,
                    'slot_type' => $slot->slot_type,
                    'slot_status' => $slot->slot_status,
                    'price_per_slot' => $slot->price_per_slot
                ];
            }),
            'features' => $ground->features->map(function ($feature) {
                return [
                    'id' => $feature->id,
                    'feature_name' => $feature->feature_name,
                    'feature_type' => $feature->feature_type,
                    'feature_status' => $feature->feature_status
                ];
            }),
            'images' => $ground->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => asset($image->image_path)
                ];
            })
        ];

        return response()->json([
            'status' => 'success',
            'ground' => $formattedGround
        ]);
    }

    public function ground_view(Request $request, $id)
    {
        $ground = \App\Models\Ground::with(['slots', 'images'])->find($id);

        if (!$ground) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ground not found'
            ], 404);
        }

        // Get bookings if you have a Booking model with a relationship to Ground
        // Uncomment and adjust the code below when you have implemented bookings
        /*
        $bookings = $ground->bookings()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'date' => $booking->booking_date,
                    'time' => $booking->booking_time,
                    'client_name' => $booking->client->name,
                    'status' => $booking->status,
                    'amount' => $booking->amount
                ];
            });
        */

        // For now, return empty bookings array
        $bookings = [];

        // Format images for response
        $images = $ground->images->map(function ($image) {
            return [
                'id' => $image->id,
                'image_path' => asset($image->image_path)
            ];
        });

        // Format slots for response
        $slots = $ground->slots->map(function ($slot) {
            return [
                'id' => $slot->id,
                'slot_name' => $slot->slot_name,
                'day_of_week' => $slot->day_of_week,
                'start_time' => $slot->start_time ? date('H:i', strtotime($slot->start_time)) : null,
                'end_time' => $slot->end_time ? date('H:i', strtotime($slot->end_time)) : null,
                'slot_type' => $slot->slot_type,
                'slot_status' => $slot->slot_status,
                'price_per_slot' => $slot->price_per_slot
            ];
        });

        return response()->json([
            'status' => 'success',
            'ground' => [
                'id' => $ground->id,
                'name' => $ground->name,
                'location' => $ground->location,
                'price_per_hour' => $ground->price_per_hour,
                'capacity' => $ground->capacity,
                'ground_type' => $ground->ground_type,
                'ground_category' => $ground->ground_category,
                'description' => $ground->description,
                'rules' => $ground->rules,
                'opening_time' => $ground->opening_time,
                'closing_time' => $ground->closing_time,
                'status' => $ground->status,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->ground_image ? asset($ground->ground_image) : null,
                'created_at' => $ground->created_at->format('d M Y'),
                'images' => $images,
                'slots' => $slots
            ],
            'bookings' => $bookings,
            'bookings_count' => count($bookings)
        ]);
    }

    public function ground_view_page($id)
    {
        $ground = \App\Models\Ground::with(['images'])->findOrFail($id);

        // Get bookings if you have implemented the relationship
        // Uncomment when you have the Booking model
        /*
        $bookings = $ground->bookings()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $bookingsCount = $ground->bookings()->count();
        */

        // For now, use empty data
        $bookings = [];
        $bookingsCount = 0;

        // Get ground photos
        $photos = $ground->images;

        // Return React app for SPA - detail pages will be handled by React Router
        return view('admin.react-app');
    }

    public function grounds_pagination(Request $request)
    {
        $grounds = \App\Models\Ground::with(['slots', 'features', 'images'])->orderBy('created_at', 'desc')->paginate(10);

        // Format grounds for AJAX response
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
                'rules' => $ground->rules,
                'opening_time' => $ground->opening_time,
                'closing_time' => $ground->closing_time,
                'phone' => $ground->phone,
                'email' => $ground->email,
                'created_at' => $ground->created_at->format('d M Y'),
                'status' => $ground->status,
                'client_id' => $ground->client_id,
                'is_new' => $ground->is_new,
                'is_featured' => $ground->is_featured,
                'ground_image' => $ground->ground_image ? asset($ground->ground_image) : null,
                'bookings_count' => 0, // Update with actual count when you implement bookings
                'last_booking_date' => 'No bookings', // Update with actual date when you implement bookings
                // Include related data
                'images' => $ground->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => asset($image->image_path)
                    ];
                }),
                'features' => $ground->features->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'feature_name' => $feature->feature_name,
                        'feature_type' => $feature->feature_type,
                        'feature_status' => $feature->feature_status
                    ];
                }),
                'slots' => $ground->slots->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'slot_name' => $slot->slot_name,
                        'day_of_week' => $slot->day_of_week,
                        'start_time' => $slot->start_time ? date('H:i', strtotime($slot->start_time)) : null,
                        'end_time' => $slot->end_time ? date('H:i', strtotime($slot->end_time)) : null,
                        'slot_type' => $slot->slot_type,
                        'slot_status' => $slot->slot_status
                    ];
                })
            ];
        });

        return response()->json([
            'grounds' => $formattedGrounds,
            'pagination' => [
                'total' => $grounds->total(),
                'per_page' => $grounds->perPage(),
                'current_page' => $grounds->currentPage(),
                'last_page' => $grounds->lastPage(),
                'from' => $grounds->firstItem(),
                'to' => $grounds->lastItem()
            ]
        ]);
    }

    // API endpoint to get clients for dropdown
    public function get_clients()
    {
        $clients = Client::where('status', 'active')
            ->orderBy('name', 'asc')
            ->select('id', 'name')
            ->get();

        return response()->json($clients);
    }

    // API endpoint to get users for booking dropdown
    public function get_users()
    {
        $users = User::where('user_type', '!=', 'admin')
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'email', 'phone')
            ->get();

        return response()->json($users);
    }

    // API endpoint to get grounds for booking dropdown
    public function get_grounds()
    {
        $grounds = Ground::where('status', 'active')
            ->orderBy('name', 'asc')
            ->select('id', 'name', 'location')
            ->get();

        return response()->json($grounds);
    }

    // Ground image delete endpoint
    public function ground_image_delete(Request $request, $id)
    {
        $image = \App\Models\GroundImage::find($id);

        if (!$image) {
            return response()->json([
                'status' => 'error',
                'message' => 'Image not found'
            ], 404);
        }

        // Delete the image file if it exists
        if (file_exists(public_path($image->image_path))) {
            unlink(public_path($image->image_path));
        }

        // Delete the database record
        $image->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Image deleted successfully!'
        ]);
    }

    /**
     * Show a dedicated page for uploading ground images
     */
    public function ground_image_upload_page($id)
    {
        // Return React app for SPA - image upload will be handled in React
        return view('admin.react-app');
    }

    /**
     * Upload new images for a ground
     */
    public function ground_image_upload(Request $request)
    {
        // Log the request for debugging
        Log::info('Image upload request received', [
            'ground_id' => $request->ground_id,
            'has_files' => $request->hasFile('images'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
        ]);

        $validator = Validator::make($request->all(), [
            'ground_id' => 'required|exists:grounds,id',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('Image upload validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $groundId = $request->ground_id;
        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                try {
                    // Create a unique filename
                    $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

                    // Make sure the directory exists
                    $directory = public_path('uploads/grounds');
                    if (!file_exists($directory)) {
                        mkdir($directory, 0777, true);
                    }

                    // Move the file to the public uploads directory
                    $image->move($directory, $filename);

                    // Create a new image record in the database
                    $groundImage = new \App\Models\GroundImage();
                    $groundImage->ground_id = $groundId;
                    $groundImage->image_path = 'uploads/grounds/' . $filename;
                    $groundImage->save();

                    $uploadedImages[] = [
                        'id' => $groundImage->id,
                        'image_path' => asset($groundImage->image_path),
                    ];

                    Log::info('Image uploaded successfully', [
                        'filename' => $filename,
                        'image_id' => $groundImage->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error uploading image', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        // Store success message in session for toast
        session()->flash('toast_success', 'Images uploaded successfully!');

        return response()->json([
            'status' => 'success',
            'message' => 'Images uploaded successfully!',
            'images' => $uploadedImages,
            'photos' => $uploadedImages, // Adding an alias to match the variable name used in the blade template
        ]);
    }

    /**
     * Display the bookings list page
     */
    public function admin_bookings(Request $request)
    {
        $bookings = \App\Models\Booking::with(['user', 'details.ground', 'details.slot', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Check if this is an AJAX request for table reload
        if ($request->ajax() || $request->has('ajax')) {
            // Format booking data for JSON response
            $formattedBookings = $bookings->map(function ($booking) {
                $firstDetail = $booking->details->first();
                $ground = $firstDetail ? $firstDetail->ground : null;
                $slots = $booking->details->map(function ($detail) {
                    return $detail->slot ? $detail->slot->slot_name : 'N/A';
                })->implode(', ');

                return [
                    'id' => $booking->id,
                    'booking_sku' => $booking->booking_sku,
                    'user_id' => $booking->user_id,
                    'user_name' => $booking->user ? $booking->user->name : 'N/A',
                    'user_email' => $booking->user ? $booking->user->email : 'N/A',
                    'ground_id' => $ground ? $ground->id : null,
                    'ground_name' => $ground ? $ground->name : 'N/A',
                    'booking_date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                    'booking_date_formatted' => $booking->booking_date ? $booking->booking_date->format('d M Y') : 'N/A',
                    'booking_time' => $booking->details->first() ? ($booking->details->first()->time_slot ?? $booking->details->first()->booking_time ?? 'N/A') : 'N/A',
                    'slots' => $slots,
                    'amount' => $booking->amount,
                    'amount_formatted' => '₹' . number_format($booking->amount, 2),
                    'booking_status' => $booking->booking_status,
                    'payment_status' => $booking->payment_status,
                    'notes' => $booking->notes,
                    'created_at' => $booking->created_at->format('d M Y'),
                    'created_at_full' => $booking->created_at->format('d M Y, h:i A'),
                    'payment' => $booking->payment ? [
                        'id' => $booking->payment->id,
                        'transaction_id' => $booking->payment->transaction_id,
                        'payment_method' => $booking->payment->payment_method,
                    ] : null,
                ];
            });

            // Return JSON response
            return response()->json([
                'bookings' => $formattedBookings,
                'pagination' => [
                    'total' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem()
                ]
            ]);
        }

        // Get users and grounds for the modal
        // Return React app for SPA
        return view('admin.react-app');
    }

    /**
     * Display the booking view page
     */
    public function booking_view_page($id)
    {
        $booking = \App\Models\Booking::with(['user', 'ground', 'payment'])
            ->findOrFail($id);
        $users = User::where('user_type', '!=', 'admin')->get();
        $grounds = Ground::all();

        // Return React app for SPA - detail pages will be handled by React Router
        return view('admin.react-app');
    }

    /**
     * Create a new booking
     */
    public function booking_create(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ground_id' => 'required|exists:grounds,id',
            'slot_ids' => 'required|array',
            'slot_ids.*' => 'exists:ground_slots,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required',
            'duration' => 'required|numeric|min:1|max:24',
            'amount' => 'required|numeric|min:0',
            'booking_status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|in:pending,initiated,processing,completed,failed,cancelled,refunded',
            'notes' => 'nullable|string',
        ]);

        // Generate a unique booking SKU
        $bookingSku = 'BK-' . strtoupper(substr(md5(uniqid()), 0, 8));

        try {
            DB::beginTransaction();

            // Create the booking
            $booking = new Booking();
            $booking->user_id = $request->user_id;
            $booking->booking_sku = $bookingSku;
            $booking->booking_date = $request->booking_date;
            $booking->booking_time = $request->booking_time;
            $booking->duration = $request->duration;
            $booking->amount = $request->amount;
            $booking->booking_status = $request->booking_status;
            $booking->payment_status = $request->payment_status;
            $booking->notes = $request->notes;
            // No longer using slot_id directly in the booking as we're supporting multiple slots
            $booking->save();

            // Create booking details for each selected slot
            foreach ($request->slot_ids as $slotId) {
                $bookingDetail = new BookingDetail();
                $bookingDetail->booking_id = $booking->id;
                $bookingDetail->ground_id = $request->ground_id;
                $bookingDetail->slot_id = $slotId;
                $bookingDetail->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully',
                'booking' => $booking,
                'redirect' => route('admin.bookings')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing booking
     */
    public function booking_update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'ground_id' => 'required|exists:grounds,id',
            'slot_ids' => 'required|array',
            'slot_ids.*' => 'exists:ground_slots,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required',
            'duration' => 'required|numeric|min:1',
            'amount' => 'required|numeric|min:0',
            'booking_status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|in:pending,initiated,processing,completed,failed,cancelled,refunded',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $booking = \App\Models\Booking::findOrFail($id);

            // Update booking
            $booking->update([
                'user_id' => $request->user_id,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'duration' => $request->duration,
                'amount' => $request->amount,
                'booking_status' => $request->booking_status,
                'payment_status' => $request->payment_status,
                'notes' => $request->notes
            ]);

            // Delete existing booking details
            $booking->details()->delete();

            // Create new booking details for each selected slot
            foreach ($request->slot_ids as $slotId) {
                $bookingDetail = new BookingDetail();
                $bookingDetail->booking_id = $booking->id;
                $bookingDetail->ground_id = $request->ground_id;
                $bookingDetail->slot_id = $slotId;
                $bookingDetail->save();
            }

            // Update payment amount and status
            if ($booking->payment) {
                $booking->payment->update([
                    'amount' => $request->amount,
                    'payment_status' => $request->payment_status
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking updated successfully!',
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a booking
     */
    public function booking_delete($id)
    {
        try {
            DB::beginTransaction();

            $booking = \App\Models\Booking::findOrFail($id);

            // Delete related records
            $booking->details()->delete();
            $booking->payment()->delete();
            $booking->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Booking deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking data for editing
     */
    public function booking_edit($id)
    {
        $booking = \App\Models\Booking::with(['user', 'payment', 'details.ground', 'details.slot'])
            ->findOrFail($id);

        // Get the ground ID from the first booking detail
        $groundId = $booking->details->first() ? $booking->details->first()->ground_id : null;

        // Get all selected slot IDs
        $selectedSlotIds = $booking->details->pluck('slot_id')->toArray();

        return response()->json([
            'status' => 'success',
            'booking' => $booking,
            'ground_id' => $groundId,
            'slot_ids' => $selectedSlotIds
        ]);
    }

    /**
     * Handle booking pagination
     */
    public function bookings_pagination(Request $request)
    {
        $page = $request->get('page', 1);
        $bookings = \App\Models\Booking::with(['user', 'ground', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'page', $page);

        // Render only the pagination section
        $paginationHtml = '
        <div class="flex items-center justify-between w-full">
            <div class="text-sm text-gray-600">
                Showing ' . ($bookings->firstItem() ?? 0) . ' to ' . ($bookings->lastItem() ?? 0) . ' of ' . ($bookings->total() ?? 0) . ' entries
            </div>
            <div>
                ' . $bookings->links()->toHtml() . '
            </div>
        </div>';

        return response($paginationHtml);
    }

    /**
     * Get available slots for a ground
     */
    public function getAvailableSlots(Request $request, $id)
    {
        try {
            // Find the ground with all its slots
            $ground = Ground::with(['slots'])->findOrFail($id);

            // Get booking date from request or use today's date as default
            $bookingDate = $request->input('date', now()->format('Y-m-d'));

            // Get current booking ID if we're editing
            $currentBookingId = $request->input('booking_id');

            // Get all active slots for this ground
            $allSlots = $ground->slots->where('slot_status', 'active');

            // Find booked slots for this date (that aren't part of the current booking)
            $bookedSlotIds = BookingDetail::whereHas('booking', function ($query) use ($bookingDate, $currentBookingId) {
                $query->where('booking_date', $bookingDate)
                    ->whereIn('booking_status', ['pending', 'confirmed']);

                // Exclude the current booking if we're editing
                if ($currentBookingId) {
                    $query->where('bookings.id', '!=', $currentBookingId);
                }
            })
                ->where('ground_id', $id)
                ->pluck('slot_id')
                ->toArray();

            // Get available slots (all slots minus booked slots)
            $availableSlots = $allSlots->filter(function ($slot) use ($bookedSlotIds) {
                return !in_array($slot->id, $bookedSlotIds);
            })->values();

            Log::info('Available slots for ground', [
                'ground_id' => $id,
                'date' => $bookingDate,
                'total_slots' => $allSlots->count(),
                'booked_slots' => count($bookedSlotIds),
                'available_slots' => $availableSlots->count(),
                'booked_slot_ids' => $bookedSlotIds
            ]);

            return response()->json([
                'status' => 'success',
                'ground' => [
                    'id' => $ground->id,
                    'name' => $ground->name
                ],
                'slots' => $availableSlots
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting slots: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error retrieving slots: ' . $e->getMessage(),
                'slots' => []
            ], 500);
        }
    }

    /**
     * Get ground details including price
     */
    public function getGroundDetails($id)
    {
        $ground = Ground::findOrFail($id);
        return response()->json($ground);
    }

    /**
     * Display a listing of payments in the admin panel.
     */
    public function admin_payments(Request $request)
    {
        $payments = Payment::with(['booking.details.ground', 'booking.details.slot', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Check if this is an AJAX request for table reload
        if ($request->ajax() || $request->has('ajax')) {
            // Format payment data for JSON response
            $formattedPayments = $payments->map(function ($payment) {
                $firstDetail = $payment->booking ? $payment->booking->details->first() : null;
                $ground = $firstDetail ? $firstDetail->ground : null;
                
                return [
                    'id' => $payment->id,
                    'transaction_id' => $payment->transaction_id,
                    'date' => $payment->date ? $payment->date->format('Y-m-d') : null,
                    'date_formatted' => $payment->date ? $payment->date->format('d M Y') : 'N/A',
                    'amount' => $payment->amount,
                    'amount_formatted' => '₹' . number_format($payment->amount, 2),
                    'payment_status' => $payment->payment_status,
                    'payment_method' => $payment->payment_method,
                    'payment_type' => $payment->payment_type,
                    'user_id' => $payment->user_id,
                    'user_name' => $payment->user->name ?? 'N/A',
                    'user_email' => $payment->user->email ?? 'N/A',
                    'booking_id' => $payment->booking_id,
                    'booking_sku' => $payment->booking->booking_sku ?? 'N/A',
                    'ground_id' => $ground ? $ground->id : null,
                    'ground_name' => $ground ? $ground->name : 'N/A',
                    'created_at' => $payment->created_at->format('d M Y'),
                    'created_at_full' => $payment->created_at->format('d M Y, h:i A'),
                ];
            });

            // Return JSON response
            return response()->json([
                'payments' => $formattedPayments,
                'pagination' => [
                    'total' => $payments->total(),
                    'per_page' => $payments->perPage(),
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem()
                ]
            ]);
        }

        // Regular view response - return React app for SPA
        return view('admin.react-app');
    }

    /**
     * Display the payment details page.
     */
    public function payment_view_page($id)
    {
        $payment = Payment::with(['booking.ground', 'user'])
            ->findOrFail($id);

        // Return React app for SPA - detail pages will be handled by React Router
        return view('admin.react-app');
    }

    /**
     * Get payment details for AJAX request.
     */
    public function payment_view(Request $request, $id)
    {
        $payment = Payment::with(['booking.details.ground', 'booking.details.slot', 'user'])
            ->findOrFail($id);

        // Format payment data
        $formattedPayment = [
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'date' => $payment->date ? $payment->date->format('Y-m-d') : null,
            'date_formatted' => $payment->date ? $payment->date->format('d M Y') : 'N/A',
            'amount' => $payment->amount,
            'amount_formatted' => '₹' . number_format($payment->amount, 2),
            'payment_status' => $payment->payment_status,
            'payment_method' => $payment->payment_method,
            'payment_type' => $payment->payment_type,
            'payment_url' => $payment->payment_url,
            'payment_response' => $payment->payment_response,
            'payment_response_code' => $payment->payment_response_code,
            'payment_response_message' => $payment->payment_response_message,
            'payment_response_data' => $payment->payment_response_data,
            'payment_response_data_json' => $payment->payment_response_data_json ? json_decode($payment->payment_response_data_json, true) : null,
            'user' => $payment->user ? [
                'id' => $payment->user->id,
                'name' => $payment->user->name,
                'email' => $payment->user->email,
                'phone' => $payment->user->phone,
            ] : null,
            'booking' => $payment->booking ? [
                'id' => $payment->booking->id,
                'booking_sku' => $payment->booking->booking_sku,
                'booking_date' => $payment->booking->booking_date ? $payment->booking->booking_date->format('Y-m-d') : null,
                'booking_date_formatted' => $payment->booking->booking_date ? $payment->booking->booking_date->format('d M Y') : 'N/A',
                'amount' => $payment->booking->amount,
                'booking_status' => $payment->booking->booking_status,
                'payment_status' => $payment->booking->payment_status,
                'details' => $payment->booking->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'ground' => $detail->ground ? [
                            'id' => $detail->ground->id,
                            'name' => $detail->ground->name,
                            'location' => $detail->ground->location,
                        ] : null,
                        'slot' => $detail->slot ? [
                            'id' => $detail->slot->id,
                            'slot_name' => $detail->slot->slot_name,
                        ] : null,
                        'time_slot' => $detail->time_slot,
                        'booking_time' => $detail->booking_time,
                        'duration' => $detail->duration,
                        'price' => $detail->price,
                    ];
                }),
            ] : null,
            'created_at' => $payment->created_at->format('d M Y, h:i A'),
            'updated_at' => $payment->updated_at->format('d M Y, h:i A'),
        ];

        return response()->json([
            'status' => 'success',
            'payment' => $formattedPayment
        ]);
    }

    /**
     * Update payment status.
     */
    public function payment_update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:pending,initiated,processing,completed,failed,cancelled,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::findOrFail($id);
        $payment->payment_status = $request->payment_status;
        $payment->save();

        // Update booking payment status if applicable
        if ($payment->booking) {
            $payment->booking->payment_status = $request->payment_status;
            $payment->booking->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment status updated successfully',
            'payment' => $payment
        ]);
    }

    /**
     * Handle pagination for payments.
     */
    public function payments_pagination(Request $request)
    {
        $payments = Payment::with(['booking.ground', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.partials.payments-table', compact('payments'))->render();
    }
}
