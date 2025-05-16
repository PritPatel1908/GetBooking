<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function admin_home()
    {
        return view('admin.home');
    }

    public function admin_clients(Request $request)
    {
        $clients = Client::orderBy('created_at', 'desc')->paginate(10);

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
                    'bookings_count' => 0, // Update with actual count when you implement bookings
                    'last_booking_date' => 'No bookings' // Update with actual date when you implement bookings
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

        // Regular view response
        return view('admin.clients', compact('clients'));
    }

    public function client_create(Request $request)
    {
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
        if (!$request->client_id) {
            $validationRules['email'] = 'required|email|unique:clients,email|max:255';
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
        $client = $request->client_id ? Client::find($request->client_id) : new Client();

        // Fill client data
        $client->first_name = $request->first_name;
        $client->last_name = $request->last_name;
        $client->name = $fullName;

        // Only set email for new clients
        if (!$request->client_id) {
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

        User::updateOrCreate([
            'email' => $client->email,
        ], [
            'first_name' => $client->first_name,
            'middle_name' => $request->middle_name ?? null,
            'last_name' => $client->last_name,
            'name' => $client->name,
            'phone' => $client->phone,
            'address' => $client->full_address,
            'city' => $client->city,
            'state' => $client->state,
            'postal_code' => $client->pincode,
            'password' => Hash::make('welcome@123'),
            'user_type' => 'client',
            'client_id' => $client->id,
        ]);

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
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }

        $client->delete();
        $user = User::where('email', $client->email)->first();
        $user->user_type = 'user';
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully!'
        ]);
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

        // Get bookings if you have a Booking model with a relationship to Client
        // Uncomment and adjust when you have a Booking model
        /*
        $bookings = $client->bookings()
            ->orderBy('created_at', 'desc')
            ->get();

        $lastBooking = $bookings->first();
        */

        // For demonstration purposes
        $bookings = [];
        $lastBooking = null;

        return view('admin.client-view', compact('client', 'bookings', 'lastBooking'));
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

        // Regular view response
        return view('admin.grounds', compact('grounds'));
    }

    public function ground_create(Request $request)
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
            'price_per_hour' => 'required|numeric|min:0',
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
            'slot_name.*' => 'nullable|string|max:255',
            'slot_type.*' => 'nullable|string|in:morning,afternoon,evening,night',
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

        // Create or update ground
        $ground = $request->ground_id ? \App\Models\Ground::find($request->ground_id) : new \App\Models\Ground();

        // Fill ground data
        $ground->name = $request->name;
        $ground->location = $request->location;
        $ground->price_per_hour = $request->price_per_hour;
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
            if ($request->ground_id) {
                \App\Models\GroundFeature::where('ground_id', $ground->id)->delete();
            }

            foreach ($request->feature_name as $key => $featureName) {
                if (!empty($featureName)) {
                    \App\Models\GroundFeature::create([
                        'ground_id' => $ground->id,
                        'feature_name' => $featureName,
                        'feature_type' => $request->feature_type[$key] ?? 'facility',
                        'feature_status' => 'active'
                    ]);
                }
            }
        }

        // Handle ground slots
        if ($request->has('slot_name') && is_array($request->slot_name)) {
            // Clear existing slots if updating
            if ($request->ground_id) {
                \App\Models\GroundSlot::where('ground_id', $ground->id)->delete();
            }

            foreach ($request->slot_name as $key => $slotName) {
                if (!empty($slotName)) {
                    \App\Models\GroundSlot::create([
                        'ground_id' => $ground->id,
                        'slot_name' => $slotName,
                        'slot_type' => $request->slot_type[$key] ?? 'morning',
                        'slot_status' => 'active'
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
                    'slot_type' => $slot->slot_type,
                    'slot_status' => $slot->slot_status
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
        $ground = \App\Models\Ground::find($id);

        if (!$ground) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ground not found'
            ], 404);
        }

        // Delete the ground image if it exists
        if ($ground->ground_image && file_exists(public_path($ground->ground_image))) {
            unlink(public_path($ground->ground_image));
        }

        $ground->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Ground deleted successfully!'
        ]);
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
            'status' => $ground->status,
            'client_id' => $ground->client_id,
            'is_new' => $ground->is_new,
            'is_featured' => $ground->is_featured,
            'ground_image' => $ground->ground_image ? asset($ground->ground_image) : null,
            'slots' => $ground->slots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'slot_name' => $slot->slot_name,
                    'slot_type' => $slot->slot_type,
                    'slot_status' => $slot->slot_status
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
                'slot_type' => $slot->slot_type,
                'slot_status' => $slot->slot_status
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

        return view('admin.ground-view', compact('ground', 'bookings', 'bookingsCount', 'photos'));
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
        $ground = \App\Models\Ground::findOrFail($id);
        return view('admin.ground-image-upload', compact('ground'));
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
}
