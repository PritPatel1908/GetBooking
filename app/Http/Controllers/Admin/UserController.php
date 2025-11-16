<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display the users management page.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);

        // Enrich each user with bookings count
        foreach ($users as $user) {
            $user->bookings_count = $user->bookings()->count();
            $lastBooking = $user->bookings()->orderBy('created_at', 'desc')->first();
            $user->last_booking_date = $lastBooking ? $lastBooking->created_at->format('d M Y') : 'No bookings';
        }

        // Check if this is an AJAX request for table reload
        if ($request->ajax() || $request->has('ajax')) {
            // Format user data for JSON response
            $formattedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'user_type' => $user->user_type,
                    'role' => $user->role,
                    'address' => $user->address,
                    'city' => $user->city,
                    'state' => $user->state,
                    'country' => $user->country,
                    'postal_code' => $user->postal_code,
                    'created_at' => $user->created_at->format('d M Y'),
                    'bookings_count' => $user->bookings_count ?? 0,
                    'last_booking_date' => $user->last_booking_date ?? 'No bookings',
                    'client_id' => $user->client_id,
                ];
            });

            // Return JSON response
            return response()->json([
                'users' => $formattedUsers,
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ]
            ]);
        }

        // Return React app for SPA
        return view('admin.react-app');
    }

    /**
     * Get paginated users for AJAX requests.
     */
    public function pagination(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $users = User::where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Enrich each user with bookings count
        foreach ($users as $user) {
            $user->bookings_count = $user->bookings()->count();
            $lastBooking = $user->bookings()->orderBy('created_at', 'desc')->first();
            $user->last_booking_date = $lastBooking ? $lastBooking->created_at->format('d M Y') : 'No bookings';
        }

        // Format user data for JSON response
        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'role' => $user->role,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'postal_code' => $user->postal_code,
                'created_at' => $user->created_at->format('d M Y'),
                'bookings_count' => $user->bookings_count ?? 0,
                'last_booking_date' => $user->last_booking_date ?? 'No bookings',
                'client_id' => $user->client_id,
            ];
        });

        return response()->json([
            'users' => $formattedUsers,
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem()
            ]
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'user_type' => 'required|string|in:user,admin,client',
            'client_id' => 'nullable|integer|exists:clients,id',
            'role' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for user creation:', $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = new User();
            $user->name = trim(($request->first_name ?? '') . ' ' . ($request->middle_name ?? '') . ' ' . ($request->last_name ?? ''));
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->country = $request->country;
            $user->postal_code = $request->postal_code;
            $user->user_type = $request->user_type;
            $user->client_id = $request->client_id;
            $user->role = $request->role;
            $user->save();

            Log::info('User created successfully:', ['user_id' => $user->id, 'name' => $user->name]);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating user:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user data for editing.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        // If first_name, middle_name, last_name are not set, try to split from name
        if (!$user->first_name && $user->name) {
            $nameParts = explode(' ', $user->name);
            $user->first_name = $nameParts[0] ?? '';
            $user->last_name = $nameParts[count($nameParts) - 1] ?? '';
            $user->middle_name = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';
        }

        return response()->json([
            'status' => 'success',
            'user' => $user
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Check if user exists
        if (!$user) {
            Log::error('User not found for update:', ['user_id' => $id]);
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Debug: Log what we're receiving
        Log::info('Update User Request Data:', $request->all());
        Log::info('Request Method:', ['method' => $request->method()]);
        Log::info('Content Type:', ['content_type' => $request->header('Content-Type')]);

        // Check if we have the required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'user_type'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!$request->has($field) || $request->input($field) === null || $request->input($field) === '') {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            Log::error('Missing required fields for user update:', ['missing_fields' => $missingFields]);
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'user_type' => 'required|string|in:user,admin,client',
            'password' => 'nullable|string|min:8',
            'client_id' => 'nullable|integer|exists:clients,id',
            'role' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for user update:', $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update user fields one by one to identify the issue
            $user->name = trim(($request->first_name ?? '') . ' ' . ($request->middle_name ?? '') . ' ' . ($request->last_name ?? ''));
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->country = $request->country;
            $user->postal_code = $request->postal_code;
            $user->user_type = $request->user_type;
            $user->client_id = $request->client_id;
            $user->role = $request->role;

            // Update password only if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user:', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Server error. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get user details for viewing.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $bookings = $user->bookings()->with(['slot', 'payment', 'ground'])->latest()->get();

        // Return React app for SPA - detail pages will be handled by React Router
        return view('admin.react-app');
    }

    /**
     * Get user details for AJAX requests.
     */
    public function view($id)
    {
        $user = User::findOrFail($id);
        $bookings = $user->bookings()->with(['slot', 'payment'])->latest()->take(5)->get();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'bookings' => $bookings
        ]);
    }

    /**
     * Delete the specified user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Check if user has bookings
        if ($user->bookings()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete user with existing bookings'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully'
        ]);
    }
}
