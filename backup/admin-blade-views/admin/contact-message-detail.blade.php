@extends('layouts.admin')

@section('title', 'Contact Message Details')

@section('content')
<main class="flex-1 overflow-y-auto p-4 page-transition">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.contact.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Message Details</h2>
                    <p class="text-sm text-gray-500">View and manage contact message</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 text-xs font-medium rounded-full
                    @if($message->status == 'new') bg-green-100 text-green-800
                    @elseif($message->status == 'read') bg-blue-100 text-blue-800
                    @else bg-purple-100 text-purple-800
                    @endif">
                    <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                    {{ ucfirst($message->status) }}
                </span>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Message Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $message->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $message->email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Subject</label>
                                <p class="text-gray-900 font-medium">{{ $message->subject }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Received</label>
                                <p class="text-gray-900">{{ $message->created_at->format('M d, Y \a\t H:i') }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500 mb-2 block">Message</label>
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $message->message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Reply Section -->
                    @if($message->admin_reply)
                    <div class="bg-blue-50 rounded-xl p-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-reply text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Admin Reply</h3>
                                <p class="text-sm text-gray-500">
                                    Replied on {{ $message->replied_at->format('M d, Y \a\t H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="bg-white border border-blue-200 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ $message->admin_reply }}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button onclick="openReplyModal()"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                                <i class="fas fa-reply"></i>
                                <span>Reply to Customer</span>
                            </button>

                            <button onclick="openStatusModal()"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                                <i class="fas fa-edit"></i>
                                <span>Update Status</span>
                            </button>

                            <button onclick="deleteMessage()"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                                <i class="fas fa-trash"></i>
                                <span>Delete Message</span>
                            </button>
                        </div>
                    </div>

                    <!-- Message Statistics -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Message Info</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Message ID</span>
                                <span class="text-sm font-medium text-gray-900">#{{ $message->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Character Count</span>
                                <span class="text-sm font-medium text-gray-900">{{ strlen($message->message) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Word Count</span>
                                <span class="text-sm font-medium text-gray-900">{{ str_word_count($message->message) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Last Updated</span>
                                <span class="text-sm font-medium text-gray-900">{{ $message->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Actions -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Actions</h3>
                        <div class="space-y-3">
                            <a href="mailto:{{ $message->email }}"
                               class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                                <i class="fas fa-envelope"></i>
                                <span>Send Email</span>
                            </a>

                            <button onclick="copyEmail()"
                                    class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
                                <i class="fas fa-copy"></i>
                                <span>Copy Email</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Reply Modal -->
<div id="replyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Reply to Customer</h3>
            <button onclick="closeReplyModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="replyForm" class="p-6">
            <input type="hidden" name="message_id" value="{{ $message->id }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                    <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           value="{{ $message->name }} <{{ $message->email }}>" readonly>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input type="text" name="subject" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           value="Re: {{ $message->subject }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea name="admin_reply" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Enter your reply..." required></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeReplyModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Send Reply
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Update Status</h3>
            <button onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="statusForm" class="p-6">
            <input type="hidden" name="message_id" value="{{ $message->id }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="new" {{ $message->status === 'new' ? 'selected' : '' }}>New</option>
                        <option value="read" {{ $message->status === 'read' ? 'selected' : '' }}>Read</option>
                        <option value="replied" {{ $message->status === 'replied' ? 'selected' : '' }}>Replied</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeStatusModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Modal functions
function openReplyModal() {
    document.getElementById('replyModal').classList.remove('hidden');
}

function closeReplyModal() {
    document.getElementById('replyModal').classList.add('hidden');
}

function openStatusModal() {
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}

// Copy email function
function copyEmail() {
    navigator.clipboard.writeText('{{ $message->email }}').then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg z-50';
        toast.textContent = 'Email copied to clipboard!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
}

// Delete message function
function deleteMessage() {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        fetch(`/admin/contact-messages/{{ $message->id }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("admin.contact.index") }}';
            } else {
                alert('Error deleting message: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again.');
        });
    }
}

// Form submissions
document.addEventListener('DOMContentLoaded', function() {
    // Reply form submission
    document.getElementById('replyForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(`/admin/contact-messages/{{ $message->id }}/status`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeReplyModal();
                location.reload();
            } else {
                alert('Error sending reply: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again.');
        });
    });

    // Status form submission
    document.getElementById('statusForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(`/admin/contact-messages/{{ $message->id }}/status`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeStatusModal();
                location.reload();
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong. Please try again.');
        });
    });
});
</script>
@endsection
