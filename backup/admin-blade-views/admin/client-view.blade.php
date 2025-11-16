@extends('layouts.admin')

@section('title', 'Client Details')
@section('content')
    <main class="flex-1 overflow-y-auto p-4 page-transition">
        <div class="mb-4">
            <a href="{{ route('admin.clients') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Clients
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Client Profile Card -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in lg:col-span-1">
                <div class="relative h-40 bg-gradient-to-r from-indigo-500 to-purple-600">
                    <div class="absolute inset-0 bg-black opacity-20"></div>
                </div>
                <div class="relative px-6 pt-0 pb-6">
                    <div class="flex flex-col items-center">
                        <div class="w-24 h-24 bg-white rounded-full border-4 border-white shadow-md overflow-hidden -mt-12 mb-3">
                            <img src="{{ $client->profile_picture ? asset($client->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($client->name).'&color=7F9CF5&background=EBF4FF&size=200' }}"
                                 alt="{{ $client->name }}" class="w-full h-full object-cover">
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $client->name }}</h2>
                        <p class="text-gray-500">Client #{{ $client->id }}</p>

                        <div class="mt-3">
                            <span class="px-3 py-1 text-xs rounded-full {{ $client->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} font-medium">
                                {{ ucfirst($client->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-envelope text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $client->email }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-phone text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ $client->phone }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-venus-mars text-indigo-500 w-5"></i>
                            <span class="ml-3">{{ ucfirst($client->gender ?? 'Not specified') }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-calendar text-indigo-500 w-5"></i>
                            <span class="ml-3">Joined on {{ $client->created_at->format('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 flex space-x-3">
                        <button type="button" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-center edit-client-btn" data-client-id="{{ $client->id }}">
                            <i class="fas fa-edit mr-2"></i> Edit
                        </button>
                        <button type="button" class="px-4 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-50 delete-client-btn" data-client-id="{{ $client->id }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Client Details and Bookings -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Address Information -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i>
                            Address Information
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Full Address</h4>
                                <p class="font-medium">{{ $client->full_address ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Area</h4>
                                <p class="font-medium">{{ $client->area ?? 'Not provided' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">City</h4>
                                <p class="font-medium">{{ $client->city ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">Pincode</h4>
                                <p class="font-medium">{{ $client->pincode ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm text-gray-500 mb-1">State & Country</h4>
                                <p class="font-medium">{{ $client->state ? $client->state.', ' : '' }}{{ $client->country ?? 'India' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Grounds -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-map text-indigo-500 mr-2"></i>
                            Client Grounds
                        </h3>
                        <div>
                            <a href="{{ route('admin.grounds') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View All Grounds</a>
                        </div>
                    </div>
                    <div class="p-4">
                        @if(isset($grounds) && count($grounds) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($grounds as $ground)
                                    <div class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                        <div class="h-32 bg-gray-200 relative">
                                            @if($ground->images->count() > 0)
                                                <img src="{{ asset($ground->images->first()->image_path) }}" alt="{{ $ground->name }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="flex items-center justify-center h-full bg-gray-100 text-gray-400">
                                                    <i class="fas fa-image fa-2x"></i>
                                                </div>
                                            @endif
                                            <div class="absolute top-2 right-2">
                                                <span class="px-2 py-1 text-xs rounded-full {{ $ground->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} font-medium">
                                                    {{ ucfirst($ground->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="p-3">
                                            <h4 class="font-semibold text-gray-800">{{ $ground->name }}</h4>
                                            <p class="text-sm text-gray-500 mb-2">{{ $ground->location }}</p>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-indigo-600">₹{{ $ground->slots->count() == 1 ? $ground->slots->first()->price_per_slot : $ground->slots->random()->price_per_slot ?? 'N/A' }}/slot</span>
                                                <a href="{{ route('admin.grounds.show', $ground->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-map-marker-alt text-gray-300 text-4xl mb-3"></i>
                                <p>No grounds found for this client.</p>
                                <a href="{{ route('admin.grounds') }}" class="mt-3 px-4 py-2 inline-block bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 focus:outline-none">
                                    <i class="fas fa-plus mr-2"></i> Add a Ground
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Booking History -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-calendar-check text-indigo-500 mr-2"></i>
                            Ground Booking History
                        </h3>
                        <div>
                            <a href="{{ route('admin.bookings') }}" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                        </div>
                    </div>
                    <div class="p-4">
                        @if(isset($bookings) && count($bookings) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ground</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($bookings as $booking)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">#{{ $booking->booking_sku }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $booking->booking_date->format('d M Y') }}</div>
                                                    <div class="text-xs text-gray-500">{{ $booking->booking_time }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        @if($booking->details->isNotEmpty() && $booking->details->first()->ground)
                                                            {{ $booking->details->first()->ground->name }}
                                                        @else
                                                            Unknown Ground
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @php
                                                        $statusClass = 'bg-gray-100 text-gray-800';
                                                        if($booking->booking_status == 'completed') {
                                                            $statusClass = 'bg-green-100 text-green-800';
                                                        } elseif($booking->booking_status == 'cancelled') {
                                                            $statusClass = 'bg-red-100 text-red-800';
                                                        } elseif($booking->booking_status == 'pending') {
                                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                                        } elseif($booking->booking_status == 'confirmed') {
                                                            $statusClass = 'bg-blue-100 text-blue-800';
                                                        }
                                                    @endphp
                                                    <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }} font-medium">
                                                        {{ ucfirst($booking->booking_status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">₹{{ $booking->amount }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Booking">
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
                                <p>No bookings found for this client's grounds.</p>
                                <a href="{{ route('admin.bookings') }}" class="mt-3 px-4 py-2 inline-block bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 focus:outline-none">
                                    <i class="fas fa-plus mr-2"></i> Create a New Booking
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold flex items-center">
                            <i class="fas fa-history text-indigo-500 mr-2"></i>
                            Recent Activity
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li class="relative pb-8">
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-user-plus text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm text-gray-700">
                                                    Client was <span class="font-medium">registered</span>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    {{ $client->created_at->format('d M Y, h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="absolute top-0 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                </li>
                                @if($client->updated_at && $client->updated_at->ne($client->created_at))
                                <li class="relative pb-8">
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-edit text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm text-gray-700">
                                                    Client profile was <span class="font-medium">updated</span>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    {{ $client->updated_at->format('d M Y, h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="absolute top-0 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                </li>
                                @endif
                                @if(isset($lastBooking))
                                <li class="relative">
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas fa-calendar-check text-white text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm text-gray-700">
                                                    Last booking was <span class="font-medium">{{ $lastBooking->booking_status }}</span>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    {{ $lastBooking->created_at->format('d M Y, h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Simple Delete Confirmation Modal (directly in the page) -->
        <div id="simple-delete-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg max-w-md w-full mx-auto shadow-lg p-6 transform transition-all">
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trash text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">Delete Client</h3>
                    <p class="text-gray-500 mb-6">Are you sure you want to delete this client? This action cannot be undone and all associated data will be permanently removed.</p>

                    <div class="flex justify-center space-x-3">
                        <button type="button" id="cancel-delete-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 focus:outline-none">
                            Cancel
                        </button>
                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none delete-client-btn" title="Delete Client"
                            data-client-id="{{ $client->id }}">
                            <i class="fas fa-trash mr-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/page-handler.js') }}"></script>
    <script src="{{ asset('assets/admin/js/client-handler.js') }}"></script>

    <!-- Add inline popup HTML right before closing body tag -->
    <div id="delete-popup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:8px; width:90%; max-width:400px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:60px; height:60px; margin:0 auto 15px; background:#FEE2E2; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-trash" style="color:#EF4444; font-size:24px;"></i>
                </div>
                <h3 style="font-size:18px; font-weight:600; margin-bottom:10px;">Delete Client</h3>
                <p style="color:#6B7280; margin-bottom:20px;">Are you sure you want to delete this client? This action cannot be undone.</p>
            </div>
            <div style="display:flex; justify-content:center; gap:10px;">
                <button id="cancel-delete" style="padding:8px 16px; background:#E5E7EB; color:#374151; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
                <button id="confirm-delete" style="padding:8px 16px; background:#EF4444; color:white; border:none; border-radius:6px; cursor:pointer;">Delete Client</button>
            </div>
        </div>
    </div>
@endsection

@section('modals')
    @include('admin.modals.client-modal')
    @include('admin.modals.delete-confirm-modal')
@endsection
