<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    /**
     * Show the OTP verification form
     */
    public function showVerificationForm()
    {
        if (!session()->has('auth.otp_user_id')) {
            return redirect()->route('login');
        }

        $userId = session('auth.otp_user_id');
        $user = User::findOrFail($userId);

        return view('auth.verify-otp', [
            'mobile_last_digits' => substr($user->mobile_number, -4),
        ]);
    }

    /**
     * Verify the OTP
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if (!session()->has('auth.otp_user_id')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please login again.',
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $userId = session('auth.otp_user_id');
        $user = User::findOrFail($userId);

        // Find the latest valid OTP for this user
        $otp = Otp::where('user_id', $userId)
            ->where('otp', $request->otp)
            ->where('verified_at', null)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please try again.',
                ], 400);
            }

            return back()->with('error', 'Invalid or expired OTP. Please try again.');
        }

        // Mark OTP as verified
        $otp->markAsVerified();

        // Log the user in
        Auth::login($user);

        // Clear the temporary session data
        session()->forget('auth.otp_user_id');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully. Logging you in.',
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Login successful!');
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request)
    {
        if (!session()->has('auth.otp_user_id')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please login again.',
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $userId = session('auth.otp_user_id');
        $user = User::findOrFail($userId);

        // Generate new OTP
        $loginController = new LoginController();
        $otp = $loginController->generateOtp($user);

        // Send OTP via SMS
        $loginController->sendOtp($user, $otp->otp);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP has been resent to your mobile number.',
                'mobile_last_digits' => substr($user->mobile_number, -4),
            ]);
        }

        return back()->with('success', 'OTP has been resent to your mobile number.');
    }
}
