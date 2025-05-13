<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Auth;

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

    // Ground booking related routes
    Route::get('/get-ground-slots/{date}/{groundId}', [UserController::class, 'getGroundSlots']);
    Route::post('/book-ground', [UserController::class, 'bookGround'])->name('user.ground.book');
    Route::get('/ground-details/{id}', [UserController::class, 'getGroundDetails'])->name('user.ground.details');

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
});

// Home redirect
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('user.home');
    } else {
        return view('welcome.welcome');
    }
})->name('welcome');
