@extends('layouts.admin')

@section('title', 'Grounds')
@section('content')
    <main class="flex-1 overflow-y-auto p-4 page-transition">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-xl font-semibold">Grounds Management</h2>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 btn-hover-effect add-ground-btn">
                    <i class="fas fa-plus"></i>
                    <span>Add Ground</span>
                </button>
            </div>

            <div class="p-4">
                <div class="flex flex-wrap gap-4 mb-6">
                    <div class="flex-1 min-w-[250px]">
                        <div class="relative">
                            <input type="text" placeholder="Search grounds..." class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <select class="rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <select class="rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Sort By</option>
                            <option value="name">Name</option>
                            <option value="price">Price</option>
                            <option value="bookings">Bookings</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ground</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price/Hour</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($grounds as $ground)
                            <tr class="hover:bg-gray-50 transition-colors" data-ground-id="{{ $ground->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($ground->images)
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
                                                        if ($ground->images instanceof \Illuminate\Support\Collection || $ground->images instanceof \Illuminate\Database\Eloquent\Collection) {
                                                            if (!$ground->images->isEmpty()) {
                                                                $displayImage = $ground->images->random();
                                                            }
                                                        }
                                                    }
                                                } catch (Exception $e) {
                                                    $displayImage = null;
                                                }
                                            @endphp
                                            @if(!empty($displayImage))
                                                @if(is_array($displayImage) && isset($displayImage['image_path']))
                                                    <img class="h-10 w-10 rounded-lg mr-3 object-cover" src="{{ asset($displayImage['image_path']) }}" alt="{{ $ground->name }}">
                                                @elseif(is_object($displayImage) && isset($displayImage->image_path))
                                                    <img class="h-10 w-10 rounded-lg mr-3 object-cover" src="{{ asset($displayImage->image_path) }}" alt="{{ $ground->name }}">
                                                @elseif(is_string($displayImage))
                                                    <img class="h-10 w-10 rounded-lg mr-3 object-cover" src="{{ asset($displayImage) }}" alt="{{ $ground->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-lg mr-3 flex items-center justify-center bg-indigo-100 text-indigo-800 font-bold">
                                                        {{ strtoupper(substr($ground->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            @elseif($ground->ground_image)
                                                <img class="h-10 w-10 rounded-lg mr-3 object-cover" src="{{ asset($ground->ground_image) }}" alt="{{ $ground->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-lg mr-3 flex items-center justify-center bg-indigo-100 text-indigo-800 font-bold">
                                                    {{ strtoupper(substr($ground->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="h-10 w-10 rounded-lg mr-3 flex items-center justify-center bg-indigo-100 text-indigo-800 font-bold">
                                                {{ strtoupper(substr($ground->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $ground->name }}</div>
                                            <div class="text-sm text-gray-500">{{ ucfirst($ground->ground_type ?? 'Standard') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $ground->location }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">₹{{ $ground->slots->count() == 1 ? $ground->slots->first()->price_per_slot : $ground->slots->random()->price_per_slot ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $ground->capacity }} people</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $ground->bookings_count ?? 0 }} bookings</div>
                                    <div class="text-xs text-gray-500">{{ $ground->last_booking_date ?? 'No bookings' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $ground->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} font-medium">
                                        {{ ucfirst($ground->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.grounds.show', $ground->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Ground">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="text-blue-600 hover:text-blue-900 transition-colors edit-ground-btn" title="Edit Ground" data-ground-id="{{ $ground->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900 transition-colors delete-ground-btn" title="Delete Ground" data-ground-id="{{ $ground->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No grounds found. Add your first ground by clicking the "Add Ground" button.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between mt-6">
                    <div class="text-sm text-gray-600">
                        Showing {{ $grounds->firstItem() ?? 0 }} to {{ $grounds->lastItem() ?? 0 }} of {{ $grounds->total() ?? 0 }} entries
                    </div>
                    <div>
                        {{ $grounds->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('modals')
    @include('admin.modals.ground-modal')
    @include('admin.modals.delete-confirm-modal')
@endsection

@section('scripts')
    <script src="{{ asset('assets/admin/js/page-handler.js') }}"></script>
    <script src="{{ asset('assets/admin/js/ground-handler.js') }}"></script>

    <!-- Add inline popup HTML right before closing body tag -->
    <div id="delete-popup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:8px; width:90%; max-width:400px; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:60px; height:60px; margin:0 auto 15px; background:#FEE2E2; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-trash" style="color:#EF4444; font-size:24px;"></i>
                </div>
                <h3 style="font-size:18px; font-weight:600; margin-bottom:10px;">Delete Ground</h3>
                <p style="color:#6B7280; margin-bottom:20px;">Are you sure you want to delete this ground? This action cannot be undone.</p>
            </div>
            <div style="display:flex; justify-content:center; gap:10px;">
                <button id="cancel-delete" style="padding:8px 16px; background:#E5E7EB; color:#374151; border:none; border-radius:6px; cursor:pointer;">Cancel</button>
                <button id="confirm-delete" style="padding:8px 16px; background:#EF4444; color:white; border:none; border-radius:6px; cursor:pointer;">Delete Ground</button>
            </div>
        </div>
    </div>
@endsection
