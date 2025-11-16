<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($payments as $payment)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $payment->transaction_id ?? 'N/A' }}</div>
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
                        <div class="text-sm text-gray-500">{{ $payment->booking->ground->name ?? 'N/A' }}</div>
                    @else
                        <div class="text-sm text-gray-500">N/A</div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">₹{{ number_format($payment->amount, 2) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ ucfirst($payment->payment_method ?? 'N/A') }}</div>
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
                            class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Payment">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="updatePaymentStatus({{ $payment->id }})"
                            class="text-blue-600 hover:text-blue-900 transition-colors" title="Update Status">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if ($payment->payment_status === 'completed' && $payment->transaction_id)
                            <button
                                onclick="initiateRefund({{ $payment->id }}, '{{ $payment->transaction_id }}', {{ $payment->amount }})"
                                class="text-red-600 hover:text-red-900 transition-colors" title="Refund Payment">
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
