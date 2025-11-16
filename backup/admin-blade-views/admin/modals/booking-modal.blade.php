<!-- Booking Modal -->
<style>
    .modal-scroll-content::-webkit-scrollbar {
        width: 0px;
        background: transparent;
    }

    .modal-scroll-content {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
</style>

<div id="booking-modal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden backdrop-blur-sm flex items-center justify-center p-2">
    <div class="bg-white rounded-2xl w-full max-w-[98%] mx-auto shadow-2xl transform transition-all duration-300 overflow-hidden border border-gray-200">
        <div class="flex flex-col h-[95vh]">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-purple-600 p-6">
                <div class="flex justify-between items-center">
                    <h3 id="modal-title" class="text-3xl font-bold text-white drop-shadow-sm flex items-center">
                        <i class="fas fa-calendar-check mr-3 text-white bg-white bg-opacity-20 p-2 rounded-lg"></i>
                        <span>Add New Booking</span>
                    </h3>
                    <button class="text-white hover:text-gray-200 close-modal focus:outline-none bg-white bg-opacity-10 hover:bg-opacity-20 transition-all duration-200 p-2 rounded-lg" title="Close">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body with Scrolling -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50 modal-scroll-content">
                <form id="booking-form" method="POST" action="{{ route('admin.bookings.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="form_method" value="POST">
                    <input type="hidden" name="booking_id" id="booking_id">

                    <!-- Basic Info Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                            Basic Information
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Customer Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <select name="user_id" id="user_id" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none">
                                        <option value="">Select Customer</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Ground Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ground</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <select name="ground_id" id="ground_id" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none">
                                        <option value="">Select Ground</option>
                                        @foreach($grounds as $ground)
                                            <option value="{{ $ground->id }}">{{ $ground->name }} ({{ $ground->location }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Slot Selection (Improved) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Slots (Select Multiple)</label>
                                <div id="slot-selection-container" class="border border-gray-300 rounded-lg p-3 bg-white min-h-[100px] max-h-[200px] overflow-y-auto">
                                    <div id="slots-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        <div class="text-center text-gray-500 col-span-full py-2">Select a ground first</div>
                                    </div>
                                </div>
                                <input type="hidden" name="slot_ids[]" id="hidden-slot-ids">
                                <p class="text-xs text-gray-500 mt-1">Tap on slots to select multiple</p>
                            </div>

                            <!-- Booking Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Booking Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-info-circle text-gray-400"></i>
                                    </div>
                                    <select name="booking_status" id="booking_status" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none">
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>
                            Booking Details
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Booking Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Booking Date</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                    </div>
                                    <input type="date" name="booking_date" id="booking_date" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                </div>
                            </div>

                            <!-- Booking Time -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Booking Time</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-clock text-gray-400"></i>
                                    </div>
                                    <input type="time" name="booking_time" id="booking_time" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                </div>
                            </div>

                            <!-- Duration -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hourglass-half text-gray-400"></i>
                                    </div>
                                    <input type="number" name="duration" id="duration" min="1" max="24" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                </div>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-coins text-gray-400"></i>
                                    </div>
                                    <input type="number" name="amount" id="amount" step="0.01" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                </div>
                            </div>

                            <!-- Payment Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-credit-card text-gray-400"></i>
                                    </div>
                                    <select name="payment_status" id="payment_status" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none">
                                        <option value="pending">Pending</option>
                                        <option value="initiated">Initiated</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="failed">Failed</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <div class="relative">
                                <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                    <i class="fas fa-sticky-note text-gray-400"></i>
                                </div>
                                <textarea name="notes" id="notes" rows="3" class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="bg-white p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 font-medium flex items-center close-modal transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="submit" form="booking-form" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium flex items-center transition-all duration-200">
                    <i class="fas fa-save mr-2"></i> Save Booking
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Add moment.js for date handling -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
// Make sure jQuery is loaded before executing any code
$(document).ready(function() {
    // Set up CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Ground selection event
    $('#ground_id').on('change', function() {
        var groundId = $(this).val();
        console.log('Ground selected:', groundId);

        // Check if this is edit mode (has booking ID)
        var isEditMode = $('#booking_id').val() ? true : false;
        console.log('Is edit mode:', isEditMode);

        // Clear slot selection first
        $('#slots-grid').empty();

        // Keep the hidden slot IDs when changing ground in edit mode
        var existingSlotIds = $('#hidden-slot-ids').val();
        console.log('Existing slot IDs:', existingSlotIds);

        // Exit if no ground selected
        if (!groundId) {
            $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Select a ground first</div>');
            return;
        }

        // Get the current date (or from the date field if it's set)
        var selectedDate = $('#booking_date').val() || moment().format('YYYY-MM-DD');
        console.log('Selected date:', selectedDate);

        // If no date was selected, set today's date
        if (!$('#booking_date').val()) {
            $('#booking_date').val(selectedDate);
        }

        // Show loading message
        $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Loading slots...</div>');

        // Fetch available slots for this ground on the selected date
        $.ajax({
            url: '/admin/grounds/' + groundId + '/available-slots',
            type: 'GET',
            data: {
                date: selectedDate,
                booking_id: $('#booking_id').val() // Pass booking ID for edit mode
            },
            success: function(response) {
                console.log('Available slots response:', response);

                // Clear the grid
                $('#slots-grid').empty();

                // Get the price per slot for calculations
                if (response.ground && response.ground.price_per_slot) {
                    $('#booking-form').data('price', response.ground.price_per_slot);
                }

                // Check if we have slots
                if (response.status === 'success' && response.slots && response.slots.length > 0) {
                    // Get existing selected slots from hidden field
                    var selectedSlotIds = existingSlotIds ? existingSlotIds.split(',') : [];

                    // If this is an edit and we're changing ground, don't keep old selections
                    if (isEditMode && selectedSlotIds.length > 0) {
                        console.log('Edit mode with existing slots - checking if we need to reset');

                        // Check if the selected slots belong to the current ground
                        // This is a complex operation that would need a backend check
                        // For now, we'll keep the selections as they are
                    }

                    // Add each slot as a clickable button
                    $.each(response.slots, function(i, slot) {
                        // Check if this slot was previously selected
                        var isSelected = selectedSlotIds.includes(slot.id.toString());
                        var slotClass = 'slot-item p-2 border border-gray-200 rounded-md cursor-pointer transition-colors text-center';

                        if (isSelected) {
                            slotClass += ' selected bg-indigo-100 border-indigo-300';
                        } else {
                            slotClass += ' bg-white hover:bg-gray-50';
                        }

                        $('#slots-grid').append(
                            $('<div></div>')
                                .addClass(slotClass)
                                .attr('data-slot-id', slot.id)
                                .attr('data-slot-name', slot.slot_name)
                                .attr('data-slot-type', slot.slot_type)
                                .html('<span class="font-medium">' + slot.slot_name + '</span><br><span class="text-xs text-gray-500">' + slot.slot_type + '</span>')
                        );
                    });

                    // Calculate amount based on selected slots
                    if (selectedSlotIds.length > 0) {
                        var duration = $('#duration').val() || 1;
                        var price = response.ground.price_per_slot || 0;
                        var amount = duration * price * selectedSlotIds.length;
                        $('#amount').val(amount.toFixed(2));
                    } else {
                        $('#amount').val('0.00');
                    }

                    console.log('Loaded ' + response.slots.length + ' available slots');
                } else {
                    // No slots available
                    $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">No available slots for this date</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading slots:', error);
                $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Error loading slots</div>');
            }
        });
    });

    // Date change event - reload available slots when date changes
    $('#booking_date').on('change', function() {
        var selectedDate = $(this).val();
        var groundId = $('#ground_id').val();
        console.log('Date changed to:', selectedDate, 'for ground:', groundId);

        // Check if this is edit mode (has booking ID)
        var isEditMode = $('#booking_id').val() ? true : false;
        var bookingId = $('#booking_id').val();
        console.log('Is edit mode:', isEditMode, 'Booking ID:', bookingId);

        // Only reload slots if we have a ground selected
        if (groundId) {
            // Save current slot selections
            var existingSlotIds = $('#hidden-slot-ids').val();
            console.log('Existing slot IDs before date change:', existingSlotIds);

            // Clear current slots and show loading
            $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Loading slots...</div>');

            // Fetch available slots for the new date
            $.ajax({
                url: '/admin/grounds/' + groundId + '/available-slots',
                type: 'GET',
                data: {
                    date: selectedDate,
                    booking_id: bookingId // Pass booking ID for edit mode
                },
                success: function(response) {
                    console.log('Date change - available slots:', response);

                    // Clear the grid
                    $('#slots-grid').empty();

                    // Check if we have slots
                    if (response.status === 'success' && response.slots && response.slots.length > 0) {
                        // Get existing selected slots from hidden field
                        var selectedSlotIds = existingSlotIds ? existingSlotIds.split(',') : [];
                        console.log('Selected slot IDs after date change:', selectedSlotIds);

                        // If this is an edit and we're changing date, decide whether to keep selections
                        if (isEditMode && selectedSlotIds.length > 0) {
                            console.log('Edit mode date change - keeping existing selections if available');
                        }

                        // Add each slot as a clickable button
                        $.each(response.slots, function(i, slot) {
                            // Check if this slot was previously selected
                            var isSelected = selectedSlotIds.includes(slot.id.toString());
                            var slotClass = 'slot-item p-2 border border-gray-200 rounded-md cursor-pointer transition-colors text-center';

                            if (isSelected) {
                                slotClass += ' selected bg-indigo-100 border-indigo-300';
                                console.log('Date change: Slot', slot.id, 'is selected');
                            } else {
                                slotClass += ' bg-white hover:bg-gray-50';
                            }

                            $('#slots-grid').append(
                                $('<div></div>')
                                    .addClass(slotClass)
                                    .attr('data-slot-id', slot.id)
                                    .attr('data-slot-name', slot.slot_name)
                                    .attr('data-slot-type', slot.slot_type)
                                    .html('<span class="font-medium">' + slot.slot_name + '</span><br><span class="text-xs text-gray-500">' + slot.slot_type + '</span>')
                            );
                        });

                        // Calculate amount based on selected slots
                        if (selectedSlotIds.length > 0) {
                            var duration = $('#duration').val() || 1;
                            var price = response.ground.price_per_slot || 0;
                            var amount = duration * price * selectedSlotIds.length;
                            $('#amount').val(amount.toFixed(2));
                            console.log('Date change: Calculated amount:', amount.toFixed(2));
                        } else {
                            $('#amount').val('0.00');
                        }

                        console.log('Date change: Loaded', response.slots.length, 'slots with',
                                    $('.slot-item.selected').length, 'selected');
                    } else {
                        // No slots available
                        $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">No available slots for this date</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading slots for new date:', error, xhr.responseText);
                    $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Error loading slots</div>');
                }
            });
        } else {
            // Remind user to select a ground first
            $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Select a ground first</div>');
        }
    });

    // Slot selection event (click on grid items)
    $(document).on('click', '.slot-item', function() {
        var slotId = $(this).data('slot-id');
        var slotName = $(this).data('slot-name');

        console.log('Slot clicked:', slotId, slotName);

        // Toggle selection
        $(this).toggleClass('selected bg-indigo-100 border-indigo-300');
        var isSelected = $(this).hasClass('selected');

        console.log('Slot', slotId, isSelected ? 'selected' : 'deselected');

        // Update hidden input with all selected slot IDs
        var selectedSlotIds = [];
        $('.slot-item.selected').each(function() {
            selectedSlotIds.push($(this).data('slot-id'));
        });

        $('#hidden-slot-ids').val(selectedSlotIds.join(','));
        console.log('Updated selected slots:', selectedSlotIds);

        // Update amount
        var duration = $('#duration').val() || 1;
        var price = $('#booking-form').data('price') || 0;

        if (selectedSlotIds.length > 0) {
            var amount = duration * price * selectedSlotIds.length;
            $('#amount').val(amount.toFixed(2));
            console.log('Updated amount to:', amount.toFixed(2), 'based on', selectedSlotIds.length, 'slots');
        } else {
            $('#amount').val('0.00');
            console.log('Reset amount to 0.00 (no slots selected)');
        }
    });

    // Duration change event
    $('#duration').on('change', function() {
        var duration = $(this).val();
        var price = $('#booking-form').data('price') || 0;
        var selectedSlotIds = $('#hidden-slot-ids').val();

        if (selectedSlotIds) {
            var slotCount = selectedSlotIds.split(',').filter(Boolean).length;
            var amount = duration * price * slotCount;
            $('#amount').val(amount.toFixed(2));
        }
    });

    // Form submission
    $('#booking-form').on('submit', function(e) {
        e.preventDefault();

        console.log('Form submitted, processing data...');

        // Validate required fields
        var missingFields = [];

        if (!$('#user_id').val()) missingFields.push('Customer');
        if (!$('#ground_id').val()) missingFields.push('Ground');
        if (!$('#booking_date').val()) missingFields.push('Booking Date');
        if (!$('#booking_time').val()) missingFields.push('Booking Time');
        if (!$('#duration').val()) missingFields.push('Duration');

        // Check if slots are selected
        var selectedSlotIds = $('#hidden-slot-ids').val();
        if (!selectedSlotIds) {
            missingFields.push('Slots (at least one must be selected)');
        }

        // If validation fails, show error and return
        if (missingFields.length > 0) {
            showToast('Please fill in the following required fields: ' + missingFields.join(', '), 'error');
            console.error('Validation failed. Missing fields:', missingFields);
            return;
        }

        // Process selected slots into form data
        if (selectedSlotIds) {
            // Clear any existing slot_ids[] inputs first
            $('input[name="slot_ids[]"]').remove();

            // Add each selected slot ID as a hidden form element
            var slotIds = selectedSlotIds.split(',').filter(Boolean);
            console.log('Processing selected slots:', slotIds);

            $.each(slotIds, function(i, slotId) {
                $('#booking-form').append(
                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'slot_ids[]')
                        .val(slotId)
                );
            });

            console.log('Added', slotIds.length, 'slot ID fields to the form');
        } else {
            console.warn('No slots selected!');
            showToast('Please select at least one slot', 'error');
            return;
        }

        var formData = $(this).serialize();
        var url = $(this).attr('action');
        var method = $('#form_method').val() || 'POST';

        console.log('Submitting booking:', {
            url: url,
            method: method,
            slots: slotIds || []
        });

        // Show loading state
        var submitBtn = $('button[type="submit"]', this);
        var originalBtnText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving...');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                console.log('Form response:', response);

                // Reset button
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);

                if (response.status === 'success') {
                    showToast('Booking saved successfully!', 'success');
                    $('#booking-modal').hide();

                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    showToast('Error: ' + (response.message || 'Unknown error'), 'error');
                }
            },
            error: function(xhr) {
                console.error('Error saving booking:', xhr.responseText);

                // Reset button
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);

                showToast('Error saving booking. Please try again.', 'error');
            }
        });
    });

    // Close modal
    $('.close-modal').on('click', function() {
        $('#booking-modal').hide();
    });
});

