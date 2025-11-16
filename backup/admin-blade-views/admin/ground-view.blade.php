@extends('layouts.admin')

@section('title', 'Ground Details')
@section('content')
    <main class="flex-1 overflow-y-auto p-4 page-transition">
        <div class="mb-4">
            <a href="{{ route('admin.grounds') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Grounds
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Ground Profile Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in lg:col-span-1">
                <div class="relative h-40 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <div class="absolute inset-0 bg-black opacity-20"></div>
                </div>
                <div class="relative px-6 pt-0 pb-6">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-24 h-24 bg-white rounded-lg border-4 border-white shadow-md overflow-hidden -mt-12 mb-3">
                            @if ($ground->images)
                                @php
                                    try {
                                        $displayImage = null;

                                        if (is_string($ground->images)) {
                                            $imagesArray = json_decode($ground->images, true);
                                            if (json_last_error() === JSON_ERROR_NONE && !empty($imagesArray)) {
                                                // Get a random image from the array
                                                $randomIndex = array_rand($imagesArray);
                                                $displayImage = $imagesArray[$randomIndex];
                                            }
                                        } elseif (is_array($ground->images) && !empty($ground->images)) {
                                            // Get a random image from the array
                                            $randomIndex = array_rand($ground->images);
                                            $displayImage = $ground->images[$randomIndex];
                                        } else {
                                            $displayImage = $ground->images;
                                            if (
                                                $ground->images instanceof \Illuminate\Support\Collection ||
                                                $ground->images instanceof \Illuminate\Database\Eloquent\Collection
                                            ) {
                                                if (!$ground->images->isEmpty()) {
                                                    $displayImage = $ground->images->random();
                                                }
                                            }
                                        }
                                    } catch (Exception $e) {
                                        $displayImage = null;
                                    }
                                @endphp
                                @if (!empty($displayImage))
                                    @if (is_array($displayImage) && isset($displayImage['image_path']))
                                        <img src="{{ asset($displayImage['image_path']) }}"
                                            alt="{{ $ground->name }}" class="w-full h-full object-cover">
                                    @elseif(is_object($displayImage) && isset($displayImage->image_path))
                                        <img src="{{ asset($displayImage->image_path) }}" alt="{{ $ground->name }}" class="w-full h-full object-cover">
                                    @elseif(is_string($displayImage))
                                        <img src="{{ asset($displayImage) }}" alt="{{ $ground->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-800 font-bold text-2xl">
                                            {{ strtoupper(substr($ground->name, 0, 1)) }}
                                        </div>
                                    @endif
                                @elseif($ground->ground_image)
                                    <img class="w-full h-full object-cover"
                                        src="{{ asset($ground->ground_image) }}" alt="{{ $ground->name }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-800 font-bold text-2xl">
                                        {{ strtoupper(substr($ground->name, 0, 1)) }}
                                    </div>
                                @endif
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-indigo-100 text-indigo-800 font-bold text-2xl">
                                    {{ strtoupper(substr($ground->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $ground->name }}</h2>
                        <p class="text-gray-500">Ground #{{ $ground->id }}</p>

                        <div class="mt-3">
                            <span
                                class="px-3 py-1 text-xs rounded-full {{ $ground->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} font-medium">
                                {{ ucfirst($ground->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-map-marker-alt text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $ground->location }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-coins text-indigo-500 w-5"></i>
                            <span class="ml-3">₹{{ $ground->slots->count() == 1 ? $ground->slots->first()->price_per_slot : $ground->slots->random()->price_per_slot ?? 'N/A' }} per slot</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-users text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $ground->capacity }} people capacity</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-calendar text-indigo-500 w-5"></i>
                            <span class="ml-3">Added on {{ $ground->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 flex space-x-3">
                        <button type="button"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-center edit-ground-btn"
                            data-ground-id="{{ $ground->id }}">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </button>
                        <button type="button"
                            class="px-4 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 delete-ground-btn"
                            data-ground-id="{{ $ground->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ground Details and Bookings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Ground Details -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-info-circle text-indigo-500 mr-2"></i>
                            Ground Details
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Description</h4>
                                <p class="font-medium">{{ $ground->description ?? 'No description provided' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Ground Type</h4>
                                <p class="font-medium">{{ ucfirst($ground->ground_type ?? 'Standard') }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Ground Category</h4>
                                <p class="font-medium">{{ ucfirst($ground->ground_category ?? 'All Grounds') }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Rules</h4>
                                <p class="font-medium">{{ $ground->rules ?? 'No specific rules' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Ground Status</h4>
                                <p class="font-medium">
                                    @if ($ground->is_new)
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-medium">New</span>
                                    @elseif($ground->is_featured)
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700 font-medium">Featured</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 font-medium">Standard</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Operating Hours</h4>
                                <p class="font-medium">
                                    @if ($ground->opening_time && $ground->closing_time)
                                        {{ $ground->opening_time }} - {{ $ground->closing_time }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Total Bookings</h4>
                                <p class="font-medium">{{ $bookingsCount ?? 0 }} bookings to date</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking History -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-calendar-check text-indigo-500 mr-2"></i>
                            Booking History
                        </h3>
                        <div>
                            <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                        </div>
                    </div>
                    <div class="p-4">
                        @if (isset($bookings) && count($bookings) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Booking ID</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date & Time</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Client</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount</th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($bookings as $booking)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">#{{ $booking->id }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $booking->booking_date }}</div>
                                                    <div class="text-xs text-gray-500">{{ $booking->booking_time }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $booking->client_name }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @php
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                        if ($booking->status == 'completed') {
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                        } elseif ($booking->status == 'cancelled') {
                                                            $statusClass = 'bg-red-100 text-red-800';
                                                        } elseif ($booking->status == 'pending') {
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                        } elseif ($booking->status == 'confirmed') {
                                                            $statusClass = 'bg-blue-100 text-blue-800';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="px-2 py-1 text-xs rounded-full {{ $statusClass }} font-medium">
                                                        {{ ucfirst($booking->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">₹{{ $booking->amount }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <a href="#"
                                                        class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                                        title="View Booking">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-calendar-times text-gray-300 text-4xl mb-3"></i>
                                <p>No bookings found for this ground.</p>
                                <button
                                    class="mt-3 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 focus:outline-none">
                                    <i class="fas fa-plus mr-2"></i> Create a New Booking
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Ground Photos -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-images text-indigo-500 mr-2"></i>
                            Ground Photos
                        </h3>
                        <a href="{{ route('admin.grounds.upload_images', $ground->id) }}"
                            class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                            <i class="fas fa-plus mr-1"></i> Add Photos
                        </a>
                    </div>
                    <div class="p-4">
                        @if (isset($photos) && count($photos) > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach ($photos as $photo)
                                    <div class="bg-gray-100 rounded-lg overflow-hidden h-36 relative group">
                                        <img src="{{ asset($photo->image_path) }}" alt="Ground Photo"
                                            class="w-full h-full object-cover">
                                        <div
                                            class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                                            <button
                                                class="p-2 bg-white rounded-full text-gray-700 hover:text-indigo-600 transition-colors view-photo-btn"
                                                data-image-path="{{ asset($photo->image_path) }}">
                                                <i class="fas fa-expand"></i>
                                            </button>
                                            <button
                                                class="p-2 bg-white rounded-full text-gray-700 hover:text-red-600 transition-colors delete-photo-btn"
                                                data-image-id="{{ $photo->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-camera text-gray-300 text-4xl mb-3"></i>
                                <p>No photos available for this ground.</p>
                                <button
                                    class="mt-3 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 focus:outline-none upload-photos-btn"
                                    data-ground-id="{{ $ground->id }}">
                                    <i class="fas fa-upload mr-2"></i> Upload Photos
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('modals')
    @include('admin.modals.ground-modal')
    @include('admin.modals.delete-confirm-modal')
    @include('admin.modals.ground-image-upload-modal')

    <!-- Toast Notification -->
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
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
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
    <script src="{{ asset('assets/admin/js/ground-handler.js') }}"></script>
    <script src="{{ asset('assets/admin/js/test-modal.js') }}"></script>
    <script src="{{ asset('assets/admin/js/upload-redirect.js') }}"></script>

    @if (session('toast_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("{{ session('toast_success') }}", 'success');
            });
        </script>
    @endif
@endsection
