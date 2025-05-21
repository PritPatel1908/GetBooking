@extends('layouts.user')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
<style>
    .booking-container {
        padding: 2rem 0;
        color: var(--text-color);
        background-color: var(--bg-color);
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
    }

    .page-title:after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 3px;
        background: var(--primary-color);
        transform-origin: left;
        animation: underlineAnimation 3s infinite alternate;
    }

    @keyframes underlineAnimation {
        from {
            transform: scaleX(0.5);
        }
        to {
            transform: scaleX(1);
        }
    }

    .booking-status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-left: 1rem;
    }

    .booking-status-badge.confirmed {
        background-color: rgba(72, 187, 120, 0.2);
        color: #2F855A;
    }

    .booking-status-badge.pending {
        background-color: rgba(246, 173, 85, 0.2);
        color: #C05621;
    }

    .booking-status-badge.cancelled {
        background-color: rgba(229, 62, 62, 0.2);
        color: #C53030;
    }

    .booking-status-badge.completed {
        background-color: rgba(79, 209, 197, 0.2);
        color: #2B6CB0;
    }

    .booking-id {
        font-size: 1.1rem;
        color: var(--input-text);
        margin-bottom: 2rem;
    }

    .booking-card {
        background: var(--card-bg);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
    }

    .booking-section {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .booking-section:last-child {
        border-bottom: none;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--text-color);
        position: relative;
        display: inline-block;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 30px;
        height: 2px;
        background: var(--primary-color);
    }

    .booking-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .booking-info-item {
        margin-bottom: 1rem;
    }

    .info-label {
        font-size: 0.9rem;
        color: var(--input-text);
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-color);
    }

    .ground-info {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
    }

    .ground-image {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        object-fit: cover;
    }

    .ground-details {
        flex: 1;
    }

    .ground-name {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }

    .ground-location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--input-text);
        margin-bottom: 0.5rem;
    }

    .price-breakdown {
        background: var(--input-bg);
        border-radius: 8px;
        padding: 1.5rem;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        color: var(--input-text);
    }

    .price-total {
        display: flex;
        justify-content: space-between;
        padding-top: 1rem;
        margin-top: 1rem;
        border-top: 1px dashed var(--border-color);
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--text-color);
    }

    .price-amount {
        color: var(--primary-color);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .action-btn {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        border: none;
    }

    .back-btn {
        background: var(--input-bg);
        color: var(--input-text);
    }

    .back-btn:hover {
        background: var(--border-color);
    }

    .cancel-btn {
        background: rgba(229, 62, 62, 0.1);
        color: #E53E3E;
    }

    .cancel-btn:hover {
        background: #E53E3E;
        color: white;
    }

    .rebook-btn {
        background: var(--primary-color);
        color: white;
    }

    .rebook-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3);
    }

    .slot-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .slot-tag {
        background: var(--input-bg);
        border-radius: 6px;
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
        color: var(--input-text);
    }

    .slot-tag i {
        color: var(--primary-color);
        margin-right: 0.3rem;
    }

    .payment-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .payment-status {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .payment-status.paid {
        background-color: rgba(72, 187, 120, 0.2);
        color: #2F855A;
    }

    .payment-status.pending {
        background-color: rgba(246, 173, 85, 0.2);
        color: #C05621;
    }

    .payment-status.failed {
        background-color: rgba(229, 62, 62, 0.2);
        color: #C53030;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .booking-info-grid {
            grid-template-columns: 1fr;
        }

        .ground-info {
            flex-direction: column;
        }

        .ground-image {
            width: 100%;
            height: 200px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .action-btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="container booking-container">
    <div class="page-header" data-aos="fade-up">
        <div class="d-flex align-items-center">
            <h1 class="page-title">Booking Details</h1>
            @php
                $statusClass = 'pending';
                $statusLabel = 'Pending';

                if ($booking->booking_status == 'confirmed') {
                    $statusClass = 'confirmed';
                    $statusLabel = 'Confirmed';
                } elseif ($booking->booking_status == 'cancelled') {
                    $statusClass = 'cancelled';
                    $statusLabel = 'Cancelled';
                } elseif ($booking->booking_status == 'completed' ||
                        (\Carbon\Carbon::parse($booking->booking_date)->lt(\Carbon\Carbon::today()) &&
                         $booking->booking_status != 'cancelled')) {
                    $statusClass = 'completed';
                    $statusLabel = 'Completed';
                } elseif ($booking->payment && $booking->payment->payment_status == 'pending') {
                    $statusClass = 'pending';
                    $statusLabel = 'Payment Pending';
                }
            @endphp
            <span class="booking-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
        <p class="booking-id">Booking ID: #{{ $booking->booking_sku }}</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Booking Information -->
            <div class="booking-card" data-aos="fade-up">
                <div class="booking-section">
                    <h3 class="section-title">Booking Information</h3>
                    <div class="booking-info-grid">
                        <div class="booking-info-item">
                            <div class="info-label">Booking Date</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($booking->booking_date)->format('l, F d, Y') }}</div>
                        </div>
                        <div class="booking-info-item">
                            <div class="info-label">Booking Time</div>
                            <div class="info-value">
                                @php
                                    $timeRange = $booking->booking_time;
                                    try {
                                        // Check if the booking time already contains a range (e.g., "05:00 - 07:00")
                                        if (strpos($booking->booking_time, '-') !== false) {
                                            $timeRange = $booking->booking_time; // Use as is if it's already a range
                                        } else {
                                            // If it's just a start time, calculate end time using duration
                                            $startTime = \Carbon\Carbon::parse($booking->booking_time);
                                            $endTime = (clone $startTime)->addHours($booking->duration);
                                            $timeRange = $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
                                        }
                                    } catch (\Exception $e) {
                                        // Fallback if parsing fails
                                        $timeRange = $booking->booking_time;
                                    }
                                @endphp
                                {{ $timeRange }} ({{ $booking->duration }} hours)
                            </div>
                        </div>
                        <div class="booking-info-item">
                            <div class="info-label">Booking Status</div>
                            <div class="info-value">{{ $statusLabel }}</div>
                        </div>
                        <div class="booking-info-item">
                            <div class="info-label">Booking Date</div>
                            <div class="info-value">{{ $booking->created_at->format('M d, Y, H:i') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Ground Information -->
                <div class="booking-section">
                    <h3 class="section-title">Ground Information</h3>
                    @if($booking->details->isNotEmpty() && $booking->details->first()->ground)
                        @php
                            $ground = $booking->details->first()->ground;
                            $groundImage = 'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?q=80&w=1200&auto=format&fit=crop';

                            // If ground has images, use the first one
                            if (isset($ground->images) && $ground->images->isNotEmpty()) {
                                $groundImage = $ground->images->first()->image_url;
                            }
                        @endphp
                        <div class="ground-info">
                            <img src="{{ $groundImage }}" class="ground-image">
                            <div class="ground-details">
                                <h4 class="ground-name">{{ $ground->name }}</h4>
                                <div class="ground-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $ground->location }}</span>
                                </div>
                                <div class="ground-location">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $ground->contact_number ?? 'N/A' }}</span>
                                </div>

                                <div class="mt-3">
                                    <div class="info-label">Time Slots</div>
                                    <div class="slot-list">
                                        @foreach($booking->details as $detail)
                                            @if(isset($detail->slot) && $detail->slot)
                                                <span class="slot-tag">
                                                    <i class="far fa-clock"></i> {{ $detail->slot->slot_name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p>Ground information not available</p>
                    @endif
                </div>

                <!-- Payment Information -->
                <div class="booking-section">
                    <h3 class="section-title">Payment Information</h3>
                    <div class="booking-info-grid">
                        <div class="booking-info-item">
                            <div class="info-label">Payment Method</div>
                            <div class="info-value">
                                @if($booking->payment)
                                    {{ ucfirst($booking->payment->payment_method) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="booking-info-item">
                            <div class="info-label">Payment Status</div>
                            <div class="info-value payment-info">
                                @if($booking->payment)
                                    @php
                                        $paymentStatusClass = 'pending';
                                        if($booking->payment->payment_status == 'completed') {
                                            $paymentStatusClass = 'paid';
                                        } elseif($booking->payment->payment_status == 'failed') {
                                            $paymentStatusClass = 'failed';
                                        }
                                    @endphp
                                    <span class="payment-status {{ $paymentStatusClass }}">
                                        {{ ucfirst($booking->payment->payment_status) }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="booking-info-item">
                            <div class="info-label">Payment Date</div>
                            <div class="info-value">
                                @if($booking->payment && $booking->payment->date)
                                    {{ \Carbon\Carbon::parse($booking->payment->date)->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="booking-info-item">
                            <div class="info-label">Transaction ID</div>
                            <div class="info-value">
                                @if($booking->payment && $booking->payment->transaction_id)
                                    {{ $booking->payment->transaction_id }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons" data-aos="fade-up">
                <a href="{{ route('user.my_bookings') }}" class="action-btn back-btn">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
                </a>

                @if($booking->booking_status != 'cancelled' &&
                   $booking->booking_status != 'completed' &&
                   \Carbon\Carbon::parse($booking->booking_date)->gt(\Carbon\Carbon::today()))
                    <button class="action-btn cancel-btn" data-booking="{{ $booking->id }}">
                        <i class="fas fa-times mr-2"></i> Cancel Booking
                    </button>
                @endif

                @if(isset($booking->details->first()->ground))
                    <a href="{{ route('user.view_ground', $booking->details->first()->ground->id) }}" class="action-btn rebook-btn">
                        <i class="fas fa-redo mr-2"></i> Book Again
                    </a>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Price Summary -->
            <div class="booking-card" data-aos="fade-up" data-aos-delay="100">
                <div class="booking-section">
                    <h3 class="section-title">Price Summary</h3>
                    <div class="price-breakdown">
                        <div class="price-row">
                            <div>Base Price</div>
                            <div>₹{{ number_format($booking->amount * 0.8, 2) }}</div>
                        </div>
                        <div class="price-row">
                            <div>GST (18%)</div>
                            <div>₹{{ number_format($booking->amount * 0.18, 2) }}</div>
                        </div>
                        <div class="price-row">
                            <div>Platform Fee</div>
                            <div>₹{{ number_format($booking->amount * 0.02, 2) }}</div>
                        </div>
                        <div class="price-total">
                            <div>Total Amount</div>
                            <div class="price-amount">₹{{ number_format($booking->amount, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Policy -->
            <div class="booking-card" data-aos="fade-up" data-aos-delay="200">
                <div class="booking-section">
                    <h3 class="section-title">Cancellation Policy</h3>
                    <ul class="mt-3">
                        <li class="mb-2">Free cancellation up to 24 hours before booking time</li>
                        <li class="mb-2">50% refund for cancellations between 24 and 12 hours before booking time</li>
                        <li class="mb-2">No refund for cancellations less than 12 hours before booking time</li>
                        <li class="mb-2">Full refund if ground owner cancels the booking</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Cancel booking functionality
        const cancelButton = document.querySelector('.cancel-btn');

        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                const confirmed = confirm('Are you sure you want to cancel this booking? Cancellation policies may apply.');

                if (confirmed) {
                    const bookingId = this.dataset.booking;

                    // Show cancellation in progress
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Cancelling...';
                    this.disabled = true;

                    // Send cancellation request
                    fetch(`/user/bookings/${bookingId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update status display
                            document.querySelector('.booking-status-badge').className = 'booking-status-badge cancelled';
                            document.querySelector('.booking-status-badge').textContent = 'Cancelled';

                            // Remove cancel button
                            this.remove();

                            // Show success message
                            alert('Booking cancelled successfully. A confirmation email has been sent to your registered email address.');
                        } else {
                            alert('Error: ' + data.message);
                            this.innerHTML = '<i class="fas fa-times mr-2"></i> Cancel Booking';
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while trying to cancel the booking.');
                        this.innerHTML = '<i class="fas fa-times mr-2"></i> Cancel Booking';
                        this.disabled = false;
                    });
                }
            });
        }
    });
</script>
@endsection
