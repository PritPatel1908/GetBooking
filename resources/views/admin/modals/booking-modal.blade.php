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
                                        <option value="completed">Completed</option>
                                        <option value="failed">Failed</option>
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

        // Clear slot selection first
        $('#slots-grid').empty();
        $('#hidden-slot-ids').val('');
        $('#amount').val('0.00');

        // Exit if no ground selected
        if (!groundId) {
            $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Select a ground first</div>');
            return;
        }

        // Get the current date (or from the date field if it's set)
        var selectedDate = $('#booking_date').val() || moment().format('YYYY-MM-DD');

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
                date: selectedDate
            },
            success: function(response) {
                console.log('Available slots response:', response);

                // Clear the grid
                $('#slots-grid').empty();

                // Get the price per hour for calculations
                if (response.ground && response.ground.price_per_hour) {
                    $('#booking-form').data('price', response.ground.price_per_hour);
                }

                // Check if we have slots
                if (response.status === 'success' && response.slots && response.slots.length > 0) {
                    // Add each slot as a clickable button
                    $.each(response.slots, function(i, slot) {
                        $('#slots-grid').append(
                            $('<div></div>')
                                .addClass('slot-item p-2 border border-gray-200 rounded-md bg-white hover:bg-gray-50 cursor-pointer transition-colors text-center')
                                .attr('data-slot-id', slot.id)
                                .attr('data-slot-name', slot.slot_name)
                                .attr('data-slot-type', slot.slot_type)
                                .html('<span class="font-medium">' + slot.slot_name + '</span><br><span class="text-xs text-gray-500">' + slot.slot_type + '</span>')
                        );
                    });

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

        // Only reload slots if we have a ground selected
        if (groundId) {
            // Clear current slots and show loading
            $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Loading slots...</div>');
            $('#hidden-slot-ids').val('');

            // Fetch available slots for the new date
            $.ajax({
                url: '/admin/grounds/' + groundId + '/available-slots',
                type: 'GET',
                data: {
                    date: selectedDate
                },
                success: function(response) {
                    console.log('Date change - available slots:', response);

                    // Clear the grid
                    $('#slots-grid').empty();

                    // Check if we have slots
                    if (response.status === 'success' && response.slots && response.slots.length > 0) {
                        // Add each slot as a clickable button
                        $.each(response.slots, function(i, slot) {
                            $('#slots-grid').append(
                                $('<div></div>')
                                    .addClass('slot-item p-2 border border-gray-200 rounded-md bg-white hover:bg-gray-50 cursor-pointer transition-colors text-center')
                                    .attr('data-slot-id', slot.id)
                                    .attr('data-slot-name', slot.slot_name)
                                    .attr('data-slot-type', slot.slot_type)
                                    .html('<span class="font-medium">' + slot.slot_name + '</span><br><span class="text-xs text-gray-500">' + slot.slot_type + '</span>')
                            );
                        });
                    } else {
                        // No slots available
                        $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">No available slots for this date</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading slots for new date:', error);
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
        $(this).toggleClass('selected bg-indigo-100 border-indigo-300');

        // Update hidden input with all selected slot IDs
        var selectedSlotIds = [];
        $('.slot-item.selected').each(function() {
            selectedSlotIds.push($(this).data('slot-id'));
        });

        $('#hidden-slot-ids').val(selectedSlotIds.join(','));
        console.log('Selected slots:', selectedSlotIds);

        // Update amount
        if (selectedSlotIds.length > 0) {
            var duration = $('#duration').val() || 1;
            var price = $('#booking-form').data('price') || 0;
            var amount = duration * price * selectedSlotIds.length;

            $('#amount').val(amount.toFixed(2));
        } else {
            $('#amount').val('0.00');
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

        // Process selected slots into form data
        var selectedSlotIds = $('#hidden-slot-ids').val();
        if (selectedSlotIds) {
            // Clear any existing slot_ids[] inputs first
            $('input[name="slot_ids[]"]').remove();

            // Add each selected slot ID as a hidden form element
            var slotIds = selectedSlotIds.split(',').filter(Boolean);
            $.each(slotIds, function(i, slotId) {
                $('#booking-form').append(
                    $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'slot_ids[]')
                        .val(slotId)
                );
            });
        }

        var formData = $(this).serialize();
        var url = $(this).attr('action');
        var method = $('#form_method').val() || 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(response) {
                console.log('Form response:', response);

                if (response.status === 'success') {
                    alert('Booking saved successfully!');
                    $('#booking-modal').hide();

                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert('Error: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                console.error('Error saving booking:', xhr.responseText);
                alert('Error saving booking. Please try again.');
            }
        });
    });

    // Close modal
    $('.close-modal').on('click', function() {
        $('#booking-modal').hide();
    });
});

// Global functions for edit and add
function editBooking(id) {
    // Reset form
    $('#booking-form')[0].reset();

    // Clear slot selections
    $('#slots-grid').empty();
    $('#hidden-slot-ids').val('');

    // Update form for edit mode
    $('#modal-title span').text('Edit Booking');
    $('#booking_id').val(id);
    $('#form_method').val('PUT');
    $('#booking-form').attr('action', '/admin/bookings/' + id);

    // Show loading message in slots grid
    $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Loading...</div>');

    // Load booking data
    $.ajax({
        url: '/admin/bookings/' + id + '/edit',
        type: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                var booking = response.booking;
                var groundId = response.ground_id;
                var slotIds = response.slot_ids;

                console.log('Editing booking:', booking);
                console.log('Ground ID:', groundId);
                console.log('Selected slot IDs:', slotIds);

                // Fill basic form fields
                $('#user_id').val(booking.user_id);
                $('#booking_date').val(booking.booking_date);
                $('#booking_time').val(booking.booking_time);
                $('#duration').val(booking.duration);
                $('#amount').val(booking.amount);
                $('#booking_status').val(booking.booking_status);
                $('#payment_status').val(booking.payment_status || 'pending');
                $('#notes').val(booking.notes || '');

                // Set ground ID
                $('#ground_id').val(groundId);

                // Get available slots for this date, including this booking's slots
                $.ajax({
                    url: '/admin/grounds/' + groundId + '/available-slots',
                    type: 'GET',
                    data: {
                        date: booking.booking_date,
                        booking_id: id // Important: Pass booking ID to exclude its slots from "booked" check
                    },
                    success: function(slotsResponse) {
                        console.log('Available slots for editing:', slotsResponse);

                        // Clear the grid
                        $('#slots-grid').empty();

                        if (slotsResponse.status === 'success' && slotsResponse.slots && slotsResponse.slots.length > 0) {
                            // Save the ground price for calculations
                            if (slotsResponse.ground && slotsResponse.ground.price_per_hour) {
                                $('#booking-form').data('price', slotsResponse.ground.price_per_hour);
                            }

                            // Add each slot as a clickable button
                            $.each(slotsResponse.slots, function(i, slot) {
                                var isSelected = slotIds.includes(slot.id.toString()) || slotIds.includes(slot.id);

                                $('#slots-grid').append(
                                    $('<div></div>')
                                        .addClass('slot-item p-2 border border-gray-200 rounded-md cursor-pointer transition-colors text-center')
                                        .addClass(isSelected ? 'selected bg-indigo-100 border-indigo-300' : 'bg-white hover:bg-gray-50')
                                        .attr('data-slot-id', slot.id)
                                        .attr('data-slot-name', slot.slot_name)
                                        .attr('data-slot-type', slot.slot_type)
                                        .html('<span class="font-medium">' + slot.slot_name + '</span><br><span class="text-xs text-gray-500">' + slot.slot_type + '</span>')
                                );
                            });

                            // Set the hidden slots field
                            $('#hidden-slot-ids').val(slotIds.join(','));
                            console.log('Set selected slots:', slotIds);
                        } else {
                            $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">No available slots</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading slots for editing:', error);
                        $('#slots-grid').html('<div class="text-center text-gray-500 col-span-full py-2">Error loading slots</div>');
                    }
                });
            } else {
                console.error('Error loading booking:', response.message || 'Unknown error');
                alert('Error loading booking. Please try again.');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading booking:', error);
            alert('Error loading booking data. Please try again.');
        }
    });

    // Show modal
    $('#booking-modal').show();
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
</script>
