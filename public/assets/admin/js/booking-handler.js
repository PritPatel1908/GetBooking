document.addEventListener('DOMContentLoaded', function() {
    // Check for message in localStorage (for showing toast after redirect)
    if (localStorage.getItem('bookingActionMessage')) {
        window.showToast(
            localStorage.getItem('bookingActionMessage'),
            localStorage.getItem('bookingActionType') || 'success'
        );

        // Clear the message from localStorage
        localStorage.removeItem('bookingActionMessage');
        localStorage.removeItem('bookingActionType');
    }

    // Modal elements - with null checks to prevent errors if they don't exist
    const modal = document.getElementById('booking-modal');
    const closeButtons = document.querySelectorAll('.close-modal');
    const form = document.getElementById('booking-form');
    const saveButton = document.getElementById('save-booking');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');
    const bookingInitial = document.getElementById('booking-initial');

    // Exit if critical elements don't exist
    if (!modal || !form) {
        console.warn('Booking modal or form not found in the DOM.');
        return;
    }

    // Add booking button
    const addBookingBtn = document.querySelector('.add-booking-btn');
    if (addBookingBtn) {
        addBookingBtn.addEventListener('click', function() {
            try {
                openAddBookingModal();
            } catch (error) {
                console.error('Error opening booking modal:', error);
                // Fallback to simple modal open if the function fails
                if (modal) {
                    modal.classList.remove('hidden');
                }
            }
        });
    }

    // Close modal handlers
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            closeBookingModal();
        });
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeBookingModal();
        }
    });

    // Close modal on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeBookingModal();
        }
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveBooking();
    });

    // Edit booking buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-booking-btn')) {
            const bookingId = e.target.closest('.edit-booking-btn').getAttribute('data-booking-id');
            editBooking(bookingId);
        }
    });

    // Delete booking buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-booking-btn')) {
            const bookingId = e.target.closest('.delete-booking-btn').getAttribute('data-booking-id');
            showDeleteConfirmation(bookingId);
        }
    });

    // View booking buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-booking-btn')) {
            const bookingId = e.target.closest('.view-booking-btn').getAttribute('data-booking-id');
            viewBooking(bookingId);
        }
    });

    // Ground selection change handler
    const groundSelect = document.getElementById('ground_id');
    if (groundSelect) {
        groundSelect.addEventListener('change', function() {
            updateBookingAmount();
        });
    }

    // Duration change handler
    const durationInput = document.getElementById('duration');
    if (durationInput) {
        durationInput.addEventListener('change', function() {
            updateBookingAmount();
        });
    }

    function openAddBookingModal() {
        // Update modal title - with null checks
        if (modalTitle) modalTitle.textContent = 'Add New Booking';
        if (modalSubtitle) modalSubtitle.textContent = 'Create a new booking for a customer';
        if (bookingInitial) bookingInitial.textContent = 'B';

        // Reset form
        form.reset();
        form.querySelector('input[name="booking_id"]').value = '';

        // Show modal with animation
        if (window.showBookingModal) {
            window.showBookingModal();
        } else {
            modal.classList.remove('hidden');
        }
    }

    function openEditBookingModal(booking) {
        // Update modal title
        if (modalTitle) modalTitle.textContent = 'Edit Booking';
        if (modalSubtitle) modalSubtitle.textContent = 'Update booking details';
        if (bookingInitial) bookingInitial.textContent = booking.booking_sku ? booking.booking_sku.charAt(0).toUpperCase() : 'B';

        // Fill form with booking data
        form.querySelector('input[name="booking_id"]').value = booking.id;
        form.querySelector('select[name="user_id"]').value = booking.user_id;
        form.querySelector('select[name="ground_id"]').value = booking.ground_id;
        form.querySelector('input[name="booking_date"]').value = booking.booking_date;
        form.querySelector('input[name="booking_time"]').value = booking.booking_time;
        form.querySelector('input[name="duration"]').value = booking.duration;
        form.querySelector('input[name="amount"]').value = booking.amount;
        form.querySelector('select[name="booking_status"]').value = booking.booking_status;
        form.querySelector('select[name="payment_status"]').value = booking.payment_status || 'pending';
        form.querySelector('textarea[name="notes"]').value = booking.notes || '';

        // Get slot IDs from booking details
        fetch(`/admin/bookings/${booking.id}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Get ground ID and slot IDs
                const groundId = data.ground_id;
                const slotIds = data.slot_ids;

                // Load slots for ground
                if (groundId) {
                    // Trigger ground selection to load slots
                    form.querySelector('select[name="ground_id"]').dispatchEvent(new Event('change'));

                    // Set hidden slot IDs field
                    const hiddenSlotIds = form.querySelector('#hidden-slot-ids');
                    if (hiddenSlotIds) {
                        hiddenSlotIds.value = slotIds.join(',');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading slot details:', error);
        });

        // Show modal with animation
        if (window.showBookingModal) {
            window.showBookingModal();
        } else {
            modal.classList.remove('hidden');
        }
    }

    function closeBookingModal() {
        if (window.closeModalWithAnimation) {
            window.closeModalWithAnimation(modal);
        } else {
            modal.classList.add('hidden');
        }
        form.reset();
    }

    function saveBooking() {
        const formData = new FormData(form);
        const bookingId = formData.get('booking_id');
        const url = bookingId ? `/admin/bookings/${bookingId}` : '/admin/bookings/create';
        const method = bookingId ? 'PUT' : 'POST';

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
        submitBtn.disabled = true;

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;

            if (data.status === 'success') {
                // Close modal
                closeBookingModal();

                // Show success message
                window.showToast(data.message || 'Booking saved successfully!', 'success');

                // Reload the table
                reloadBookingTable();

                // Reset form
                form.reset();
            } else if (data.status === 'error') {
                // Display validation errors
                displayValidationErrors(data.errors);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            window.showToast('An error occurred while saving the booking.', 'error');
        });
    }

    function editBooking(bookingId) {
        console.log('booking-handler.js editBooking called with ID:', bookingId);

        // Check if we're on a booking view page or if the global function should be used
        if (typeof window.editBooking === 'function') {
            console.log('Using global editBooking function');
            window.editBooking(bookingId);
            return;
        }

        console.log('Using local editBooking implementation');

        // Regular edit functionality for bookings list page
        // Show loading state
        const editBtn = document.querySelector(`.edit-booking-btn[data-booking-id="${bookingId}"]`);
        if (!editBtn) {
            console.error('Edit button not found for booking ID:', bookingId);
            window.showToast('Error finding edit button. Please refresh and try again.', 'error');
            return;
        }

        const originalContent = editBtn.innerHTML;
        editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        editBtn.disabled = true;

        fetch(`/admin/bookings/${bookingId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            editBtn.innerHTML = originalContent;
            editBtn.disabled = false;

            if (data.status === 'success') {
                openEditBookingModal(data.booking);
            } else {
                window.showToast(data.message || 'Failed to fetch booking data.', 'error');
            }
        })
        .catch(error => {
            // Reset button state
            editBtn.innerHTML = originalContent;
            editBtn.disabled = false;

            console.error('Error:', error);
            window.showToast('An error occurred while fetching booking data.', 'error');
        });
    }

    function showDeleteConfirmation(bookingId) {
        const deletePopup = document.getElementById('delete-popup');
        const confirmDelete = document.getElementById('confirm-delete');
        const cancelDelete = document.getElementById('cancel-delete');

        // Show delete confirmation popup
        deletePopup.classList.remove('hidden');

        // Handle confirm delete
        confirmDelete.onclick = function() {
            deleteBooking(bookingId);
            deletePopup.classList.add('hidden');
        };

        // Handle cancel delete
        cancelDelete.onclick = function() {
            deletePopup.classList.add('hidden');
        };
    }

    function deleteBooking(bookingId) {
        fetch(`/admin/bookings/${bookingId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.showToast(data.message || 'Booking deleted successfully!', 'success');
                reloadBookingTable();
            } else {
                window.showToast(data.message || 'Error deleting booking.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showToast('An error occurred while deleting the booking.', 'error');
        });
    }

    function viewBooking(bookingId) {
        window.location.href = `/admin/bookings/${bookingId}`;
    }

    function updateBookingAmount() {
        const groundId = document.getElementById('ground_id').value;
        const duration = document.getElementById('duration').value;

        if (groundId && duration) {
            fetch(`/admin/grounds/${groundId}/price`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const amount = data.price_per_hour * duration;
                    document.getElementById('amount').value = amount.toFixed(2);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }

    function reloadBookingTable() {
        fetch('/admin/bookings/pagination', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                const tbody = document.querySelector('table tbody');
                tbody.innerHTML = data.html;

                // Update pagination info
                const paginationInfo = document.querySelector('.text-sm.text-gray-600');
                if (paginationInfo && data.pagination) {
                    paginationInfo.textContent = `Showing ${data.pagination.from || 0} to ${data.pagination.to || 0} of ${data.pagination.total || 0} entries`;
                }

                // Reattach event listeners
                attachTableEventListeners();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showToast('An error occurred while reloading the table.', 'error');
        });
    }

    function attachTableEventListeners() {
        // Attach event listeners to the new rows
        document.querySelectorAll('.edit-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                editBooking(bookingId);
            });
        });

        document.querySelectorAll('.delete-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                showDeleteConfirmation(bookingId);
            });
        });

        document.querySelectorAll('.view-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                viewBooking(bookingId);
            });
        });
    }

    function displayValidationErrors(errors) {
        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));

        // Display new errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('border-red-500');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
            }
        });

        window.showToast('Please correct the errors in the form.', 'error');
    }

    // Initialize table event listeners
    attachTableEventListeners();
});
