<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ReviewReplyController;
use App\Http\Controllers\User\GroundController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Login routes - accessible to all, React will handle redirects
// NOTE: guest middleware removed - controller handles password verification and authentication check
Route::get('/login', [UserController::class, 'home'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

// Registration routes - accessible to all, React will handle redirects
Route::get('/register', [UserController::class, 'home'])->name('register');
Route::post('/register', [LoginController::class, 'register'])->name('register')->middleware('guest');

// Password Reset routes - accessible to all
Route::get('/password/reset', [PasswordResetController::class, 'showResetForm'])->name('password.request');
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Authenticated routes
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Home route - accessible to all, React will handle auth
Route::get('/home', [UserController::class, 'home'])->name('user.home');

Route::middleware('auth')->group(function () {
    // Logout route - available for all authenticated users
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // User routes - only for regular users and clients
    Route::middleware('user.type:user,client')->group(function () {
        Route::get('/all_grounds', [UserController::class, 'all_grounds'])->name('user.all_grounds');
        Route::get('/view_ground/{id}', [UserController::class, 'view_ground'])->name('user.view_ground');
        Route::get('/my_bookings', [UserController::class, 'my_bookings'])->name('user.my_bookings');
        Route::get('/pending-payments', [PaymentController::class, 'pendingPayments'])->name('user.pending-payments');
    });

    // Ground booking related routes - only for regular users and clients
    Route::middleware('user.type:user,client')->group(function () {
        Route::get('/get-ground-slots/{date}/{groundId}', [UserController::class, 'getGroundSlots']);
        // Note: /book-ground route moved to BookingController::store (line 262) for Razorpay integration
        // Route::post('/book-ground', [UserController::class, 'bookGround'])->name('user.ground.book');
        Route::get('/ground-details/{id}', [UserController::class, 'getGroundDetails'])->name('user.ground.details');

        // Payment related routes
        Route::post('/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');
        Route::get('/payment-callback', [PaymentController::class, 'paymentCallback'])->name('payment.callback');
    });

    // Admin routes - only for admin users
    Route::middleware('user.type:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'admin_home'])->name('admin.dashboard');
        Route::get('/api/admin/dashboard/stats', [AdminController::class, 'dashboardStats'])->name('admin.dashboard.stats');
        Route::get('/admin/clients', [AdminController::class, 'admin_clients'])->name('admin.clients');
        Route::get('/admin/clients/pagination', [AdminController::class, 'clients_pagination'])->name('admin.clients.pagination');
        Route::get('/admin/clients/{id}', [AdminController::class, 'client_view_page'])->name('admin.clients.show');
    });

    // Client routes - only for client users
    Route::middleware('user.type:client')->group(function () {
        Route::get('/client/dashboard', [App\Http\Controllers\Client\ClientController::class, 'dashboard'])->name('client.dashboard');
        Route::get('/client/grounds', [App\Http\Controllers\Client\ClientController::class, 'grounds'])->name('client.grounds');
        Route::get('/client/grounds/pagination', [App\Http\Controllers\Client\ClientController::class, 'grounds_pagination'])->name('client.grounds.pagination');
        Route::get('/client/grounds/{id}', [App\Http\Controllers\Client\ClientController::class, 'ground_view_page'])->name('client.grounds.show');
        Route::get('/client/bookings', [App\Http\Controllers\Client\ClientController::class, 'bookings'])->name('client.bookings');
        Route::get('/client/bookings/pagination', [App\Http\Controllers\Client\ClientController::class, 'bookings_pagination'])->name('client.bookings.pagination');
        Route::get('/client/bookings/{id}', [App\Http\Controllers\Client\ClientController::class, 'booking_view_page'])->name('client.bookings.show');
        Route::get('/client/payments', [App\Http\Controllers\Client\ClientController::class, 'payments'])->name('client.payments');
        Route::get('/client/payments/pagination', [App\Http\Controllers\Client\ClientController::class, 'payments_pagination'])->name('client.payments.pagination');
        Route::get('/client/payments/{id}', [App\Http\Controllers\Client\ClientController::class, 'payment_view_page'])->name('client.payments.show');
        Route::get('/client/users', [App\Http\Controllers\Client\UserController::class, 'index'])->name('client.users');
        Route::get('/client/users/pagination', [App\Http\Controllers\Client\UserController::class, 'pagination'])->name('client.users.pagination');
        Route::get('/client/users/{id}', [App\Http\Controllers\Client\UserController::class, 'show'])->name('client.users.show');

        // Client API Routes for CRUD operations
        Route::post('/client/grounds/create', [App\Http\Controllers\Client\ClientController::class, 'ground_create'])->name('client.grounds.store');
        Route::delete('/client/grounds/{id}', [App\Http\Controllers\Client\ClientController::class, 'ground_delete'])->name('client.grounds.delete');
        Route::get('/client/grounds/{id}/edit', [App\Http\Controllers\Client\ClientController::class, 'ground_edit'])->name('client.grounds.edit');
        Route::get('/client/grounds/{id}/view', [App\Http\Controllers\Client\ClientController::class, 'ground_view'])->name('client.grounds.view');
        Route::delete('/client/ground-images/{id}', [App\Http\Controllers\Client\ClientController::class, 'ground_image_delete'])->name('client.ground_images.delete');
        Route::post('/client/ground-images/upload', [App\Http\Controllers\Client\ClientController::class, 'ground_image_upload'])->name('client.ground_images.upload');
        Route::get('/client/grounds/{id}/upload-images', [App\Http\Controllers\Client\ClientController::class, 'ground_image_upload_page'])->name('client.grounds.upload_images');

        Route::post('/client/users', [App\Http\Controllers\Client\UserController::class, 'store'])->name('client.users.store');
        Route::get('/client/users/{id}/edit', [App\Http\Controllers\Client\UserController::class, 'edit'])->name('client.users.edit');
        Route::put('/client/users/{id}', [App\Http\Controllers\Client\UserController::class, 'update'])->name('client.users.update');
        Route::delete('/client/users/{id}', [App\Http\Controllers\Client\UserController::class, 'destroy'])->name('client.users.delete');
        Route::get('/client/users/{id}/view', [App\Http\Controllers\Client\UserController::class, 'view'])->name('client.users.view');
    });

    // Admin User Management Routes
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::get('/admin/users/pagination', [AdminUserController::class, 'pagination'])->name('admin.users.pagination');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.delete');
    Route::get('/admin/users/{id}/view', [AdminUserController::class, 'view'])->name('admin.users.view');
    Route::get('/admin/users/{id}', [AdminUserController::class, 'show'])->name('admin.users.show');

    // Admin Ground routes
    Route::get('/admin/grounds', [AdminController::class, 'admin_grounds'])->name('admin.grounds');
    Route::get('/admin/grounds/pagination', [AdminController::class, 'grounds_pagination'])->name('admin.grounds.pagination');
    Route::get('/admin/grounds/create', [AdminController::class, 'admin_grounds'])->name('admin.grounds.create');
    Route::get('/admin/grounds/{id}', [AdminController::class, 'ground_view_page'])->where('id', '[0-9]+')->name('admin.grounds.show');

    // Admin API Routes
    Route::post('/admin/clients/create', [AdminController::class, 'client_create'])->name('admin.clients.store');
    Route::put('/admin/clients/{id}', [AdminController::class, 'client_create'])->name('admin.clients.update');
    Route::delete('/admin/clients/{id}', [AdminController::class, 'client_delete'])->name('admin.clients.delete');
    Route::get('/admin/clients/{id}/edit', [AdminController::class, 'client_edit'])->name('admin.clients.edit');
    Route::get('/admin/clients/{id}/view', [AdminController::class, 'client_view'])->name('admin.clients.view');

    // Admin Ground API Routes
    Route::post('/admin/grounds/create', [AdminController::class, 'ground_create'])->name('admin.grounds.store');
    Route::put('/admin/grounds/{id}', [AdminController::class, 'ground_create'])->where('id', '[0-9]+')->name('admin.grounds.update');
    Route::delete('/admin/grounds/{id}', [AdminController::class, 'ground_delete'])->where('id', '[0-9]+')->name('admin.grounds.delete');
    Route::get('/admin/grounds/{id}/edit', [AdminController::class, 'ground_edit'])->where('id', '[0-9]+')->name('admin.grounds.edit');
    Route::get('/admin/grounds/{id}/view', [AdminController::class, 'ground_view'])->where('id', '[0-9]+')->name('admin.grounds.view');
    Route::delete('/admin/ground-images/{id}', [AdminController::class, 'ground_image_delete'])->name('admin.ground_images.delete');
    Route::post('/admin/ground-images/upload', [AdminController::class, 'ground_image_upload'])->name('admin.ground_images.upload');
    Route::get('/admin/grounds/{id}/upload-images', [AdminController::class, 'ground_image_upload_page'])->where('id', '[0-9]+')->name('admin.grounds.upload_images');

    // Admin Client API endpoint for dropdown
    Route::get('/admin/api/clients', [AdminController::class, 'get_clients'])->name('api.clients');
    Route::get('/admin/api/users', [AdminController::class, 'get_users'])->name('api.users');
    Route::get('/admin/api/grounds', [AdminController::class, 'get_grounds'])->name('api.grounds');

    // Additional user routes - only for regular users and clients
    Route::middleware('user.type:user,client')->group(function () {
        // Ground routes (React SPA routes)
        Route::get('/grounds', [GroundController::class, 'allGrounds'])->name('user.all_grounds');
        Route::get('/grounds/{id}', [GroundController::class, 'viewGround'])->name('user.view_ground');

        // Profile route (React SPA)
        Route::get('/profile', [App\Http\Controllers\User\ProfileController::class, 'show'])->name('user.profile');
        
        // API routes that are used by React but also accessible for form submissions
        Route::post('/profile', [App\Http\Controllers\User\ProfileController::class, 'update'])->name('user.profile.update');
        Route::post('/profile/password', [App\Http\Controllers\User\ProfileController::class, 'updatePassword'])->name('user.password.update');

        // User booking routes
        Route::get('/my-bookings/{bookingSku}', [UserController::class, 'view_booking'])->name('user.view_booking');
        Route::post('/user/bookings/{id}/cancel', [BookingController::class, 'cancelBooking'])->name('user.cancel_booking');
        Route::get('/user/bookings/{bookingSku}/invoice', [UserController::class, 'download_invoice'])->name('user.download_invoice');

        // Transaction routes
        Route::get('/user/transaction/{id}', [PaymentController::class, 'viewTransaction'])->name('user.transaction.view');
    });

    // Admin routes - only for admin users
    Route::middleware('user.type:admin')->group(function () {
        // Admin Booking Routes
        Route::get('/admin/bookings', [AdminController::class, 'admin_bookings'])->name('admin.bookings');
        Route::post('/admin/bookings', [AdminController::class, 'booking_create'])->name('admin.bookings.store');
        Route::put('/admin/bookings/{id}', [AdminController::class, 'booking_update'])->name('admin.bookings.update');
        Route::delete('/admin/bookings/{id}', [AdminController::class, 'booking_delete'])->name('admin.bookings.delete');
        Route::get('/admin/bookings/{id}', [AdminController::class, 'booking_view_page'])->name('admin.bookings.show');
        Route::get('/admin/bookings/{id}/edit', [AdminController::class, 'booking_edit'])->name('admin.bookings.edit');
        Route::get('/admin/bookings/pagination', [AdminController::class, 'bookings_pagination'])->name('admin.bookings.pagination');

        // Admin Payment Routes
        Route::get('/admin/payments', [AdminController::class, 'admin_payments'])->name('admin.payments');
        Route::get('/admin/payments/{id}', [AdminController::class, 'payment_view_page'])->name('admin.payments.show');
        Route::get('/admin/payments/{id}/view', [AdminController::class, 'payment_view'])->name('admin.payments.view');
        Route::put('/admin/payments/{id}', [AdminController::class, 'payment_update'])->name('admin.payments.update');
        Route::get('/admin/payments/pagination', [AdminController::class, 'payments_pagination'])->name('admin.payments.pagination');
        Route::post('/admin/payments/{id}/refund', [App\Http\Controllers\Admin\PaymentController::class, 'processRefund'])->name('admin.payments.refund');

        // Ground slots and details for booking modal
        Route::get('/admin/grounds/{id}/available-slots', [AdminController::class, 'getAvailableSlots'])->where('id', '[0-9]+')->name('admin.grounds.available-slots');
        Route::get('/admin/grounds/{id}/details', [AdminController::class, 'getGroundDetails'])->where('id', '[0-9]+')->name('admin.grounds.details');

        // Admin Contact Messages Routes
        Route::get('/admin/contact-messages', [ContactController::class, 'index'])->name('admin.contact.index');
        Route::get('/admin/contact-messages/{id}', [ContactController::class, 'show'])->name('admin.contact.show');
        Route::post('/admin/contact-messages/{id}/status', [ContactController::class, 'updateStatus'])->name('admin.contact.status');
        Route::delete('/admin/contact-messages/{id}', [ContactController::class, 'destroy'])->name('admin.contact.delete');
    });
});

// Welcome Page Routes
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// APK download route
Route::get('/download/app', function () {
    // Directories to search
    $dirs = [
        public_path(''),
        public_path('assets'),
        public_path('uploads'),
        public_path('storage'),
        storage_path('app/public'),
        base_path(''), // project root as last resort
    ];

    // Filename variants to support
    $names = [
        'GetBooking.apk',
        'Get Booking.apk',
        'getbooking.apk',
        'get booking.apk',
    ];

    $apkPath = null;
    $foundName = 'GetBooking.apk';
    foreach ($dirs as $dir) {
        foreach ($names as $name) {
            $candidate = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
            if (file_exists($candidate)) {
                $apkPath = $candidate;
                $foundName = basename($candidate);
                break 2;
            }
        }
    }

    if (!$apkPath) {
        abort(404, 'Application APK not found. Place GetBooking.apk in public/ or public/storage/.');
    }

    $headers = [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Content-Disposition' => 'attachment; filename="' . $foundName . '"',
        'Content-Length' => @filesize($apkPath) ?: null,
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ];

    return response()->download($apkPath, $foundName, array_filter($headers));
})->name('app.download');
Route::get('/grounds', [WelcomeController::class, 'index'])->name('grounds.index');
Route::get('/ground/{id}', [WelcomeController::class, 'showGround'])->name('ground.show');
Route::get('/ground/{id}/slots', [WelcomeController::class, 'getGroundSlots'])->name('ground.slots');
Route::post('/booking-summary', [WelcomeController::class, 'getBookingSummary'])->name('booking.summary');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/book-ground', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/book-ground-offline', [BookingController::class, 'storeOffline'])->name('booking.store.offline');
    Route::post('/complete-payment/{bookingId}', [BookingController::class, 'completePayment'])->name('booking.complete.payment');

    // Review routes
    Route::post('/store-review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/get-review/{id}', [ReviewController::class, 'show'])->name('reviews.show');
    Route::put('/update-review/{id}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/delete-review/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Review reply routes
    Route::post('/store-reply', [ReviewReplyController::class, 'store'])->name('replies.store');
    Route::get('/get-reply/{id}', [ReviewReplyController::class, 'show'])->name('replies.show');
    Route::put('/update-reply/{id}', [ReviewReplyController::class, 'update'])->name('replies.update');
    Route::delete('/delete-reply/{id}', [ReviewReplyController::class, 'destroy'])->name('replies.destroy');
});

