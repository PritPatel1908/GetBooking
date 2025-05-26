<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\PaymentController;
use App\Http\Controllers\User\GroundController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewReplyController;

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

// Guest routes (unauthenticated users)
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
    // Registration routes
    Route::post('/register', [LoginController::class, 'register'])->name('register');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // User routes
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/home', [UserController::class, 'home'])->name('user.home');
    Route::get('/all_grounds', [UserController::class, 'all_grounds'])->name('user.all_grounds');
    Route::get('/view_ground/{id}', [UserController::class, 'view_ground'])->name('user.view_ground');
    Route::get('/my_bookings', [UserController::class, 'my_bookings'])->name('user.my_bookings');
    Route::get('/pending-payments', [PaymentController::class, 'pendingPayments'])->name('user.pending-payments');

    // Ground booking related routes
    Route::get('/get-ground-slots/{date}/{groundId}', [UserController::class, 'getGroundSlots']);
    Route::post('/book-ground', [UserController::class, 'bookGround'])->name('user.ground.book');
    Route::get('/ground-details/{id}', [UserController::class, 'getGroundDetails'])->name('user.ground.details');

    // Payment related routes
    Route::post('/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment-callback', [PaymentController::class, 'paymentCallback'])->name('payment.callback');

    // Admin routes
    Route::get('/admin/dashboard', [AdminController::class, 'admin_home'])->name('admin.dashboard');
    Route::get('/admin/clients', [AdminController::class, 'admin_clients'])->name('admin.clients');
    Route::get('/admin/clients/pagination', [AdminController::class, 'clients_pagination'])->name('admin.clients.pagination');
    Route::get('/admin/clients/{id}', [AdminController::class, 'client_view_page'])->name('admin.clients.show');

    // Admin Ground routes
    Route::get('/admin/grounds', [AdminController::class, 'admin_grounds'])->name('admin.grounds');
    Route::get('/admin/grounds/pagination', [AdminController::class, 'grounds_pagination'])->name('admin.grounds.pagination');
    Route::get('/admin/grounds/{id}', [AdminController::class, 'ground_view_page'])->name('admin.grounds.show');

    // Admin API Routes
    Route::post('/admin/clients/create', [AdminController::class, 'client_create'])->name('admin.clients.store');
    Route::delete('/admin/clients/{id}', [AdminController::class, 'client_delete'])->name('admin.clients.delete');
    Route::get('/admin/clients/{id}/edit', [AdminController::class, 'client_edit'])->name('admin.clients.edit');
    Route::get('/admin/clients/{id}/view', [AdminController::class, 'client_view'])->name('admin.clients.view');

    // Admin Ground API Routes
    Route::post('/admin/grounds/create', [AdminController::class, 'ground_create'])->name('admin.grounds.store');
    Route::delete('/admin/grounds/{id}', [AdminController::class, 'ground_delete'])->name('admin.grounds.delete');
    Route::get('/admin/grounds/{id}/edit', [AdminController::class, 'ground_edit'])->name('admin.grounds.edit');
    Route::get('/admin/grounds/{id}/view', [AdminController::class, 'ground_view'])->name('admin.grounds.view');
    Route::delete('/admin/ground-images/{id}', [AdminController::class, 'ground_image_delete'])->name('admin.ground_images.delete');
    Route::post('/admin/ground-images/upload', [AdminController::class, 'ground_image_upload'])->name('admin.ground_images.upload');
    Route::get('/admin/grounds/{id}/upload-images', [AdminController::class, 'ground_image_upload_page'])->name('admin.grounds.upload_images');

    // Admin Client API endpoint for dropdown
    Route::get('/api/clients', [AdminController::class, 'get_clients'])->name('api.clients');

    // Ground routes
    Route::get('/grounds', [GroundController::class, 'allGrounds'])->name('user.all_grounds');
    Route::get('/grounds/{id}', [GroundController::class, 'viewGround'])->name('user.view_ground');

    // User booking routes
    Route::get('/my-bookings/{bookingSku}', [UserController::class, 'view_booking'])->name('user.view_booking');
    Route::post('/user/bookings/{id}/cancel', [BookingController::class, 'cancelBooking'])->name('user.cancel_booking');
    Route::get('/user/bookings/{bookingSku}/invoice', [UserController::class, 'download_invoice'])->name('user.download_invoice');

    // Profile routes
    Route::get('/profile', [App\Http\Controllers\User\ProfileController::class, 'show'])->name('user.profile');
    Route::post('/profile', [App\Http\Controllers\User\ProfileController::class, 'update'])->name('user.profile.update');
    Route::post('/profile/password', [App\Http\Controllers\User\ProfileController::class, 'updatePassword'])->name('user.password.update');

    // Transaction routes
    Route::get('/user/transaction/{id}', [PaymentController::class, 'viewTransaction'])->name('user.transaction.view');

    // Admin Booking Routes
    Route::get('/admin/bookings', [AdminController::class, 'admin_bookings'])->name('admin.bookings');
    Route::post('/admin/bookings', [AdminController::class, 'booking_create'])->name('admin.bookings.store');
    Route::put('/admin/bookings/{id}', [AdminController::class, 'booking_update'])->name('admin.bookings.update');
    Route::delete('/admin/bookings/{id}', [AdminController::class, 'booking_delete'])->name('admin.bookings.delete');
    Route::get('/admin/bookings/{id}', [AdminController::class, 'booking_view_page'])->name('admin.bookings.show');
    Route::get('/admin/bookings/{id}/edit', [AdminController::class, 'booking_edit'])->name('admin.bookings.edit');
    Route::get('/admin/bookings/pagination', [AdminController::class, 'bookings_pagination'])->name('admin.bookings.pagination');

    // Ground slots and details for booking modal
    Route::get('/admin/grounds/{id}/available-slots', [AdminController::class, 'getAvailableSlots'])->name('admin.grounds.available-slots');
    Route::get('/admin/grounds/{id}/details', [AdminController::class, 'getGroundDetails'])->name('admin.grounds.details');
});

// Welcome Page Routes
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/grounds', [WelcomeController::class, 'index'])->name('grounds.index');
Route::get('/ground/{id}', [WelcomeController::class, 'showGround'])->name('ground.show');
Route::get('/ground/{id}/slots', [WelcomeController::class, 'getGroundSlots'])->name('ground.slots');
Route::post('/booking-summary', [WelcomeController::class, 'getBookingSummary'])->name('booking.summary');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/book-ground', [BookingController::class, 'store'])->name('booking.store');

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

// Debug route - remove in production
Route::get('/debug/bookings', [App\Http\Controllers\User\UserController::class, 'debug_bookings'])->name('debug.bookings');
