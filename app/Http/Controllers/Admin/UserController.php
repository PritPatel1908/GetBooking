<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display the users management page.
     */
    public function index()
    {
        return view('admin.users');
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

        return view('admin.partials.users-table', compact('users'))->render();
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'user_type' => 'required|string|in:user,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'user_type' => $request->user_type,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    /**
     * Get user data for editing.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'user_type' => 'required|string|in:user,admin',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'user_type' => $request->user_type,
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Get user details for viewing.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $bookings = $user->bookings()->with(['slot', 'payment', 'ground'])->latest()->get();

        return view('admin.user-view', compact('user', 'bookings'));
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
