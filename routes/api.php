<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\GroundController;
use App\Http\Controllers\API\AndroidApiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ground filtering route
Route::get('/filter-grounds', [GroundController::class, 'filterGrounds']);

// Android App API Routes
Route::prefix('android')->group(function () {
    // Auth routes
    Route::post('/login', [AndroidApiController::class, 'login']);
    Route::post('/register', [AndroidApiController::class, 'register']);
    Route::post('/token', [AndroidApiController::class, 'issueToken']);

    // Public ground routes
    Route::get('/grounds', [AndroidApiController::class, 'getAllGrounds']);
    Route::get('/grounds/{id}', [AndroidApiController::class, 'getGroundDetails']);
    Route::get('/featured-grounds', [AndroidApiController::class, 'getFeaturedGrounds']);
    Route::get('/new-grounds', [AndroidApiController::class, 'getNewGrounds']);
    Route::post('/search-grounds', [AndroidApiController::class, 'searchGrounds']);

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // User profile management
        Route::get('/profile', function (Request $request) {
            return response()->json([
                'success' => true,
                'user' => $request->user()
            ]);
        });
        Route::post('/profile', [AndroidApiController::class, 'updateProfile']);
        Route::post('/logout', [AndroidApiController::class, 'logout']);

        // Booking management
        Route::post('/available-slots', [AndroidApiController::class, 'getAvailableSlots']);
        Route::post('/bookings', [AndroidApiController::class, 'createBooking']);
        Route::get('/bookings', [AndroidApiController::class, 'getUserBookings']);
        Route::put('/bookings/{id}/cancel', [AndroidApiController::class, 'cancelBooking']);
    });
});