// Function to edit a booking
function editBooking(id) {
    console.log('Global editBooking called with ID:', id);

    // Prevent multiple simultaneous calls
    if (window.editBookingInProgress) {
        console.log('Edit booking already in progress, ignoring duplicate call');
        return;
    }

    window.editBookingInProgress = true;

    // Show loading state in modal
    $('#modal-title span').text('Loading Booking...');
    if ($('#booking-initial').length) {
        $('#booking-initial').html('<i class="fas fa-spinner fa-spin"></i>');
    }

    // Show modal immediately to indicate loading
    $('#booking-modal').show();

    // Reset form
    $('#booking-form')[0].reset();

    // Update form for edit mode
    $('#booking_id').val(id);
    $('#form_method').val('PUT');
    $('#booking-form').attr('action', '/admin/bookings/' + id);

    // Fetch booking data
    $.ajax({
        url: '/admin/bookings/' + id + '/edit',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Edit booking response:', response);

            if (response.status === 'success') {
                const booking = response.booking;
                const groundId = response.ground_id;
                const slotIds = response.slot_ids || [];

                console.log('Editing booking details:', booking);

                // Update modal title
                $('#modal-title span').text('Edit Booking');

                // Format the booking date for the date input (YYYY-MM-DD)
                let formattedDate = booking.booking_date;
                if (booking.booking_date && booking.booking_date.includes('T')) {
                    formattedDate = booking.booking_date.split('T')[0];
                }

                // Fill basic form fields
                $('#user_id').val(booking.user_id);
                $('#booking_date').val(formattedDate);

                // Format the booking time if needed
                if (booking.booking_time && booking.booking_time.includes(' - ')) {
                    // If it's in format "03:00 - 05:00", just use the start time
                    const startTime = booking.booking_time.split(' - ')[0];
                    $('#booking_time').val(startTime);
                } else {
                    $('#booking_time').val(booking.booking_time);
                }

                $('#duration').val(booking.duration);
                $('#amount').val(booking.amount);
                $('#booking_status').val(booking.booking_status);
                $('#payment_status').val(booking.payment_status || 'pending');
                $('#notes').val(booking.notes || '');

                // Set ground ID
                console.log('Setting ground_id select to:', groundId);
                $('#ground_id').val(groundId);

                // Store slot IDs for later use
                console.log('Setting hidden slot IDs:', slotIds);
                $('#hidden-slot-ids').val(slotIds.join(','));

                // Load slots directly instead of relying on change event
                setTimeout(function() {
                    loadSlotsForBooking(groundId, booking.booking_date, id, slotIds);
                }, 100);
            } else {
                console.error('Error loading booking:', response.message || 'Unknown error');
                showToast('Error loading booking data', 'error');
                $('#slots-grid').html('<div class="text-center text-red-500 col-span-full py-2">Error loading slot data</div>');
                $('#modal-title span').text('Error Loading Booking');
                if ($('#booking-initial').length) {
                    $('#booking-initial').text('!');
                }
                resetEditState();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error loading booking:', error, xhr.responseText);
            showToast('Error loading booking data', 'error');
            $('#slots-grid').html('<div class="text-center text-red-500 col-span-full py-2">Error loading slot data</div>');
            $('#modal-title span').text('Error Loading Booking');
            if ($('#booking-initial').length) {
                $('#booking-initial').text('!');
            }
            resetEditState();
        }
    });
}

