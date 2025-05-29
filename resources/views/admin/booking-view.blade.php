@extends('layouts.admin')

@section('title', 'Booking Details')
@section('content')
    <main class="flex-1 overflow-y-auto p-4 page-transition">
        <div class="mb-4">
            <a href="{{ route('admin.bookings') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Booking Profile Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in lg:col-span-1">
                <div class="relative h-40 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <div class="absolute inset-0 bg-black opacity-20"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-white text-4xl font-bold">
                            {{ strtoupper(substr($booking->booking_sku, 0, 1)) }}
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">{{ $booking->booking_sku }}</h3>
                        <span class="px-3 py-1 text-xs rounded-full font-medium
                            {{ $booking->booking_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $booking->booking_status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $booking->booking_status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $booking->booking_status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($booking->booking_status) }}
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-user text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $booking->user->name }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-envelope text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $booking->user->email }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-calendar text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $booking->booking_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-clock text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $booking->booking_time }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-coins text-indigo-500 w-5"></i>
                            <span class="ml-3">₹{{ number_format($booking->amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 flex space-x-3">
                        <button type="button" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-center edit-booking-btn" data-booking-id="{{ $booking->id }}">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </button>
                        <button type="button" class="px-4 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 delete-booking-btn" data-booking-id="{{ $booking->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ground Information -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i>
                            Ground Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Ground Name</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $booking->ground->name }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Location</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $booking->ground->location }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Ground Type</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ ucfirst($booking->ground->ground_type) }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Ground Category</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ ucfirst($booking->ground->ground_category) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-credit-card text-indigo-500 mr-2"></i>
                            Payment Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Payment Status</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ ucfirst($booking->payment->payment_status ?? 'Pending') }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Payment Method</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ ucfirst($booking->payment->payment_method ?? 'Not specified') }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Transaction ID</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $booking->payment->transaction_id ?? 'Not available' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Payment Date</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ isset($booking->payment) && $booking->payment->created_at ? $booking->payment->created_at->format('d M Y H:i') : 'Not available' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-user text-indigo-500 mr-2"></i>
                            Customer Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $booking->user->name }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Email</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $booking->user->email }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Phone</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $booking->user->phone ?? 'Not available' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500">Member Since</h4>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ isset($booking->user) && $booking->user->created_at ? $booking->user->created_at->format('d M Y') : 'Not available' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('modals')
    @include('admin.modals.booking-modal')
    @include('admin.modals.delete-confirm-modal')

    <div id="toast-notification"
        class="fixed top-4 right-4 z-50 transform transition-transform duration-300 ease-in-out translate-x-full">
        <div class="flex items-center p-4 mb-4 text-gray-500 bg-white rounded-lg shadow max-w-xs">
            <div id="toast-icon" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20"></svg>
            </div>
            <div id="toast-message" class="ml-3 text-sm font-normal"></div>
            <button type="button"
                class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8"
                onclick="hideToast()">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = document.getElementById('toast-icon');

            // Set message
            toastMessage.textContent = message;

            // Set icon and colors based on type
            if (type === 'success') {
                toastIcon.className =
                    'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg';
                toastIcon.innerHTML =
                    '<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/></svg>';
            } else if (type === 'error') {
                toastIcon.className =
                    'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg';
                toastIcon.innerHTML =
                    '<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.5 11.5a1 1 0 0 1-2 0v-4a1 1 0 0 1 2 0Zm-3.5-1a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>';
            }

            // Show toast
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');

            // Auto hide after 5 seconds
            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('toast-notification');
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
        }
    </script>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/page-handler.js') }}"></script>
    <script src="{{ asset('assets/admin/js/booking-handler.js') }}"></script>

    <script>
        // Connect edit button on booking view page with the modal's editBooking function
        document.addEventListener('DOMContentLoaded', function() {
            const editBookingBtn = document.querySelector('.edit-booking-btn');

            if (editBookingBtn) {
                // Remove the inline onclick attribute if it exists to prevent duplicate calls
                editBookingBtn.removeAttribute('onclick');

                editBookingBtn.addEventListener('click', function() {
                    const bookingId = this.getAttribute('data-booking-id');

                    // Only call editBooking if it hasn't been triggered already
                    if (typeof window.editBooking === 'function' && !window.editBookingInProgress) {
                        window.editBookingInProgress = true;
                        window.editBooking(bookingId);

                        // Reset the flag after a short delay
                        setTimeout(function() {
                            window.editBookingInProgress = false;
                        }, 500);
                    } else if (window.editBookingInProgress) {
                        console.log('Edit booking already in progress, ignoring duplicate click');
                    } else {
                        // Fallback if the function isn't available
                        console.error('editBooking function not found');
                        alert('Could not open booking editor. Please try again.');
                    }
                });
            }
        });
    </script>
@endsection