// Payment callback route - accessible without authentication
Route::post('/payment-callback', [BookingController::class, 'handlePaymentCallback'])->name('payment.callback');

// Test Razorpay connection - remove in production
Route::get('/test/razorpay', function () {
    try {
        $razorpayKey = config('services.razorpay.key');
        $razorpaySecret = config('services.razorpay.secret');
        
        if (empty($razorpayKey) || empty($razorpaySecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Razorpay credentials not configured',
                'key_exists' => !empty($razorpayKey),
                'secret_exists' => !empty($razorpaySecret)
            ], 500);
        }
        
        $razorpay = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
        
        // Test connection by creating a test order (minimum 100 paise = 1 rupee)
        $testOrderData = [
            'receipt' => 'TEST_' . time(),
            'amount' => 100, // 1 rupee in paise
            'currency' => 'INR',
            'payment_capture' => 1,
            'notes' => [
                'test' => true
            ]
        ];
        
        $testOrder = $razorpay->order->create($testOrderData);
        
        return response()->json([
            'success' => true,
            'message' => 'Razorpay connection successful',
            'test_order_id' => $testOrder['id'] ?? 'N/A',
            'key_prefix' => substr($razorpayKey, 0, 10) . '...',
            'note' => 'Test order created successfully. This confirms Razorpay API is working.'
        ]);
    } catch (\Razorpay\Api\Errors\Error $e) {
        return response()->json([
            'success' => false,
            'message' => 'Razorpay API Error',
            'error' => [
                'code' => $e->getCode(),
                'description' => $e->getDescription(),
                'field' => $e->getField(),
                'message' => $e->getMessage(),
                'reason' => $e->getReason()
            ]
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error testing Razorpay connection',
            'error' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class' => get_class($e)
            ]
        ], 500);
    }
})->name('test.razorpay');