// Add a resetEditState function
function resetEditState() {
    window.editBookingInProgress = false;
    console.log('Reset edit booking state');
}

// Function to load slots for a booking directly without relying on change event
function loadSlotsForBooking(groundId, bookingDate, bookingId, selectedSlotIds) {
    if (!groundId || !bookingDate) {
        console.error('Missing required data for loading slots:', { groundId, bookingDate });
        $('#slots-grid').html('<div class="text-center text-red-500 col-span-full py-2">Missing ground or date data</div>');
        resetEditState();
        return;
    }

    // Format the date if it's in ISO format
    let formattedDate = bookingDate;
    if (bookingDate && bookingDate.includes('T')) {
        formattedDate = bookingDate.split('T')[0];
    }

    console.log('Loading slots directly for booking:', {
        bookingId: bookingId,
        groundId: groundId,
        date: formattedDate,
        selectedSlots: selectedSlotIds
    });

    // Show loading message
    $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Loading slots...</div>');

    // Fetch available slots
    $.ajax({
        url: '/admin/grounds/' + groundId + '/available-slots',
        type: 'GET',
        data: {
            date: formattedDate,
            booking_id: bookingId
        },
        success: function(response) {
            console.log('Slots loaded directly:', response);

            // Reset edit state flag
            resetEditState();

            // Clear the grid
            $('#slots-grid').empty();

            // Get the price per slot for calculations
            if (response.ground && response.ground.price_per_slot) {
                $('#booking-form').data('price', response.ground.price_per_slot);
                console.log('Set price per slot:', response.ground.price_per_slot);
            }

            // Check if we have slots
            if (response.status === 'success' && response.slots && response.slots.length > 0) {
                console.log('Processing', response.slots.length, 'slots with', selectedSlotIds.length, 'selected');

                // Add each slot as a clickable button
                $.each(response.slots, function(i, slot) {
                    // Check if this slot was previously selected
                    var isSelected = selectedSlotIds.includes(slot.id.toString()) || selectedSlotIds.includes(slot.id);
                    var slotClass = 'slot-item p-2 border border-gray-200 rounded-md cursor-pointer transition-colors text-center';

                    if (isSelected) {
                        slotClass += ' selected bg-indigo-100 border-indigo-300';
                        console.log('Slot', slot.id, '(', slot.slot_name, ') is selected');
                    } else {
                        slotClass += ' bg-white hover:bg-gray-50';
                    }

                    $('#slots-grid').append(
                        $('<div></div>')
                            .addClass(slotClass)
                            .attr('data-slot-id', slot.id)
                            .attr('data-slot-name', slot.slot_name)
                            .attr('data-slot-type', slot.slot_type)
                            .html('<span class="font-medium">' + slot.slot_name + '</span><br><span class="text-xs text-gray-500">' + slot.slot_type + '</span>')
                    );
                });

                // Set the hidden slots field again
                $('#hidden-slot-ids').val(selectedSlotIds.join(','));

                // Calculate amount based on selected slots
                if (selectedSlotIds.length > 0) {
                    var duration = $('#duration').val() || 1;
                    var price = response.ground.price_per_slot || 0;
                    var amount = duration * price * selectedSlotIds.length;
                    $('#amount').val(amount.toFixed(2));
                    console.log('Calculated amount:', amount.toFixed(2), 'based on', selectedSlotIds.length, 'slots');
                }

                console.log('Loaded ' + response.slots.length + ' slots, selected: ' + selectedSlotIds.length);
            } else {
                // No slots available
                console.warn('No slots available for date', formattedDate);
                $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">No available slots for this date</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error loading slots:', error, xhr.responseText);
            $('#slots-grid').html('<div class="text-center text-red-500 col-span-full py-2">Error loading slots</div>');
            resetEditState();
        }
    });
}

function addBooking() {
    // Reset form
    $('#booking-form')[0].reset();

    // Update form for add mode
    $('#modal-title span').text('Add New Booking');
    $('#booking_id').val('');
    $('#form_method').val('POST');
    $('#booking-form').attr('action', '/admin/bookings');

    // Reset and clear slots grid
    $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Select a ground first</div>');
    $('#hidden-slot-ids').val('');

    // Set today's date by default
    $('#booking_date').val(moment().format('YYYY-MM-DD'));

    // Show modal
    $('#booking-modal').show();
}

// Make functions global
window.editBooking = editBooking;
window.addBooking = addBooking;

// Helper function to show toast notifications
function showToast(message, type = 'success') {
    // Try to use the page-handler.js showToast function first
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
        return;
    }

    // Fallback to the toast in booking-view.blade.php
    const toast = document.getElementById('toast-notification');
    if (toast) {
        const toastMessage = document.getElementById('toast-message');
        const toastIcon = document.getElementById('toast-icon');

        // Set message
        if (toastMessage) toastMessage.textContent = message;

        // Set icon and colors based on type
        if (toastIcon) {
            if (type === 'success') {
                toastIcon.className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg';
                toastIcon.innerHTML = '<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/></svg>';
            } else if (type === 'error') {
                toastIcon.className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg';
                toastIcon.innerHTML = '<svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20"><path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.5 11.5a1 1 0 0 1-2 0v-4a1 1 0 0 1 2 0Zm-3.5-1a1 1 0 1 1 0 2 1 1 0 0 1 0-2Z"/></svg>';
            }
        }

        // Show toast
        toast.classList.remove('translate-x-full');
        toast.classList.add('translate-x-0');

        // Auto hide after 5 seconds
        setTimeout(function() {
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
        }, 5000);

        return;
    }

    // Last resort fallback
    alert(message);
}
</script>
