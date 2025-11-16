@extends('layouts.admin')

@section('title', 'Payments')
@section('content')
    <main class="flex-1 overflow-y-auto p-4 page-transition">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-xl font-semibold">Payments Management</h2>
            </div>

            <div class="p-4">
                <div class="flex flex-wrap gap-4 mb-6">
                    <div class="flex-1 min-w-[250px]">
                        <div class="relative">
                            <input type="text" placeholder="Search transactions..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="w-[200px]">
                        <select
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="initiated">Initiated</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="w-[200px]">
                        <input type="date"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Transaction ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Booking</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $payment->transaction_id ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $payment->date->format('d M Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $payment->user->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $payment->user->email ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($payment->booking)
                                            <div class="text-sm text-gray-900">{{ $payment->booking->booking_sku }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $payment->booking->ground->name ?? 'N/A' }}</div>
                                        @else
                                            <div class="text-sm text-gray-500">N/A</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            ₹{{ number_format($payment->amount, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst($payment->payment_method ?? 'N/A') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $payment->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $payment->payment_status === 'initiated' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $payment->payment_status === 'processing' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $payment->payment_status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $payment->payment_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $payment->payment_status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $payment->payment_status === 'refunded' ? 'bg-purple-100 text-purple-800' : '' }}">
                                            {{ ucfirst($payment->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.payments.show', $payment->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                                title="View Payment">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="updatePaymentStatus({{ $payment->id }})"
                                                class="text-blue-600 hover:text-blue-900 transition-colors"
                                                title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if ($payment->payment_status === 'completed' && $payment->transaction_id)
                                                <button
                                                    onclick="initiateRefund({{ $payment->id }}, '{{ $payment->transaction_id }}', {{ $payment->amount }})"
                                                    class="text-red-600 hover:text-red-900 transition-colors"
                                                    title="Refund Payment">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        No payment records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between mt-6">
                    <div class="text-sm text-gray-600">
                        Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of
                        {{ $payments->total() ?? 0 }} entries
                    </div>
                    <div>
                        {{ $payments->links() }}
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
                        <input type="hidden" id="payment_id" name="payment_id">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                            <select id="payment_status" name="payment_status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="pending">Pending</option>
                                <option value="initiated">Initiated</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
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
                        <input type="hidden" id="refund_payment_id" name="refund_payment_id">
                        <input type="hidden" id="refund_transaction_id" name="refund_transaction_id">
                        <input type="hidden" id="refund_amount" name="refund_amount">

                        <div class="mb-4">
                            <p class="text-gray-700 mb-2">Are you sure you want to refund this payment?</p>
                            <p class="text-gray-700 mb-2">Transaction ID: <span id="display_transaction_id"
                                    class="font-medium"></span></p>
                            <p class="text-gray-700">Amount: ₹<span id="display_amount" class="font-medium"></span></p>
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
                            'Refund initiated successfully. The amount will be credited back to the customer within 5-7 working days.'
                            );
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
