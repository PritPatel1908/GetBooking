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
            All Bookings
        </div>
        <div class="booking-tab" data-tab="upcoming">
            Upcoming
            <span class="count-badge">2</span>
        </div>
        <div class="booking-tab" data-tab="completed">
            Completed
        </div>
        <div class="booking-tab" data-tab="cancelled">
            Cancelled
        </div>
    </div>

    <!-- Booking List -->
    <div class="booking-list" data-aos="fade-up" data-aos-delay="200">
        <!-- Upcoming Booking -->
        <div class="booking-card fade-in-delay-1" data-category="upcoming">
            <div class="booking-status confirmed">Confirmed</div>
            <div class="booking-header">
                <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?q=80&w=1200&auto=format&fit=crop"
                    class="booking-image">
                <div>
                    <h3 class="booking-title">Football Ground A</h3>
                    <div class="booking-meta">
                        <div class="booking-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Central Sports Complex, Ahmedabad</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>May 10, 2025</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>18:00 - 20:00</span>
                        </div>
                    </div>
                    <div class="booking-meta-item">
                        <i class="fas fa-id-card"></i>
                        <span>Booking ID: #GB10052025</span>
                    </div>
                </div>
            </div>
            <div class="booking-details">
                <div class="booking-info-row">
                    <div class="booking-info-label">Duration</div>
                    <div class="booking-info-value">2 hours</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Number of Players</div>
                    <div class="booking-info-value">10 players</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Additional Services</div>
                    <div class="booking-info-value">Basic Equipment, Referee</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Payment Method</div>
                    <div class="booking-info-value">Online Payment (UPI)</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Total Amount</div>
                    <div class="booking-price">₹3,800</div>
                </div>
            </div>
            <div class="booking-actions">
                <div>
                    <button class="action-btn view-btn">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </button>
                </div>
                <div>
                    <button class="action-btn cancel-btn">
                        <i class="fas fa-times mr-1"></i> Cancel Booking
                    </button>
                </div>
            </div>
        </div>

        <!-- Another Upcoming Booking -->
        <div class="booking-card fade-in-delay-1" data-category="upcoming">
            <div class="booking-status pending">Payment Pending</div>
            <div class="booking-header">
                <img src="https://images.unsplash.com/photo-1489944440615-453fc2b6a9a9?q=80&w=1200&auto=format&fit=crop"
                    class="booking-image">
                <div>
                    <h3 class="booking-title">Cricket Ground B</h3>
                    <div class="booking-meta">
                        <div class="booking-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Sports Arena, Satellite</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>May 15, 2025</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>15:00 - 18:00</span>
                        </div>
                    </div>
                    <div class="booking-meta-item">
                        <i class="fas fa-id-card"></i>
                        <span>Booking ID: #GB15052025</span>
                    </div>
                </div>
            </div>
            <div class="booking-details">
                <div class="booking-info-row">
                    <div class="booking-info-label">Duration</div>
                    <div class="booking-info-value">3 hours</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Number of Players</div>
                    <div class="booking-info-value">22 players</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Additional Services</div>
                    <div class="booking-info-value">Full Equipment Set</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Payment Method</div>
                    <div class="booking-info-value">Pay at Venue</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Total Amount</div>
                    <div class="booking-price">₹5,200</div>
                </div>
            </div>
            <div class="booking-actions">
                <div>
                    <button class="action-btn view-btn">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </button>
                </div>
                <div>
                    <button class="action-btn cancel-btn">
                        <i class="fas fa-times mr-1"></i> Cancel Booking
                    </button>
                </div>
            </div>
        </div>

        <!-- Completed Booking -->
        <div class="booking-card fade-in-delay-2" data-category="completed">
            <div class="booking-status completed">Completed</div>
            <div class="booking-header">
                <img src="https://images.pexels.com/photos/114296/pexels-photo-114296.jpeg?auto=compress&w=1200"
                    class="booking-image">
                <div>
                    <h3 class="booking-title">Basketball Court C</h3>
                    <div class="booking-meta">
                        <div class="booking-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>City Sports Hub, Vastrapur</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>April 30, 2025</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>19:00 - 21:00</span>
                        </div>
                    </div>
                    <div class="booking-meta-item">
                        <i class="fas fa-id-card"></i>
                        <span>Booking ID: #GB30042025</span>
                    </div>
                </div>
            </div>
            <div class="booking-details">
                <div class="booking-info-row">
                    <div class="booking-info-label">Duration</div>
                    <div class="booking-info-value">2 hours</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Number of Players</div>
                    <div class="booking-info-value">8 players</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Additional Services</div>
                    <div class="booking-info-value">None</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Payment Method</div>
                    <div class="booking-info-value">Credit Card</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Total Amount</div>
                    <div class="booking-price">₹2,800</div>
                </div>
            </div>
            <div class="booking-actions">
                <div>
                    <button class="action-btn view-btn">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </button>
                </div>
                <div>
                    <button class="action-btn review-btn" data-booking="GB30042025">
                        <i class="fas fa-star mr-1"></i> Leave Review
                    </button>
                    <button class="action-btn rebook-btn">
                        <i class="fas fa-redo mr-1"></i> Book Again
                    </button>
                </div>
            </div>
        </div>

        <!-- Cancelled Booking -->
        <div class="booking-card fade-in-delay-2" data-category="cancelled">
            <div class="booking-status cancelled">Cancelled</div>
            <div class="booking-header">
                <img src="https://images.unsplash.com/photo-1624880357913-a8539238245b?q=80&w=1200&auto=format&fit=crop"
                    class="booking-image">
                <div>
                    <h3 class="booking-title">Tennis Court D</h3>
                    <div class="booking-meta">
                        <div class="booking-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Premium Sports Club, Bopal</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>April 25, 2025</span>
                        </div>
                        <div class="booking-meta-item">
                            <i class="fas fa-clock"></i>
                            <span>09:00 - 11:00</span>
                        </div>
                    </div>
                    <div class="booking-meta-item">
                        <i class="fas fa-id-card"></i>
                        <span>Booking ID: #GB25042025</span>
                    </div>
                </div>
            </div>
            <div class="booking-details">
                <div class="booking-info-row">
                    <div class="booking-info-label">Duration</div>
                    <div class="booking-info-value">2 hours</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Number of Players</div>
                    <div class="booking-info-value">4 players</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Cancellation Reason</div>
                    <div class="booking-info-value">Weather conditions</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Refund Status</div>
                    <div class="booking-info-value">Refunded (May 1, 2025)</div>
                </div>
                <div class="booking-info-row">
                    <div class="booking-info-label">Total Amount</div>
                    <div class="booking-price">₹2,100</div>
                </div>
            </div>
            <div class="booking-actions">
                <div>
                    <button class="action-btn view-btn">
                        <i class="fas fa-eye mr-1"></i> View Details
                    </button>
                </div>
                <div>
                    <button class="action-btn rebook-btn">
                        <i class="fas fa-redo mr-1"></i> Book Again
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State (Hidden by default) -->
        <div class="empty-state" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3 class="empty-title">No bookings found</h3>
            <p class="empty-subtitle">You haven't made any bookings yet. Start by exploring our available grounds.</p>
            <button class="browse-btn">
                <i class="fas fa-search mr-2"></i> Browse Grounds
            </button>
        </div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
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

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));

                // Add active class to clicked tab
                tab.classList.add('active');

                // Get the category
                const category = tab.dataset.tab;

                // Hide all booking cards
                bookingCards.forEach(card => {
                    card.style.display = 'none';
                });

                // If category is 'all', show all cards
                if (category === 'all') {
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

                    filteredCards.forEach(card => {
                        card.style.display = 'block';
                    });

                    // If no cards match the category, show empty state
                    if (filteredCards.length === 0) {
                        emptyState.style.display = 'block';
                    } else {
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

        // Cancel booking functionality
        const cancelButtons = document.querySelectorAll('.cancel-btn');

        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                const confirmed = confirm('Are you sure you want to cancel this booking? Cancellation policies may apply.');

                if (confirmed) {
                    const bookingCard = this.closest('.booking-card');
                    const statusBadge = bookingCard.querySelector('.booking-status');

                    // Show cancellation in progress
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cancelling...';

                    // Simulate cancellation process
                    setTimeout(() => {
                        statusBadge.className = 'booking-status cancelled';
                        statusBadge.textContent = 'Cancelled';

                        // Update actions
                        const actionsContainer = this.closest('.booking-actions');
                        actionsContainer.innerHTML = `
                            <div>
                                <button class="action-btn view-btn">
                                    <i class="fas fa-eye mr-1"></i> View Details
                                </button>
                            </div>
                            <div>
                                <button class="action-btn rebook-btn">
                                    <i class="fas fa-redo mr-1"></i> Book Again
                                </button>
                            </div>
                        `;

                        // Update booking category
                        bookingCard.dataset.category = 'cancelled';

                        alert('Booking cancelled successfully. A confirmation email has been sent to your registered email address.');
                    }, 1500);
                }
            });
        });

        // Browse grounds button
        document.querySelector('.browse-btn')?.addEventListener('click', () => {
            window.location.href = '/all-grounds';
        });
    });
</script>
@endsection
