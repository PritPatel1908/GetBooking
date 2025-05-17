<!-- filepath: d:\WebProjects\Laravel\Personal\GetBooking\resources\views\layouts\user-modern.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    @yield('styles')
</head>

<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="https://img.icons8.com/color/48/000000/basketball.png" alt="GetBooking Logo">
                    <span>GetBooking</span>
                </div>

                <ul class="nav-links">
                    <li><a href="{{ route('user.home') }}"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="#sports-grounds"><i class="fas fa-map-marker-alt"></i> Sports Grounds</a></li>
                    {{-- <li><a href="#tournaments"><i class="fas fa-trophy"></i> Tournaments</a></li> --}}
                    <li><a href="{{ route('user.my_bookings') }}"><i class="fas fa-calendar-alt"></i> My Bookings</a></li>
                    <li><a href="#"><i class="fas fa-info-circle"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-phone"></i> Contact</a></li>
                    <li><a href="{{ route('user.profile') }}"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
                </ul>

                <div class="theme-toggle" id="theme-toggle">
                    <span class="sun">☀️</span>
                    <span class="moon">🌙</span>
                    <div class="toggle-ball"></div>
                </div>

                <div class="mobile-menu">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    @yield('content')

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
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Footer Section 2 -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="#sports-grounds"><i class="fas fa-chevron-right"></i> Sports Grounds</a></li>
                        {{-- <li><a href="#tournaments"><i class="fas fa-chevron-right"></i> Tournaments</a></li> --}}
                        <li><a href="#"><i class="fas fa-chevron-right"></i> My Bookings</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Contact</a></li>
                    </ul>
                </div>

                <!-- Footer Section 3 -->
                <div class="footer-section">
                    <h3>Sports Categories</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-futbol"></i> Football</a></li>
                        <li><a href="#"><i class="fas fa-basketball-ball"></i> Basketball</a></li>
                        <li><a href="#"><i class="fas fa-table-tennis"></i> Table Tennis</a></li>
                        <li><a href="#"><i class="fas fa-baseball-ball"></i> Cricket</a></li>
                        <li><a href="#"><i class="fas fa-volleyball-ball"></i> Volleyball</a></li>
                        <li><a href="#"><i class="fas fa-running"></i> Badminton</a></li>
                    </ul>
                </div>

                <!-- Footer Section 4 -->
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-map-marker-alt"></i> 123 Sports Street, City</a></li>
                        <li><a href="tel:+12345678900"><i class="fas fa-phone"></i> +1 234 567 8900</a></li>
                        <li><a href="mailto:info@getbooking.com"><i class="fas fa-envelope"></i> info@getbooking.com</a>
                        </li>
                    </ul>

                    <div class="newsletter">
                        <h3>Subscribe to Newsletter</h3>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Your email address">
                            <button type="submit" class="btn">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2023 GetBooking. All rights reserved. | Designed with <i class="fas fa-heart"
                        style="color: #e74c3c;"></i> by GetBooking Team</p>
            </div>
        </div>
    </footer>

    <!-- Go to top button -->
    <div class="go-top" id="goTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}

    <!-- Custom JS -->
    <script src="{{ asset('assets/user/js/main.js') }}"></script>

    <!-- Page-specific scripts -->
    @yield('scripts')
</body>

</html>
