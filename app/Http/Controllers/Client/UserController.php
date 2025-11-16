<?php

namespace App\Http\Controllers\Client;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display the users management page.
     */
    public function index()
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $users = User::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.users', compact('users'));
    }

    /**
     * Get paginated users for AJAX requests.
     */
    public function pagination(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $users = User::where('client_id', $client->id)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return view('client.partials.users-table', compact('users'))->render();
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
                'client_id' => $client->id,
                'user_type' => 'user', // Only allow creating 'user' type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('User creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user'
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $user = User::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$user) {
            abort(404, 'User not found or you do not have permission to view it.');
        }

        return view('client.user-view', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $user = User::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or you do not have permission to edit it.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $user = User::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or you do not have permission to update it.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('User update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user'
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $user = User::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or you do not have permission to delete it.'
            ], 404);
        }

        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('User deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

    /**
     * View user details.
     */
    public function view($id)
    {
        $client = Auth::user()->client;
        if (!$client) {
            return redirect()->route('user.home');
        }

        $user = User::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found or you do not have permission to view it.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }
}
