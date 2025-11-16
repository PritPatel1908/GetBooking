<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $bookings = $user->bookings()->count();
        $totalPayments = Payment::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->sum('amount');

        // Return React app view for SPA
        return view('user.react-app');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        try {
            // If only profile photo is being updated
            if ($request->hasFile('profile_photo') && !$request->filled('first_name')) {
                $validator = Validator::make($request->all(), [
                    'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]);

                if ($validator->fails()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validation failed',
                            'errors' => $validator->errors()
                        ], 422);
                    }
                    return back()->withErrors($validator)->withInput();
                }

                try {
                    // Delete old photo if exists
                    if ($user->profile_photo_path) {
                        Storage::delete($user->profile_photo_path);
                    }

                    $path = $request->file('profile_photo')->store('profile-photos', 'public');
                    $user->profile_photo_path = $path;
                    $user->save();

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Profile photo updated successfully.',
                            'user' => [
                                'name' => $user->name,
                                'email' => $user->email,
                                'profile_photo_path' => asset('storage/' . $user->profile_photo_path)
                            ]
                        ]);
                    }
                    return back()->with('success', 'Profile photo updated successfully.');
                } catch (\Exception $e) {
                    Log::error('Profile photo upload error: ' . $e->getMessage());
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to upload profile photo. Please try again.'
                        ], 500);
                    }
                    return back()->withErrors(['profile_photo' => 'Failed to upload profile photo. Please try again.']);
                }
            }

            // For profile information update
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            // Update basic info
            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->country = $request->country;
            $user->postal_code = $request->postal_code;

            // Generate username from first_name and last_name
            $user->name = trim($request->first_name . ' ' . $request->last_name);

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                try {
                    // Delete old photo if exists
                    if ($user->profile_photo_path) {
                        Storage::delete($user->profile_photo_path);
                    }

                    $path = $request->file('profile_photo')->store('profile-photos', 'public');
                    $user->profile_photo_path = $path;
                } catch (\Exception $e) {
                    Log::error('Profile photo upload error: ' . $e->getMessage());
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to upload profile photo. Please try again.'
                        ], 500);
                    }
                    return back()->withErrors(['profile_photo' => 'Failed to upload profile photo. Please try again.']);
                }
            }

            $user->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully.',
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'profile_photo_path' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null
                    ]
                ]);
            }

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile. Please try again.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update profile. Please try again.']);
        }
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            if (!Hash::check($request->current_password, $user->password)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The current password is incorrect.'
                    ], 422);
                }
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully.'
                ]);
            }

            return back()->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            Log::error('Password update error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update password. Please try again.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update password. Please try again.']);
        }
    }

    public function apiShow()
    {
        $user = Auth::user();
        $bookings = $user->bookings()->count();
        $totalPayments = Payment::where('user_id', $user->id)
            ->where('payment_status', 'completed')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'address' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'country' => $user->country,
                'postal_code' => $user->postal_code,
                'profile_photo_path' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
            ],
            'stats' => [
                'bookings' => $bookings,
                'total_payments' => $totalPayments,
            ]
        ]);
    }

    public function apiUpdate(Request $request)
    {
        return $this->update($request);
    }

    public function apiUpdatePhoto(Request $request)
    {
        $user = Auth::user();

        try {
            $validator = Validator::make($request->all(), [
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::delete($user->profile_photo_path);
            }

            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully.',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_path' => asset('storage/' . $user->profile_photo_path)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Profile photo upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile photo. Please try again.'
            ], 500);
        }
    }
}
