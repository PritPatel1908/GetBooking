@extends('layouts.user')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
<style>
    .my-bookings-container {
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

    .page-subtitle {
        color: var(--input-text);
        font-size: 1.1rem;
    }

    /* Booking tabs */
    .booking-tabs {
        display: flex;
        margin-bottom: 2rem;
        border-radius: 12px;
        overflow: hidden;
        background: var(--card-bg);
        box-shadow: var(--card-shadow);
    }

    .booking-tab {
        flex: 1;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        color: var(--text-color);
        font-weight: 600;
        border-bottom: 3px solid transparent;
    }

    .booking-tab:hover {
        background: var(--input-bg);
    }

    .booking-tab.active {
        border-bottom: 3px solid var(--primary-color);
        background: var(--input-bg);
        color: var(--primary-color);
    }

    .booking-tab .count-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Booking cards */
    .booking-card {
        background: var(--card-bg);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
    }

    .booking-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .booking-status {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 10;
    }

    .booking-status.confirmed {
        background-color: rgba(72, 187, 120, 0.2);
        color: #2F855A;
    }

    .booking-status.pending {
        background-color: rgba(246, 173, 85, 0.2);
        color: #C05621;
    }

    .booking-status.cancelled {
        background-color: rgba(229, 62, 62, 0.2);
        color: #C53030;
    }

    .booking-status.completed {
        background-color: rgba(79, 209, 197, 0.2);
        color: #2B6CB0;
    }

    .booking-header {
        display: flex;
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .booking-image {
        width: 100px;
        height: 100px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 1.5rem;
    }

    .booking-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }

    .booking-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .booking-meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--input-text);
    }

    .booking-meta-item i {
        color: var(--primary-color);
    }

    .booking-details {
        padding: 1.5rem;
    }

    .booking-info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed var(--border-color);
    }

    .booking-info-row:last-child {
        border-bottom: none;
    }

    .booking-info-label {
        font-weight: 600;
        color: var(--text-color);
    }

    .booking-info-value {
        color: var(--input-text);
    }

    .booking-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .booking-actions {
        padding: 1rem 1.5rem;
        background: var(--input-bg);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border-color);
    }

    .action-btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        border: none;
    }

    .view-btn {
        background: rgba(66, 153, 225, 0.1);
        color: var(--primary-color);
    }

    .view-btn:hover {
        background: var(--primary-color);
        color: white;
    }

    .cancel-btn {
        background: rgba(229, 62, 62, 0.1);
        color: #E53E3E;
    }

    .cancel-btn:hover {
        background: #E53E3E;
        color: white;
    }

    .review-btn {
        background: rgba(72, 187, 120, 0.1);
        color: #2F855A;
    }

    .review-btn:hover {
        background: #2F855A;
        color: white;
    }

    .rebook-btn {
        background: rgba(246, 173, 85, 0.1);
        color: #C05621;
    }

    .rebook-btn:hover {
        background: #C05621;
        color: white;
    }

    .download-btn {
        background: rgba(79, 209, 197, 0.1);
        color: #2B6CB0;
        margin-left: 10px;
    }

    .download-btn:hover {
        background: #2B6CB0;
        color: white;
    }

    .payment-btn {
        background: rgba(72, 187, 120, 0.1);
        color: #2F855A;
    }

    .payment-btn:hover {
        background: #2F855A;
        color: white;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem;
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: var(--card-shadow);
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--border-color);
        margin-bottom: 1.5rem;
    }

    .empty-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-color);
    }

    .empty-subtitle {
        color: var(--input-text);
        margin-bottom: 2rem;
    }

    .browse-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.8rem 2rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .browse-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3);
    }

    /* Review Modal */
    .review-modal-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        visibility: hidden;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .review-modal-bg.active {
        visibility: visible;
        opacity: 1;
    }

    .review-modal {
        background: var(--card-bg);
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        padding: 2rem;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        transform: translateY(30px);
        transition: all 0.3s ease;
    }

    .review-modal-bg.active .review-modal {
        transform: translateY(0);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-color);
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--input-text);
        transition: all 0.3s ease;
    }

    .close-modal:hover {
        color: var(--primary-color);
        transform: rotate(90deg);
    }

    .rating-stars {
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .rating-stars i {
        color: #CBD5E0;
        font-size: 2rem;
        cursor: pointer;
        margin: 0 0.3rem;
        transition: all 0.2s ease;
    }

    .rating-stars i:hover,
    .rating-stars i.active {
        color: #F6AD55;
        transform: scale(1.2);
    }

    /* Animation classes */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }

    .fade-in-delay-1 {
        opacity: 0;
        animation: fadeIn 0.5s ease forwards 0.1s;
    }

    .fade-in-delay-2 {
        opacity: 0;
        animation: fadeIn 0.5s ease forwards 0.2s;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .booking-header {
            flex-direction: column;
        }

        .booking-image {
            width: 100%;
            height: 180px;
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .booking-actions {
            flex-direction: column;
            gap: 0.8rem;
        }

        .action-btn {
            width: 100%;
        }

        .booking-tabs {
            overflow-x: auto;
        }
    }

    /* Cancellation Modal Styles */
    .cancellation-modal-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        visibility: hidden;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .cancellation-modal-bg.active {
        visibility: visible;
        opacity: 1;
    }

    .cancellation-modal {
        background: var(--card-bg);
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        padding: 2rem;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        transform: translateY(30px);
        transition: all 0.3s ease;
    }

    .cancellation-modal-bg.active .cancellation-modal {
        transform: translateY(0);
    }

    .cancellation-icon {
        text-align: center;
        font-size: 3rem;
        color: #E53E3E;
        margin-bottom: 1rem;
    }

    .cancellation-message {
        text-align: center;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--text-color);
    }

    .cancellation-policy {
        background: var(--input-bg);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .cancellation-policy h5 {
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }

    .cancellation-policy ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .cancellation-policy li {
        color: var(--input-text);
        margin-bottom: 0.5rem;
        padding-left: 1.5rem;
        position: relative;
    }

    .cancellation-policy li:before {
        content: '•';
        position: absolute;
        left: 0.5rem;
        color: var(--primary-color);
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .cancel-confirm-btn {
        background: #E53E3E;
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .cancel-confirm-btn:hover {
        background: #C53030;
        transform: translateY(-2px);
    }

    .cancel-dismiss-btn {
        background: var(--input-bg);
        color: var(--text-color);
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .cancel-dismiss-btn:hover {
        background: var(--border-color);
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="container my-bookings-container">
    <div class="page-header" data-aos="fade-up">
        <h1 class="page-title">My Bookings</h1>
        <p class="page-subtitle">View and manage all your ground bookings</p>
    </div>

    <!-- Booking Tabs -->
    <div class="booking-tabs" data-aos="fade-up" data-aos-delay="100">
        <div class="booking-tab active" data-tab="all">
            All
            <span class="count-badge">{{ $bookings->count() }}</span>
        </div>
        <div class="booking-tab" data-tab="upcoming">
            Upcoming
            <span class="count-badge">{{ $upcomingCount }}</span>
        </div>
        <div class="booking-tab" data-tab="completed">
            Completed
            <span class="count-badge">{{ $completedCount }}</span>
        </div>
        <div class="booking-tab" data-tab="cancelled">
            Cancelled
            <span class="count-badge">{{ $cancelledCount }}</span>
        </div>
    </div>

    <!-- Booking List -->
    <div class="booking-list" data-aos="fade-up" data-aos-delay="200">
        @if($bookings->count() > 0)
            @foreach($bookings as $booking)
                @php
                    $groundName = '';
                    $groundLocation = '';
                    $groundImage = 'https://images.unsplash.com/photo-1575361204480-aadea25e6e68?q=80&w=1200&auto=format&fit=crop';

                    // Get the ground details from the first booking detail
                    if ($booking->details->isNotEmpty() && $booking->details->first()->ground) {
                        $ground = $booking->details->first()->ground;
                        $groundName = $ground->name;
                        $groundLocation = $ground->location;

                        // If ground has images, use the first one
                        if ($ground->images && $ground->images->isNotEmpty()) {
                            // Check if image_url exists, otherwise fall back to image_path
                            if (isset($ground->images->first()->image_url)) {
                                $groundImage = $ground->images->first()->image_url;
                            } elseif (isset($ground->images->first()->image_path)) {
                                $groundImage = asset($ground->images->first()->image_path);
                            }
                        }
                    }

                    // Determine booking category
                    $bookingCategory = 'upcoming';
                    if ($booking->booking_status == 'cancelled') {
                        $bookingCategory = 'cancelled';
                    } elseif ($booking->booking_status == 'completed' ||
                            (\Carbon\Carbon::parse($booking->booking_date)->lt(\Carbon\Carbon::today()) &&
                             $booking->booking_status != 'cancelled')) {
                        $bookingCategory = 'completed';
                    }

                    // Format booking date
                    $bookingDate = \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y');

                    // Calculate end time (handle time slot format safely)
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

                    // Determine booking status class and label
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

                    $groundId = $booking->details->isNotEmpty() && $booking->details->first()->ground
                              ? $booking->details->first()->ground->id
                              : '';
                    $viewUrl = route('user.view_booking', $booking->booking_sku);
                @endphp

                <div class="booking-card fade-in-delay-1" data-category="{{ $bookingCategory }}" data-ground-id="{{ $groundId }}" data-view-url="{{ $viewUrl }}">
                    <div class="booking-status {{ $statusClass }}">{{ $statusLabel }}</div>
                    <div class="booking-header">
                        <img src="{{ $groundImage }}" class="booking-image">
                        <div>
                            <h3 class="booking-title">{{ $groundName }}</h3>
                            <div class="booking-meta">
                                <div class="booking-meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>{{ $groundLocation }}</span>
                                </div>
                                <div class="booking-meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>{{ $bookingDate }}</span>
                                </div>
                                <div class="booking-meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span>{{ $timeRange }}</span>
                                </div>
                            </div>
                            <div class="booking-meta-item">
                                <i class="fas fa-id-card"></i>
                                <span>Booking ID: #{{ $booking->booking_sku }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="booking-details">
                        <div class="booking-info-row">
                            <div class="booking-info-label">Duration</div>
                            <div class="booking-info-value">{{ $booking->duration }} hours</div>
                        </div>
                        <div class="booking-info-row">
                            <div class="booking-info-label">Number of Players</div>
                            <div class="booking-info-value">
                                @if(isset($booking->details->first()->ground->capacity))
                                    {{ $booking->details->first()->ground->capacity }} players
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="booking-info-row">
                            <div class="booking-info-label">Additional Services</div>
                            <div class="booking-info-value">
                                @if(isset($booking->details->first()->ground->features) && $booking->details->first()->ground->features->isNotEmpty())
                                    {{ $booking->details->first()->ground->features->pluck('feature_name')->implode(', ') }}
                                @else
                                    None
                                @endif
                            </div>
                        </div>
                        <div class="booking-info-row">
                            <div class="booking-info-label">Payment Method</div>
                            <div class="booking-info-value">
                                @if ($booking->payment)
                                    {{ ucfirst($booking->payment->payment_method) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="booking-info-row">
                            <div class="booking-info-label">Total Amount</div>
                            <div class="booking-price">₹{{ number_format($booking->amount, 2) }}</div>
                        </div>
                    </div>
                    <div class="booking-actions">
                        <div>
                            <a href="{{ route('user.view_booking', $booking->booking_sku) }}" class="action-btn view-btn">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </a>
                            <a href="{{ route('user.download_invoice', $booking->booking_sku) }}" class="action-btn download-btn">
                                <i class="fas fa-download mr-1"></i> Download Invoice
                            </a>
                        </div>
                        <div>
                            @if($booking->payment && $booking->payment->payment_status == 'pending' && $bookingCategory != 'cancelled')
                                <a href="{{ route('user.pending-payments') }}" class="action-btn payment-btn">
                                    <i class="fas fa-credit-card mr-1"></i> Pay Now
                                </a>
                            @elseif($bookingCategory == 'upcoming')
                                <button class="action-btn cancel-btn" data-booking="{{ $booking->id }}">
                                    <i class="fas fa-times mr-1"></i> Cancel Booking
                                </button>
                            @elseif($bookingCategory == 'completed')
                                <button class="action-btn review-btn" data-booking="{{ $booking->booking_sku }}">
                                    <i class="fas fa-star mr-1"></i> Leave Review
                                </button>
                                @if($booking->details->isNotEmpty() && $booking->details->first()->ground)
                                    <a href="{{ route('user.view_ground', $booking->booking_sku) }}/${groundId}" class="action-btn rebook-btn">
                                        <i class="fas fa-redo mr-1"></i> Book Again
                                    </a>
                                @endif
                            @elseif($bookingCategory == 'cancelled')
                                @if($booking->details->isNotEmpty() && $booking->details->first()->ground)
                                    <a href="{{ route('user.view_ground', $booking->booking_sku) }}/${groundId}" class="action-btn rebook-btn">
                                        <i class="fas fa-redo mr-1"></i> Book Again
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <!-- Empty State (Show when no bookings found) -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3 class="empty-title">No bookings found</h3>
                <p class="empty-subtitle">You haven't made any bookings yet. Start by exploring our available grounds.</p>
                <a href="{{ route('user.all_grounds') }}" class="browse-btn">
                    <i class="fas fa-search mr-2"></i> Browse Grounds
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Review Modal -->
<div class="review-modal-bg">
    <div class="review-modal">
        <div class="modal-header">
            <h4 class="modal-title">Rate Your Experience</h4>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="rating-stars">
                <i class="far fa-star" data-rating="1"></i>
                <i class="far fa-star" data-rating="2"></i>
                <i class="far fa-star" data-rating="3"></i>
                <i class="far fa-star" data-rating="4"></i>
                <i class="far fa-star" data-rating="5"></i>
            </div>

            <div class="form-group mb-3">
                <label for="reviewTitle" class="form-label">Title</label>
                <input type="text" class="form-control" id="reviewTitle" placeholder="Summarize your experience">
            </div>

            <div class="form-group mb-4">
                <label for="reviewComment" class="form-label">Your Review</label>
                <textarea class="form-control" id="reviewComment" rows="4"
                    placeholder="Tell us about your experience"></textarea>
            </div>

            <button type="button" class="booking-btn" id="submitReview">
                Submit Review <i class="fas fa-paper-plane ml-2"></i>
            </button>
        </div>
    </div>
</div>

<!-- Cancellation Confirmation Modal -->
<div class="cancellation-modal-bg">
    <div class="cancellation-modal">
        <div class="modal-header">
            <h4 class="modal-title">Cancel Booking</h4>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="cancellation-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p class="cancellation-message">Are you sure you want to cancel this booking?</p>
            <div class="cancellation-policy">
                <h5>Cancellation Policy:</h5>
                <ul>
                    <li>Free cancellation up to 24 hours before booking time</li>
                    <li>50% refund for cancellations between 24 and 12 hours before booking time</li>
                    <li>No refund for cancellations less than 12 hours before booking time</li>
                </ul>
            </div>
            <div class="modal-actions">
                <button class="cancel-confirm-btn">Yes, Cancel Booking</button>
                <button class="cancel-dismiss-btn">No, Keep Booking</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log('DOM Content Loaded');

        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Tab Functionality
        const tabs = document.querySelectorAll('.booking-tab');
        const bookingCards = document.querySelectorAll('.booking-card');
        const emptyState = document.querySelector('.empty-state');

        console.log('Total booking cards:', bookingCards.length);
        console.log('Empty state element:', emptyState);

        // Show all cards initially
        bookingCards.forEach(card => {
            card.style.display = 'block';
        });

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                console.log('Tab clicked:', tab.dataset.tab);

                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));

                // Add active class to clicked tab
                tab.classList.add('active');

                // Get the category
                const category = tab.dataset.tab;
                console.log('Selected category:', category);

                // Hide all booking cards
                bookingCards.forEach(card => {
                    card.style.display = 'none';
                });

                // If category is 'all', show all cards
                if (category === 'all') {
                    console.log('Showing all cards');
                    bookingCards.forEach(card => {
                        card.style.display = 'block';
                    });
                    // If there are cards, hide empty state
                    if (bookingCards.length > 0) {
                        emptyState.style.display = 'none';
                    } else {
                        emptyState.style.display = 'block';
                    }
                } else {
                    // Show only cards with matching category
                    const filteredCards = document.querySelectorAll(`.booking-card[data-category="${category}"]`);
                    console.log('Filtered cards for category', category, ':', filteredCards.length);

                    filteredCards.forEach(card => {
                        card.style.display = 'block';
                    });

                    // If no cards match the category, show empty state
                    if (filteredCards.length === 0) {
                        console.log('No cards found for category, showing empty state');
                        emptyState.style.display = 'block';
                    } else {
                        console.log('Cards found for category, hiding empty state');
                        emptyState.style.display = 'none';
                    }
                }
            });
        });

        // Review Modal Functionality
        const reviewButtons = document.querySelectorAll('.review-btn');
        const reviewModal = document.querySelector('.review-modal-bg');
        const closeModal = document.querySelector('.close-modal');
        const ratingStars = document.querySelectorAll('.rating-stars i');
        const submitReviewBtn = document.getElementById('submitReview');

        reviewButtons.forEach(button => {
            button.addEventListener('click', () => {
                const bookingId = button.dataset.booking;
                document.getElementById('reviewTitle').placeholder = `Your experience with booking #${bookingId}`;
                reviewModal.classList.add('active');
            });
        });

        closeModal.addEventListener('click', () => {
            reviewModal.classList.remove('active');
        });

        // Close modal when clicking outside
        reviewModal.addEventListener('click', (e) => {
            if (e.target === reviewModal) {
                reviewModal.classList.remove('active');
            }
        });

        // Star rating functionality
        ratingStars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = parseInt(star.dataset.rating);

                // Reset all stars
                ratingStars.forEach(s => {
                    s.className = 'far fa-star';
                    s.classList.remove('active');
                });

                // Fill stars up to selected rating
                for (let i = 0; i < rating; i++) {
                    ratingStars[i].className = 'fas fa-star active';
                }
            });

            // Hover effect
            star.addEventListener('mouseover', () => {
                const rating = parseInt(star.dataset.rating);

                // Fill stars up to hovered star
                for (let i = 0; i < rating; i++) {
                    if (!ratingStars[i].classList.contains('active')) {
                        ratingStars[i].className = 'fas fa-star';
                    }
                }
            });

            star.addEventListener('mouseout', () => {
                // Reset stars that aren't active
                ratingStars.forEach(s => {
                    if (!s.classList.contains('active')) {
                        s.className = 'far fa-star';
                    }
                });
            });
        });

        // Submit review
        submitReviewBtn.addEventListener('click', () => {
            const activeStars = document.querySelectorAll('.rating-stars i.active');
            const rating = activeStars.length;
            const title = document.getElementById('reviewTitle').value;
            const comment = document.getElementById('reviewComment').value;

            if (rating === 0) {
                alert('Please select a rating');
                return;
            }

            if (!title || !comment) {
                alert('Please fill in all fields');
                return;
            }

            // Show success state
            submitReviewBtn.innerHTML = '<i class="fas fa-check"></i> Review Submitted';
            submitReviewBtn.style.background = '#2F855A';

            // Close modal after delay
            setTimeout(() => {
                reviewModal.classList.remove('active');

                // Reset form
                document.getElementById('reviewTitle').value = '';
                document.getElementById('reviewComment').value = '';
                ratingStars.forEach(s => {
                    s.className = 'far fa-star';
                    s.classList.remove('active');
                });

                // Reset button
                submitReviewBtn.innerHTML = 'Submit Review <i class="fas fa-paper-plane ml-2"></i>';
                submitReviewBtn.style.background = '';

                alert('Thank you for your review!');
            }, 1500);
        });

        // Cancellation Modal Functionality
        const cancellationModal = document.querySelector('.cancellation-modal-bg');
        const cancelButtons = document.querySelectorAll('.cancel-btn');
        let currentBookingId = null;
        let currentCancelButton = null;

        function updateBookingCounts() {
            const allCount = document.querySelector('.booking-tab[data-tab="all"] .count-badge');
            const upcomingCount = document.querySelector('.booking-tab[data-tab="upcoming"] .count-badge');
            const cancelledCount = document.querySelector('.booking-tab[data-tab="cancelled"] .count-badge');

            // Decrement all and upcoming counts
            allCount.textContent = parseInt(allCount.textContent);
            upcomingCount.textContent = parseInt(upcomingCount.textContent) - 1;
            // Increment cancelled count
            cancelledCount.textContent = parseInt(cancelledCount.textContent) + 1;
        }

        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                currentBookingId = this.dataset.booking;
                currentCancelButton = this;
                cancellationModal.classList.add('active');
            });
        });

        // Close modal when clicking outside
        cancellationModal.addEventListener('click', (e) => {
            if (e.target === cancellationModal) {
                cancellationModal.classList.remove('active');
            }
        });

        // Close modal when clicking close button
        document.querySelector('.cancellation-modal .close-modal').addEventListener('click', () => {
            cancellationModal.classList.remove('active');
        });

        // Handle cancel confirmation
        document.querySelector('.cancel-confirm-btn').addEventListener('click', function() {
            if (!currentBookingId || !currentCancelButton) return;

            const bookingCard = currentCancelButton.closest('.booking-card');
            const statusBadge = bookingCard.querySelector('.booking-status');

            // Show cancellation in progress
            currentCancelButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cancelling...';
            currentCancelButton.disabled = true;

            // Send cancellation request
            fetch(`/user/bookings/${currentBookingId}/cancel`, {
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
                    // Update status badge
                    statusBadge.className = 'booking-status cancelled';
                    statusBadge.textContent = 'Cancelled';

                    // Update actions
                    const actionsContainer = currentCancelButton.closest('.booking-actions');
                    const groundId = bookingCard.dataset.groundId || '';

                    actionsContainer.innerHTML = `
                        <div>
                            <a href="${bookingCard.dataset.viewUrl || window.location.href}" class="action-btn view-btn">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </a>
                        </div>
                        <div>
                            <a href="/user/grounds/${groundId}" class="action-btn rebook-btn">
                                <i class="fas fa-redo mr-1"></i> Book Again
                            </a>
                        </div>
                    `;

                    // Update booking category
                    bookingCard.dataset.category = 'cancelled';

                    // Update counts
                    updateBookingCounts();

                    // Show refund information if applicable
                    let refundMessage = '';
                    if (data.refundPercentage > 0) {
                        refundMessage = ` A refund of ₹${data.refundAmount.toFixed(2)} (${data.refundPercentage}%) has been initiated.`;
                    } else {
                        refundMessage = ' No refund was applicable according to cancellation policy.';
                    }

                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'alert alert-success';
                    successMessage.innerHTML = 'Booking cancelled successfully.' + refundMessage;
                    document.querySelector('.my-bookings-container').insertBefore(successMessage, document.querySelector('.booking-list'));

                    // Remove success message after 5 seconds
                    setTimeout(() => {
                        successMessage.remove();
                    }, 5000);
                } else {
                    // Error occurred
                    alert('Error: ' + data.message);
                    currentCancelButton.innerHTML = '<i class="fas fa-times mr-1"></i> Cancel Booking';
                    currentCancelButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while trying to cancel the booking.');
                currentCancelButton.innerHTML = '<i class="fas fa-times mr-1"></i> Cancel Booking';
                currentCancelButton.disabled = false;
            });

            // Close the modal
            cancellationModal.classList.remove('active');
        });

        // Handle cancel dismissal
        document.querySelector('.cancel-dismiss-btn').addEventListener('click', () => {
            cancellationModal.classList.remove('active');
        });

        // Browse grounds button
        document.querySelector('.browse-btn')?.addEventListener('click', () => {
            window.location.href = '/all-grounds';
        });
    });
</script>
@endsection
