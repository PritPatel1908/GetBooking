<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="GetBooking - The easiest way to book sports facilities near you">
    <meta name="theme-color" content="#3490dc">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    <title>GetBooking - Sports Ground Booking</title>

    {{-- <!-- Bootstrap CSS -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> --}}

    <!-- Google Fonts -->
    {{-- <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"> --}}

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/user/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-fixes.css') }}">
    @yield('styles')
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="https://img.icons8.com/color/48/000000/basketball.png" alt="GetBooking Logo" width="48" height="48">
                    <span>GetBooking</span>
                </div>

                <ul class="nav-links" id="main-nav">
                    <li><a href="{{ route('user.home') }}"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <li><a href="#sports-grounds"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Sports Grounds</a></li>
                    {{-- <li><a href="#tournaments"><i class="fas fa-trophy"></i> Tournaments</a></li> --}}
                    <li><a href="{{ route('user.my_bookings') }}"><i class="fas fa-calendar-alt" aria-hidden="true"></i> My Bookings</a></li>
                    <li><a href="#"><i class="fas fa-info-circle" aria-hidden="true"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-phone" aria-hidden="true"></i> Contact</a></li>
                    <li><a href="{{ route('user.profile') }}"><i class="fas fa-user" aria-hidden="true"></i> Profile</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Log Out</a></li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </ul>

                <div class="theme-toggle" id="theme-toggle" role="switch" aria-checked="false" tabindex="0" aria-label="Toggle dark mode">
                    <span class="sun">☀️</span>
                    <span class="moon">🌙</span>
                    <div class="toggle-ball"></div>
                </div>

                <button class="mobile-menu" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="main-nav">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            </nav>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <!-- Footer Section 1 -->
                <div class="footer-section">
                    <h3>About GetBooking</h3>
                    <p>The ultimate platform for sports enthusiasts to discover, book, and play at the best sports
                        facilities in your area.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                    </div>
                </div>

                <!-- Footer Section 2 -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right" aria-hidden="true"></i> Home</a></li>
                        <li><a href="#sports-grounds"><i class="fas fa-chevron-right" aria-hidden="true"></i> Sports Grounds</a></li>
                        {{-- <li><a href="#tournaments"><i class="fas fa-chevron-right"></i> Tournaments</a></li> --}}
                        <li><a href="#"><i class="fas fa-chevron-right" aria-hidden="true"></i> My Bookings</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right" aria-hidden="true"></i> About Us</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right" aria-hidden="true"></i> Contact</a></li>
                    </ul>
                </div>

                <!-- Footer Section 3 -->
                <div class="footer-section">
                    <h3>Sports Categories</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-futbol" aria-hidden="true"></i> Football</a></li>
                        <li><a href="#"><i class="fas fa-basketball-ball" aria-hidden="true"></i> Basketball</a></li>
                        <li><a href="#"><i class="fas fa-table-tennis" aria-hidden="true"></i> Table Tennis</a></li>
                        <li><a href="#"><i class="fas fa-baseball-ball" aria-hidden="true"></i> Cricket</a></li>
                        <li><a href="#"><i class="fas fa-volleyball-ball" aria-hidden="true"></i> Volleyball</a></li>
                        <li><a href="#"><i class="fas fa-running" aria-hidden="true"></i> Badminton</a></li>
                    </ul>
                </div>

                <!-- Footer Section 4 -->
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> 123 Sports Street, City</a></li>
                        <li><a href="tel:+12345678900"><i class="fas fa-phone" aria-hidden="true"></i> +1 234 567 8900</a></li>
                        <li><a href="mailto:info@getbooking.com"><i class="fas fa-envelope" aria-hidden="true"></i> info@getbooking.com</a>
                        </li>
                    </ul>

                    <div class="newsletter">
                        <h3>Subscribe to Newsletter</h3>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Your email address" aria-label="Email for newsletter">
                            <button type="submit" class="btn" aria-label="Subscribe to newsletter">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} GetBooking. All rights reserved. | Designed with <i class="fas fa-heart"
                        style="color: #e74c3c;" aria-hidden="true"></i> by GetBooking Team</p>
            </div>
        </div>
    </footer>

    <!-- Go to top button -->
    <button class="go-top" id="goTop" aria-label="Go to top">
        <i class="fas fa-arrow-up" aria-hidden="true"></i>
    </button>

    <!-- Bootstrap Bundle with Popper -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}

    <!-- Custom JS -->
    <script src="{{ asset('assets/user/js/main.js') }}"></script>
    <script src="{{ asset('js/responsive-fixes.js') }}"></script>

    <!-- Page-specific scripts -->
    @yield('scripts')
</body>

</html>
