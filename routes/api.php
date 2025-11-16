<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\GroundController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\BookingController as APIBookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes - no authentication required
Route::get('/grounds/featured', [GroundController::class, 'apiFeaturedGrounds']);
Route::get('/grounds', [GroundController::class, 'apiAllGrounds']);
// More specific routes must come before less specific ones
Route::get('/grounds/{id}/slots/{date}', [UserController::class, 'getGroundSlots']);
Route::get('/grounds/{groundId}/reviews', [ReviewController::class, 'getGroundReviews']);
Route::get('/grounds/{id}', [GroundController::class, 'apiGroundDetails']);
Route::get('/statistics', [GroundController::class, 'apiStatistics']);
Route::get('/cities', [GroundController::class, 'apiCities']);

// Review routes - public (fetching reviews doesn't require auth)
Route::get('/reviews/{id}', [ReviewController::class, 'show']);

// Authentication routes for mobile apps (Flutter)
Route::post('/login', [LoginController::class, 'apiLogin']);
Route::post('/register', [LoginController::class, 'apiRegister']);
Route::post('/logout', [LoginController::class, 'apiLogout'])->middleware('auth:sanctum');

// Protected routes - support both session and token authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        \Log::info('========== API /user ENDPOINT CALLED ==========');
        \Log::info('API /user - Request URL: ' . $request->fullUrl());
        \Log::info('API /user - Session ID: ' . session()->getId());
        \Log::info('API /user - Auth Check: ' . (auth()->check() ? 'AUTHENTICATED' : 'NOT AUTHENTICATED'));
        \Log::info('API /user - Auth ID: ' . (auth()->check() ? auth()->id() : 'NULL'));
        \Log::info('API /user - Auth Guard: ' . auth()->getDefaultDriver());

        try {
            $authCheck = auth()->check();
            \Log::info('API /user - auth()->check() result: ' . ($authCheck ? 'TRUE' : 'FALSE'));

            if (!$authCheck) {
                \Log::warning('API /user - User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $user = auth()->user();
            \Log::info('API /user - User retrieved:', [
                'user_id' => $user?->id,
                'email' => $user?->email,
                'user_type' => $user?->user_type,
            ]);

            if (!$user) {
                \Log::error('API /user - auth()->user() returned NULL');
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Build profile photo URL if exists
            $profilePhotoUrl = null;
            if (!empty($user->profile_photo_path)) {
                $profilePhotoUrl = url('storage/' . $user->profile_photo_path);
            }

            $responseData = [
                'id' => (int) $user->id,
                'name' => (string) ($user->name ?? ''),
                'email' => (string) ($user->email ?? ''),
                'user_type' => (string) ($user->user_type ?? 'user'),
                'profile_photo_path' => $profilePhotoUrl,
                'phone' => $user->phone ? (string) $user->phone : null,
                'address' => $user->address ? (string) $user->address : null,
                'city' => $user->city ? (string) $user->city : null,
                'state' => $user->state ? (string) $user->state : null,
                'country' => $user->country ? (string) $user->country : null,
                'postal_code' => $user->postal_code ? (string) $user->postal_code : null,
                'client_id' => $user->client_id ? (int) $user->client_id : null,
            ];

            \Log::info('API /user - Response data prepared:', $responseData);

            // Return only basic user data to avoid serialization issues
            return response()->json($responseData);
        } catch (\Throwable $e) {
            \Log::error('========== API /user ENDPOINT ERROR ==========');
            \Log::error('API /user endpoint error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'session_id' => session()->getId(),
                'auth_check' => auth()->check(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching user data.',
                'error' => config('app.debug', false) ? $e->getMessage() : null,
            ], 500);
        }
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'apiShow']);
    Route::post('/profile', [ProfileController::class, 'apiUpdate']);
    Route::post('/profile/photo', [ProfileController::class, 'apiUpdatePhoto']);

    // Bookings routes - New comprehensive API for mobile app
    Route::get('/bookings', [APIBookingController::class, 'index']);
    Route::get('/bookings/{id}', [APIBookingController::class, 'show']);
    Route::get('/bookings/sku/{sku}', [APIBookingController::class, 'showBySku']);
    Route::get('/bookings/{id}/debug', [APIBookingController::class, 'debug']);

    // Legacy booking routes (kept for backward compatibility)
    Route::get('/bookings-legacy', [UserController::class, 'apiMyBookings']);
    Route::post('/bookings', [UserController::class, 'bookGround']);
    Route::post('/bookings/offline', [BookingController::class, 'storeOffline']);
    Route::get('/bookings-legacy/{id}', [UserController::class, 'apiBookingDetails']);
    Route::get('/bookings-legacy/sku/{bookingSku}', [UserController::class, 'apiBookingDetailsBySku']);

    // Review routes - protected (create/update/delete require authentication)
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});
