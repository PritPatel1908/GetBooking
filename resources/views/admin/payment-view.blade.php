@extends('layouts.admin')

@section('title', 'Payment Details')
@section('content')
    <main class="flex-1 overflow-y-auto p-4 page-transition">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-xl font-semibold">Payment Details</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.payments') }}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center space-x-2 btn-hover-effect">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Payments</span>
                    </a>
                    <button onclick="updatePaymentStatus({{ $payment->id }})"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 btn-hover-effect">
                        <i class="fas fa-edit"></i>
                        <span>Update Status</span>
                    </button>
                    @if ($payment->payment_status === 'completed' && $payment->transaction_id)
                        <button
                            onclick="initiateRefund({{ $payment->id }}, '{{ $payment->transaction_id }}', {{ $payment->amount }})"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 btn-hover-effect">
                            <i class="fas fa-undo"></i>
                            <span>Refund Payment</span>
                        </button>
                    @endif
                </div>
            </div>

            <div class="p-6">
                <!-- Payment Status Badge -->
                <div class="mb-6 flex items-center">
                    <span class="text-lg font-medium mr-3">Status:</span>
                    <span
                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        {{ $payment->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $payment->payment_status === 'initiated' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $payment->payment_status === 'processing' ? 'bg-indigo-100 text-indigo-800' : '' }}
                        {{ $payment->payment_status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $payment->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $payment->payment_status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $payment->payment_status === 'refunded' ? 'bg-purple-100 text-purple-800' : '' }}">
                        {{ ucfirst($payment->payment_status) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Payment Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4 border-b pb-2">Payment Information</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transaction ID:</span>
                                <span class="font-medium">{{ $payment->transaction_id ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium">{{ $payment->date->format('d M Y, h:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Amount:</span>
                                <span class="font-medium">₹{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium">{{ ucfirst($payment->payment_method ?? 'N/A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Type:</span>
                                <span class="font-medium">{{ ucfirst($payment->payment_type ?? 'N/A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4 border-b pb-2">Customer Information</h3>
                        <div class="space-y-3">
                            @if ($payment->user)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium">{{ $payment->user->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium">{{ $payment->user->email }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="font-medium">{{ $payment->user->phone ?? 'N/A' }}</span>
                                </div>
                            @else
                                <div class="text-center text-gray-500">
                                    No customer information available
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Booking Information -->
                    @if ($payment->booking)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium mb-4 border-b pb-2">Booking Information</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Booking ID:</span>
                                    <span class="font-medium">{{ $payment->booking->booking_sku }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ground:</span>
                                    <span class="font-medium">{{ $payment->booking->ground->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Booking Date:</span>
                                    <span class="font-medium">{{ $payment->booking->booking_date->format('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Booking Time:</span>
                                    <span class="font-medium">{{ $payment->booking->booking_time }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="font-medium">{{ ucfirst($payment->booking->booking_status) }}</span>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('admin.bookings.show', $payment->booking->id) }}"
                                        class="text-indigo-600 hover:text-indigo-800">
                                        View Booking Details <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Response -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4 border-b pb-2">Payment Response</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Response Code:</span>
                                <span class="font-medium">{{ $payment->payment_response_code ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Response Message:</span>
                                <span class="font-medium">{{ $payment->payment_response_message ?? 'N/A' }}</span>
                            </div>
                            @if ($payment->payment_response_data)
                                <div>
                                    <span class="text-gray-600 block mb-1">Response Data:</span>
                                    <div class="bg-gray-100 p-3 rounded text-sm overflow-x-auto">
                                        <pre>{{ $payment->payment_response_data }}</pre>
                                    </div>
                                </div>
                            @endif
                            @if ($payment->payment_response_data_json)
                                <div>
                                    <span class="text-gray-600 block mb-1">Response JSON:</span>
                                    <div class="bg-gray-100 p-3 rounded text-sm overflow-x-auto">
                                        <pre>{{ json_encode(json_decode($payment->payment_response_data_json), JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Timeline -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium mb-4">Payment Timeline</h3>
                    <div class="border-l-2 border-gray-200 pl-4 ml-3">
                        <div class="relative mb-6">
                            <div class="absolute -left-6 mt-1 w-4 h-4 rounded-full bg-indigo-500"></div>
                            <div>
                                <p class="font-medium">Payment Created</p>
                                <p class="text-sm text-gray-500">{{ $payment->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                        @if ($payment->updated_at->gt($payment->created_at))
                            <div class="relative mb-6">
                                <div class="absolute -left-6 mt-1 w-4 h-4 rounded-full bg-indigo-500"></div>
                                <div>
                                    <p class="font-medium">Payment Updated</p>
                                    <p class="text-sm text-gray-500">{{ $payment->updated_at->format('d M Y, h:i A') }}</p>
                                    <p class="text-sm text-gray-600">Status changed to
                                        {{ ucfirst($payment->payment_status) }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('modals')
    <!-- Payment Status Update Modal -->
    <div id="paymentStatusModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black opacity-50 transition-opacity"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl z-10">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">Update Payment Status</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 close-modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="updatePaymentStatusForm">
                    <div class="p-4">
                        <input type="hidden" id="payment_id" name="payment_id" value="{{ $payment->id }}">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                            <select id="payment_status" name="payment_status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="pending" {{ $payment->payment_status === 'pending' ? 'selected' : '' }}>
                                    Pending</option>
                                <option value="initiated"
                                    {{ $payment->payment_status === 'initiated' ? 'selected' : '' }}>Initiated</option>
                                <option value="processing"
                                    {{ $payment->payment_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed"
                                    {{ $payment->payment_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ $payment->payment_status === 'failed' ? 'selected' : '' }}>
                                    Failed</option>
                                <option value="cancelled"
                                    {{ $payment->payment_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="refunded" {{ $payment->payment_status === 'refunded' ? 'selected' : '' }}>
                                    Refunded</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end p-4 border-t">
                        <button type="button" class="px-4 py-2 text-gray-600 close-modal mr-2">Cancel</button>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Update
                            Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Refund Confirmation Modal -->
    <div id="refundModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black opacity-50 transition-opacity"></div>
            <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl z-10">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">Confirm Refund</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500 close-refund-modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="refundPaymentForm">
                    <div class="p-4">
                        <input type="hidden" id="refund_payment_id" name="refund_payment_id"
                            value="{{ $payment->id }}">
                        <input type="hidden" id="refund_transaction_id" name="refund_transaction_id"
                            value="{{ $payment->transaction_id }}">
                        <input type="hidden" id="refund_amount" name="refund_amount" value="{{ $payment->amount }}">

                        <div class="mb-4">
                            <p class="text-gray-700 mb-2">Are you sure you want to refund this payment?</p>
                            <p class="text-gray-700 mb-2">Transaction ID: <span id="display_transaction_id"
                                    class="font-medium">{{ $payment->transaction_id }}</span></p>
                            <p class="text-gray-700">Amount: ₹<span id="display_amount"
                                    class="font-medium">{{ number_format($payment->amount, 2) }}</span></p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Refund Reason</label>
                            <select id="refund_reason" name="refund_reason"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="customer_request">Customer Request</option>
                                <option value="duplicate_payment">Duplicate Payment</option>
                                <option value="fraudulent_transaction">Fraudulent Transaction</option>
                                <option value="service_unavailable">Service Unavailable</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div id="other_reason_container" class="mb-4 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Specify Reason</label>
                            <textarea id="other_reason" name="other_reason" rows="2"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Please specify the refund reason"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end p-4 border-t">
                        <button type="button" class="px-4 py-2 text-gray-600 close-refund-modal mr-2">Cancel</button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Process
                            Refund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Function to show payment status modal
        function updatePaymentStatus(paymentId) {
            document.getElementById('payment_id').value = paymentId;
            document.getElementById('paymentStatusModal').classList.remove('hidden');
        }

        // Close modal when clicking close button or cancel
        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('paymentStatusModal').classList.add('hidden');
            });
        });

        // Handle form submission
        document.getElementById('updatePaymentStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const paymentId = document.getElementById('payment_id').value;
            const paymentStatus = document.getElementById('payment_status').value;

            // Send AJAX request to update payment status
            fetch(`/admin/payments/${paymentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        payment_status: paymentStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Close modal and reload page to show updated data
                        document.getElementById('paymentStatusModal').classList.add('hidden');
                        window.location.reload();
                    } else {
                        alert('Failed to update payment status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating payment status');
                });
        });

        // Function to show refund modal
        function initiateRefund(paymentId, transactionId, amount) {
            document.getElementById('refund_payment_id').value = paymentId;
            document.getElementById('refund_transaction_id').value = transactionId;
            document.getElementById('refund_amount').value = amount;
            document.getElementById('display_transaction_id').textContent = transactionId;
            document.getElementById('display_amount').textContent = amount.toFixed(2);
            document.getElementById('refundModal').classList.remove('hidden');
        }

        // Close refund modal when clicking close button or cancel
        document.querySelectorAll('.close-refund-modal').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('refundModal').classList.add('hidden');
            });
        });

        // Show/hide other reason textarea based on selection
        document.getElementById('refund_reason').addEventListener('change', function() {
            const otherReasonContainer = document.getElementById('other_reason_container');
            if (this.value === 'other') {
                otherReasonContainer.classList.remove('hidden');
            } else {
                otherReasonContainer.classList.add('hidden');
            }
        });

        // Handle refund form submission
        document.getElementById('refundPaymentForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const paymentId = document.getElementById('refund_payment_id').value;
            const transactionId = document.getElementById('refund_transaction_id').value;
            const amount = document.getElementById('refund_amount').value;
            const reason = document.getElementById('refund_reason').value;
            const otherReason = document.getElementById('other_reason').value;

            // Prepare data for submission
            const data = {
                transaction_id: transactionId,
                amount: amount,
                reason: reason,
            };

            if (reason === 'other') {
                data.other_reason = otherReason;
            }

            // Send AJAX request to process refund
            fetch(`/admin/payments/${paymentId}/refund`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Close modal and reload page to show updated data
                        document.getElementById('refundModal').classList.add('hidden');
                        alert(
                            'Refund initiated successfully. The amount will be credited back to the customer within 5-7 working days.');
                        window.location.reload();
                    } else {
                        alert('Failed to process refund: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing refund');
                });
        });
    </script>
@endsection
