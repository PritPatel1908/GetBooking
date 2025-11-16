<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ReflectionClass;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset request form
     */
    public function showResetRequestForm()
    {
        // Return React app view for SPA
        return view('user.react-app');
    }

    /**
     * Send password reset link to user's email
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
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
            // TEMPORARY: For testing, return token directly without sending email
            // TODO: Remove this and use Password::sendResetLink() when email is configured
            
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'We could not find a user with that email address.'
                    ], 422);
                }
                return back()->withErrors(['email' => 'We could not find a user with that email address.']);
            }

            // Get the Password repository to access its hashKey
            $repository = Password::broker()->getRepository();
            
            // Use reflection to get the private hashKey property
            $reflection = new ReflectionClass($repository);
            $hashKeyProperty = $reflection->getProperty('hashKey');
            $hashKeyProperty->setAccessible(true);
            $hashKey = $hashKeyProperty->getValue($repository);
            
            // Generate token - Laravel uses Str::random(64)
            $token = Str::random(64);
            
            // Hash token using the same method and key that Laravel uses
            // Laravel uses hash_hmac('sha256', $token, $hashKey)
            $hashedToken = hash_hmac('sha256', $token, $hashKey);
            
            // Store in database (same table Laravel uses)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $hashedToken,
                    'created_at' => now()
                ]
            );
            
            // Create reset URL with plain token
            // Password::reset() will hash it and compare with stored hash
            $resetUrl = url("/password/reset/{$token}?email=" . urlencode($user->email));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset link generated successfully (for testing - email not sent).',
                    'reset_token' => $token,
                    'reset_url' => $resetUrl,
                    'email' => $user->email,
                    'note' => 'This is for testing. In production, the link will be sent via email.'
                ]);
            }
            
            // For non-AJAX requests, also return with token info
            return back()->with([
                'status' => 'Password reset link generated. Check response for token.',
                'reset_token' => $token,
                'reset_url' => $resetUrl
            ]);

            /* PRODUCTION CODE - Uncomment when email is configured:
            // Use Laravel's built-in password reset
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Password reset link has been sent to your email address.'
                    ]);
                }
                return back()->with('status', __($status));
            }
            */

            // If email doesn't exist or other error
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send reset link. Please try again later.'
                ], 422);
            }

            return back()->withErrors(['email' => __($status)]);
        } catch (\Exception $e) {
            Log::error('Password reset link error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred. Please try again later.'
                ], 500);
            }

            return back()->withErrors(['email' => 'An error occurred. Please try again later.']);
        }
    }

    /**
     * Show the password reset form (with token)
     */
    public function showResetForm(Request $request, $token = null)
    {
        // If token is provided in URL, redirect to React with token in query
        if ($token) {
            // Token will be handled by React Router via query parameter
            return view('user.react-app');
        }
        
        // If email is provided in query, include it
        // Return React app view for SPA
        return view('user.react-app');
    }

    /**
     * Reset the user's password
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
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
            // Use Laravel's built-in password reset
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    // User model has 'password' => 'hashed' cast, so assign plain password
                    // Laravel will automatically hash it via the cast
                    $user->password = $password;
                    $user->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Your password has been reset successfully. You can now login with your new password.'
                    ]);
                }
                return redirect()->route('login')->with('status', __($status));
            }

            // Handle errors
            if ($request->ajax() || $request->wantsJson()) {
                $errors = [];
                if ($status === Password::INVALID_TOKEN) {
                    $errors['token'] = 'The reset token is invalid or has expired.';
                } elseif ($status === Password::INVALID_USER) {
                    $errors['email'] = 'We could not find a user with that email address.';
                } else {
                    $errors['general'] = 'Unable to reset password. Please try again.';
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Password reset failed',
                    'errors' => $errors
                ], 422);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while resetting your password. Please try again.'
                ], 500);
            }

            return back()->withErrors(['general' => 'An error occurred. Please try again.']);
        }
    }
}

