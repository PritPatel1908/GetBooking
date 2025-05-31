<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login-signup');
    }

    /**
     * Attempt to log in the user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // get the user by email
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // login with remember me set to true to keep the user logged in
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            // Authentication passed...
            $request->session()->regenerate();

            // Set a longer session lifetime
            config(['session.lifetime' => 10080]); // 7 days in minutes

            if ($request->expectsJson()) {
                if ($user->user_type == 'admin') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Login successful.',
                        'redirect' => route('admin.dashboard'),
                    ]);
                } else if ($user->user_type == 'client') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Login successful.',
                        'redirect' => route('user.home'),
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Login successful.',
                        'redirect' => route('user.home'),
                    ]);
                }
            }

            // Fallback for non-AJAX requests
            if ($user->user_type == 'admin') {
                return redirect()->route('admin.dashboard');
            } else {
                // Add success message to session
                return redirect()->route('user.home')->with('success', 'Login successful.');
            }
        }

        // Authentication failed
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string',
            'password_confirmation' => 'required|string|same:password',
        ]);

        // Check if the user already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'User already exists.',
            ], 409);
        }

        // Create a new user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'redirect' => route('login'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Add JavaScript to clear any success message from sessionStorage
        return redirect()->route('welcome')->with('clearSuccessMessage', true);
    }
}
