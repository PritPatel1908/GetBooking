@extends('layouts.admin')

@section('title', 'Contact Messages')

@section('content')
<main class="flex-1 overflow-y-auto p-4 page-transition">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Contact Messages</h2>
                    <p class="text-sm text-gray-500">Manage customer inquiries and support requests</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-2xl font-bold text-indigo-600">{{ $messages->total() }}</div>
                    <div class="text-sm text-gray-500">Total Messages</div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-green-600">{{ $messages->where('status', 'new')->count() }}</div>
                    <div class="text-sm text-gray-500">New Messages</div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="p-4 border-b bg-gray-50">
            <div class="flex flex-wrap gap-4 mb-4">
                <div class="flex-1 min-w-[250px]">
                    <div class="relative">
                        <input type="text" placeholder="Search messages..." class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <select class="rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                    </select>
                    <select class="rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Sort By</option>
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="name">Name</option>
                        <option value="subject">Subject</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message Preview</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($messages as $message)
                    <tr class="hover:bg-gray-50 transition-colors" data-message-id="{{ $message->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $message->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-indigo-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $message->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $message->email }}</div>
                                    @if($message->status == 'new')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                        <i class="fas fa-circle text-green-500 mr-1" style="font-size: 6px;"></i>
                                        NEW
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $message->subject }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-xs truncate" title="{{ $message->message }}">
                                {{ Str::limit($message->message, 80) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($message->status == 'new') bg-green-100 text-green-800
                                @elseif($message->status == 'read') bg-blue-100 text-blue-800
                                @else bg-purple-100 text-purple-800
                                @endif">
                                <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                {{ ucfirst($message->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $message->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $message->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 action-group">
                                <a href="{{ route('admin.contact.show', $message->id) }}"
                                   class="text-indigo-600 hover:text-indigo-900 transition-colors action-button"
                                   title="View Message">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="text-blue-600 hover:text-blue-900 transition-colors action-button"
                                        title="Reply to Message"
                                        onclick="replyToMessage({{ $message->id }})">
                                    <i class="fas fa-reply"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.contact.delete', $message->id) }}"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this message?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-900 transition-colors action-button"
                                            title="Delete Message">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-envelope-open text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No messages found</h3>
                                <p class="text-sm text-gray-500 max-w-md">Contact messages will appear here when users submit the contact form. Check back later for new inquiries.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($messages->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ $messages->firstItem() ?? 0 }} to {{ $messages->lastItem() ?? 0 }} of {{ $messages->total() ?? 0 }} entries
                </div>
                <div>
                    {{ $messages->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</main>
@endsection

@section('scripts')
<script>
function replyToMessage(messageId) {
    // This would open a reply modal or redirect to reply page
    window.location.href = `/admin/contact-messages/${messageId}?action=reply`;
}

// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[placeholder="Search messages..."]');
    const statusFilter = document.querySelector('select');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Status filter functionality
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            if (!selectedStatus) {
                row.style.display = '';
                return;
            }

            const statusCell = row.querySelector('td:nth-child(5) span');
            if (statusCell) {
                const status = statusCell.textContent.toLowerCase().trim();
                if (status.includes(selectedStatus)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
});
</script>
@endsection
