@extends('layouts.admin')

@section('title', 'Clients')
@section('content')
    <main class="flex-1 overflow-y-auto p-4 page-transition">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-xl font-semibold">Clients Management</h2>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 btn-hover-effect add-client-btn">
                    <i class="fas fa-plus"></i>
                    <span>Add Client</span>
                </button>
            </div>

            <div class="p-4">
                <div class="flex flex-wrap gap-4 mb-6">
                    <div class="flex-1 min-w-[250px]">
                        <div class="relative">
                            <input type="text" placeholder="Search clients..." class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
                            <option value="date">Registration Date</option>
                            <option value="bookings">Bookings</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($clients as $client)
                            <tr class="hover:bg-gray-50 transition-colors" data-client-id="{{ $client->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img class="h-10 w-10 rounded-full mr-3" src="{{ $client->profile_picture ? asset($client->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($client->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $client->name }}">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $client->name }}</div>
                                            <div class="text-sm text-gray-500">{{ ucfirst($client->gender ?? 'Individual') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $client->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $client->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $client->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $client->bookings_count ?? 0 }} bookings</div>
                                    <div class="text-xs text-gray-500">{{ $client->last_booking_date ?? 'No bookings' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $client->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} font-medium">
                                        {{ ucfirst($client->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.clients.show', $client->id) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Client">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="text-blue-600 hover:text-blue-900 transition-colors edit-client-btn" title="Edit Client" data-client-id="{{ $client->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900 transition-colors delete-client-btn" title="Delete Client" data-client-id="{{ $client->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No clients found. Add your first client by clicking the "Add Client" button.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between mt-6">
                    <div class="text-sm text-gray-600">
                        Showing {{ $clients->firstItem() ?? 0 }} to {{ $clients->lastItem() ?? 0 }} of {{ $clients->total() ?? 0 }} entries
                    </div>
                    <div>
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('modals')
    @include('admin.modals.client-modal')
    @include('admin.modals.delete-confirm-modal')
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