// Debug route - remove in production
Route::get('/debug/bookings', [App\Http\Controllers\User\UserController::class, 'debug_bookings'])->name('debug.bookings');
Route::get('/test/booking-data', [App\Http\Controllers\TestController::class, 'testBookingData'])->name('test.booking-data');

// Test client dashboard access
Route::get('/test-client-dashboard', function () {
    $user = \App\Models\User::where('user_type', 'client')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->route('client.dashboard');
    }
    return 'No client user found';
});

// Debug client dashboard
Route::get('/debug-client-dashboard', function () {
    $user = \App\Models\User::where('user_type', 'client')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        $client = $user->client;
        return response()->json([
            'user' => $user->toArray(),
            'client' => $client ? $client->toArray() : null,
            'dashboard_url' => route('client.dashboard')
        ]);
    }
    return response()->json(['error' => 'No client user found']);
});

// Add the new route for fetching reviews (accessible without auth)
Route::get('/get-ground-reviews/{groundId}', [\App\Http\Controllers\API\ReviewController::class, 'getGroundReviews'])
    ->name('get-ground-reviews')->withoutMiddleware(['auth']);

// Policy Pages Routes (accessible without authentication)
Route::get('/terms-and-conditions', [App\Http\Controllers\PolicyController::class, 'termsAndConditions'])->name('policy.terms');
Route::get('/contact-us', [App\Http\Controllers\PolicyController::class, 'contactUs'])->name('policy.contact');
Route::get('/cancellation-and-refund-policy', [App\Http\Controllers\PolicyController::class, 'cancellationAndRefund'])->name('policy.cancellation');
Route::get('/privacy-policy', [App\Http\Controllers\PolicyController::class, 'privacyPolicy'])->name('policy.privacy');
Route::get('/shipping-and-delivery-policy', [App\Http\Controllers\PolicyController::class, 'shippingAndDelivery'])->name('policy.shipping');

// Contact form routes
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
