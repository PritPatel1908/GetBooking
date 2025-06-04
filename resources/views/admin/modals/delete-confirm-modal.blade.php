<!-- Delete Confirmation Modal -->
<div id="delete-confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="bg-white rounded-lg max-w-md mx-auto mt-20 overflow-hidden shadow-xl">
        <div class="p-6">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-red-100">
                    <i class="fas fa-trash-alt text-red-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Confirmation</h3>
                <p class="text-gray-500 mb-6">Are you sure you want to delete this item? This action cannot be undone.</p>

                <div class="flex justify-center space-x-3">
                    <button id="cancel-delete-modal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button id="confirm-delete-modal" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Store client ID for delete operation
    let clientToDelete = null;

    // Function to show delete confirmation
    function showDeleteConfirmModal(clientId) {
        clientToDelete = clientId;
        document.getElementById('delete-confirm-modal').classList.remove('hidden');
    }

    // Function to hide delete confirmation
    function hideDeleteModal() {
        document.getElementById('delete-confirm-modal').classList.add('hidden');
    }

    // Function to confirm delete
    function deleteClientConfirmed() {
        if (clientToDelete) {
            // Send AJAX request to delete
            fetch(`/admin/clients/${clientToDelete}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideDeleteModal();

                if (data.status === 'success') {
                    // Show success message
                    window.showToast(data.message, 'success');

                    // Check if we're on client view page
                    const isClientViewPage = window.location.pathname.match(/\/admin\/clients\/\d+$/);

                    if (isClientViewPage) {
                        // If on client view page, redirect to clients list
                        setTimeout(() => {
                            window.location.href = '/admin/clients';
                        }, 1000);
                    } else {
                        // Remove row from table
                        const row = document.querySelector(`tr[data-client-id="${clientToDelete}"]`);
                        if (row) {
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 300);
                        }
                    }
                } else {
                    // Show error message
                    window.showToast(data.message || 'An error occurred while deleting the client.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideDeleteModal();
                window.showToast('An error occurred while deleting the client.', 'error');
            });
        }
    }

    // Initialize after page load
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to all delete buttons
        document.querySelectorAll('.delete-client-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const clientId = this.getAttribute('data-client-id');
                if (clientId) {
                    showDeleteConfirmModal(clientId);
                }
            });
        });
    });
</script>
