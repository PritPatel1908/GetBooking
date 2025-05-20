@extends('layouts.user')

@section('styles')
<style>
    .transaction-container {
        padding: 2rem 0;
        background: var(--bg-primary);
        min-height: 100vh;
    }

    .transaction-header {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .transaction-header h1 {
        color: var(--text-primary);
        font-size: 1.875rem;
        font-weight: 700;
    }

    .transaction-card {
        background: var(--bg-card);
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        padding: 2.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }

    .transaction-status {
        display: inline-flex;
        align-items: center;
        padding: 0.625rem 1.25rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        gap: 0.5rem;
    }

    .transaction-status i {
        font-size: 1rem;
    }

    .status-completed {
        background: #dcfce7;
        color: #166534;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-refunded {
        background: #e0e7ff;
        color: #3730a3;
    }

    .transaction-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .info-item {
        background: var(--input-bg);
        padding: 1.25rem;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .info-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow);
    }

    .info-label {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .info-value {
        font-size: 1.25rem;
        color: var(--text-primary);
        font-weight: 600;
    }

    .booking-details {
        margin-top: 2.5rem;
        background: var(--input-bg);
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .booking-details h3 {
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        font-size: 1.25rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .booking-details h3 i {
        color: var(--primary-color);
    }

    .booking-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--bg-card);
        border-radius: 12px;
        overflow: hidden;
    }

    .booking-table th,
    .booking-table td {
        padding: 1.25rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .booking-table th {
        background: var(--input-bg);
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.05em;
    }

    .booking-table tr:last-child td {
        border-bottom: none;
    }

    .booking-table tr:hover td {
        background: var(--input-bg);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .btn {
        padding: 0.875rem 1.75rem;
        border: none;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 160px;
        justify-content: center;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    .btn-secondary {
        background: var(--input-bg);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow);
    }

    .btn:active {
        transform: translateY(0);
    }

    .payment-method {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        background: var(--bg-card);
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .payment-method i {
        font-size: 1.25rem;
        color: var(--primary-color);
    }

    @media (max-width: 768px) {
        .transaction-info {
            grid-template-columns: 1fr;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }

        .transaction-card {
            padding: 1.5rem;
        }

        .booking-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
@endsection

@section('content')
<div class="transaction-container">
    <div class="container">
        <div class="transaction-header">
            <h1>Transaction Details</h1>
            <a href="{{ route('user.pending-payments') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Transactions
            </a>
        </div>

        <div class="transaction-card">
            <span class="transaction-status status-{{ strtolower($transaction->payment_status) }}">
                @if($transaction->payment_status === 'completed')
                    <i class="fas fa-check-circle"></i>
                @elseif($transaction->payment_status === 'pending')
                    <i class="fas fa-clock"></i>
                @elseif($transaction->payment_status === 'failed')
                    <i class="fas fa-times-circle"></i>
                @elseif($transaction->payment_status === 'refunded')
                    <i class="fas fa-undo"></i>
                @endif
                {{ ucfirst($transaction->payment_status) }}
            </span>

            <div class="transaction-info">
                <div class="info-item">
                    <div class="info-label">Transaction ID</div>
                    <div class="info-value">#{{ $transaction->id }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Booking ID</div>
                    <div class="info-value">#{{ $transaction->booking_id }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Amount</div>
                    <div class="info-value">₹{{ number_format($transaction->amount, 2) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Date</div>
                    <div class="info-value">{{ $transaction->date ? $transaction->date->format('M d, Y H:i') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value">
                        <div class="payment-method">
                            @if($transaction->payment_method === 'online')
                                <i class="fas fa-globe"></i>
                            @elseif($transaction->payment_method === 'cash')
                                <i class="fas fa-money-bill"></i>
                            @else
                                <i class="fas fa-credit-card"></i>
                            @endif
                            {{ ucfirst($transaction->payment_method) }}
                        </div>
                    </div>
                </div>
            </div>

            @if($transaction->booking)
                <div class="booking-details">
                    <h3>
                        <i class="fas fa-calendar-check"></i>
                        Booking Details
                    </h3>
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->booking->details as $detail)
                                <tr>
                                    <td>
                                        @if($detail->ground)
                                            {{ $detail->ground->name }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $detail->date ? $detail->date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $detail->time ? $detail->time->format('h:i A') : 'N/A' }}</td>
                                    <td>
                                        <span class="transaction-status status-{{ strtolower($transaction->booking->booking_status) }}">
                                            {{ ucfirst($transaction->booking->booking_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="action-buttons">
                @if($transaction->payment_status === 'pending')
                    <button class="btn btn-primary" onclick="processPayment({{ $transaction->id }})">
                        <i class="fas fa-money-bill-wave"></i>
                        Pay Now
                    </button>
                @endif
                @if($transaction->payment_status === 'completed' && $transaction->booking && $transaction->booking->booking_status === 'confirmed')
                    <button class="btn btn-secondary" onclick="downloadReceipt({{ $transaction->id }})">
                        <i class="fas fa-download"></i>
                        Download Receipt
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function processPayment(paymentId) {
        // Implement payment processing logic here
        console.log('Processing payment for ID:', paymentId);
        // You can redirect to payment gateway or show payment modal
    }

    function downloadReceipt(paymentId) {
        // Implement receipt download logic here
        console.log('Downloading receipt for payment ID:', paymentId);
        // You can generate and download PDF receipt
    }
</script>
@endsection
