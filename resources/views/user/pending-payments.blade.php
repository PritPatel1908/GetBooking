@extends('layouts.user')

@section('styles')
<style>
    :root {
        --primary-color: #3490dc;
        --primary-dark: #2779bd;
        --secondary-color: #38c172;
        --accent-color: #f6993f;
        --bg-color: #f8fafc;
        --text-color: #2d3748;
        --card-bg: #ffffff;
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --card-hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --header-bg: #ffffff;
        --footer-bg: #2d3748;
        --footer-text: #f7fafc;
        --border-color: #e2e8f0;
        --input-bg: #edf2f7;
        --input-text: #4a5568;
        --primary-gradient: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        --bg-primary: var(--bg-color);
        --bg-card: var(--card-bg);
        --text-primary: var(--text-color);
        --text-secondary: #718096;
        --hover-shadow: var(--card-hover-shadow);
    }

    .dark {
        --primary-color: #4299e1;
        --primary-dark: #3182ce;
        --secondary-color: #48bb78;
        --accent-color: #f6ad55;
        --bg-color: #1a202c;
        --text-color: #f7fafc;
        --card-bg: #2d3748;
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        --card-hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        --header-bg: #2d3748;
        --footer-bg: #1a202c;
        --footer-text: #f7fafc;
        --border-color: #4a5568;
        --input-bg: #4a5568;
        --input-text: #e2e8f0;
        --primary-gradient: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        --bg-primary: var(--bg-color);
        --bg-card: var(--card-bg);
        --text-primary: var(--text-color);
        --text-secondary: #a0aec0;
        --hover-shadow: var(--card-hover-shadow);
    }

    .payments-container {
        padding: 3rem 0;
        background: var(--bg-primary);
        min-height: 100vh;
        transition: background-color 0.3s ease;
    }

    .payments-header {
        margin-bottom: 3rem;
    }

    .payments-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--primary-gradient);
        padding: 2rem;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        flex: 1;
        text-align: center;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        z-index: 1;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }

    .stat-card i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, 0.9);
        position: relative;
        z-index: 2;
    }

    .stat-card h3 {
        margin: 0.5rem 0;
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        position: relative;
        z-index: 2;
    }

    .stat-card p {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
        color: white;
        position: relative;
        z-index: 2;
    }

    .payments-content {
        background: var(--bg-card);
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .payments-content h2 {
        color: var(--text-primary);
        font-size: 1.8rem;
        margin-bottom: 2rem;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .payments-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
    }

    .payments-table th,
    .payments-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .payments-table th {
        background: var(--input-bg);
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
    }

    .payments-table tr:hover {
        background: var(--input-bg);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        text-align: center;
        display: inline-block;
    }

    .status-refunded {
        background: #e0e7ff;
        color: #3730a3;
    }

    .status-cancelled {
        background: #f3f4f6;
        color: #4b5563;
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

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--hover-shadow);
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-info {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .stat-card.success {
        background: linear-gradient(135deg, #10B981, #059669);
    }

    .stat-card.warning {
        background: linear-gradient(135deg, #F59E0B, #D97706);
    }

    .stat-card.danger {
        background: linear-gradient(135deg, #EF4444, #DC2626);
    }

    .stat-card.info {
        background: linear-gradient(135deg, #3B82F6, #2563EB);
    }

    .transaction-id {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .payment-method {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .payment-method i {
        font-size: 1.25rem;
    }

    .filter-section {
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-primary);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .payments-table td, .payments-table th {
        overflow: visible !important;
        white-space: nowrap;
    }
    .btn {
        white-space: nowrap;
        min-width: unset;
        width: auto;
        display: inline-block;
    }

    .payments-table td {
        max-width: none;
    }

    @media (max-width: 768px) {
        .payments-stats {
            flex-direction: column;
        }

        .payments-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
@endsection

@section('content')
<div class="payments-container">
    <div class="container">
        <div class="payments-header">
            <div class="payments-stats">
                <div class="stat-card success">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Total Amount Paid</h3>
                    <p>₹{{ number_format($totalAmountPaid, 2) }}</p>
                </div>
                <div class="stat-card info">
                    <i class="fas fa-history"></i>
                    <h3>Total Transactions</h3>
                    <p>{{ $totalPaymentsCount }}</p>
                </div>
                <div class="stat-card warning">
                    <i class="fas fa-undo"></i>
                    <h3>Total Refunded</h3>
                    <p>₹{{ number_format($totalRefundedAmount, 2) }}</p>
                </div>
                <div class="stat-card danger">
                    <i class="fas fa-times-circle"></i>
                    <h3>Total Failed</h3>
                    <p>₹{{ number_format($totalFailedAmount, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="payments-content">
            <h2>All Transactions</h2>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="filter-section">
                <button class="filter-btn active" data-status="all">All</button>
                <button class="filter-btn" data-status="completed">Completed</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="failed">Failed</button>
                <button class="filter-btn" data-status="refunded">Refunded</button>
            </div>

            @if($payments->isEmpty())
                <div class="alert alert-info">
                    You have no payment transactions at the moment.
                </div>
            @else
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Booking ID</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr data-status="{{ strtolower($payment->payment_status) }}">
                                <td>
                                    <span class="transaction-id">{{ $payment->id }}</span>
                                </td>
                                <td>#{{ $payment->booking_id }}</td>
                                <td>
                                    @if($payment->booking && $payment->booking->details->isNotEmpty() && $payment->booking->details->first()->ground)
                                        {{ $payment->booking->details->first()->ground->name }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>₹{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->date ? $payment->date->format('M d, Y H:i') : 'N/A' }}</td>
                                <td>
                                    <div class="payment-method">
                                        @if($payment->payment_method === 'online')
                                            <i class="fas fa-globe"></i>
                                        @elseif($payment->payment_method === 'cash')
                                            <i class="fas fa-money-bill"></i>
                                        @else
                                            <i class="fas fa-credit-card"></i>
                                        @endif
                                        {{ ucfirst($payment->payment_method) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ strtolower($payment->payment_status) }}">
                                        {{ ucfirst($payment->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($payment->payment_status === 'pending')
                                        <button class="btn btn-primary" onclick="processPayment({{ $payment->id }})">
                                            Pay Now
                                        </button>
                                    @else
                                        <a href="{{ route('user.transaction.view', $payment->id) }}" class="btn btn-primary">
                                            View Details
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
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

    function viewPaymentDetails(paymentId) {
        // Implement view payment details logic here
        console.log('Viewing payment details for ID:', paymentId);
        // You can show a modal with payment details
    }

    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const tableRows = document.querySelectorAll('.payments-table tbody tr');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const status = this.dataset.status;

                // Filter rows
                tableRows.forEach(row => {
                    if (status === 'all' || row.dataset.status === status) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endsection
