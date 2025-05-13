<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GetBooking - Sports Ground Booking</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700|instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <!-- AOS Animation Library -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <link rel="stylesheet" href="{{ asset('assets/welcome/css/style.css') }}">

        <script src="{{ asset('assets/welcome/js/before_call.js')}}"></script>
    </head>
    <body class="font-sans antialiased text-gray-800 bg-gray-50">
        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <a href="{{ url('/') }}" class="flex items-center">
                            <span class="text-2xl font-bold text-emerald-600">Get<span class="text-blue-600">Booking</span></span>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="#grounds" class="text-gray-700 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition duration-300">Grounds</a>
                        <a href="#tournaments" class="text-gray-700 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition duration-300">Tournaments</a>
                        <a href="#features" class="text-gray-700 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition duration-300">Features</a>
                        <a href="#testimonials" class="text-gray-700 hover:text-emerald-600 px-3 py-2 rounded-md text-sm font-medium transition duration-300">Testimonials</a>

                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-300">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition duration-300">Log in</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="ml-4 px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition duration-300">Register</a>
                                @endif
                            @endauth
                        @endif
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button id="mobile-menu-button" class="p-2 rounded-md text-gray-700 hover:text-gray-900 focus:outline-none">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div id="mobile-menu" class="hidden md:hidden pb-3">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                        <a href="#grounds" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100">Grounds</a>
                        <a href="#tournaments" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100">Tournaments</a>
                        <a href="#features" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100">Features</a>
                        <a href="#testimonials" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100">Testimonials</a>

                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="block mt-3 px-3 py-2 rounded-md text-base font-medium bg-blue-600 text-white">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="block mt-3 px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100">Log in</a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="block mt-3 px-3 py-2 rounded-md text-base font-medium bg-emerald-600 text-white">Register</a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-16 bg-gradient-to-b from-blue-500 to-emerald-500 overflow-hidden">
            <div class="absolute inset-0 bg-black/30"></div>
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1508098682722-e99c643e7f66?q=80&w=2070')] bg-cover bg-center mix-blend-overlay"></div>
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32 relative">
                <div class="max-w-3xl" data-aos="fade-up" data-aos-duration="1000">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-4">
                        Book Your Perfect Sports Ground
                    </h1>
                    <p class="text-lg md:text-xl text-white/90 mb-8">
                        Find and book the best sports grounds for football, cricket, basketball, tennis and more. Join tournaments or create your own!
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#grounds" class="inline-flex justify-center items-center px-6 py-3 bg-emerald-600 text-white font-medium rounded-lg shadow-lg hover:bg-emerald-700 transition duration-300 transform hover:scale-105">
                            Explore Grounds
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#tournaments" class="inline-flex justify-center items-center px-6 py-3 bg-white text-blue-600 font-medium rounded-lg shadow-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                            Join Tournaments
                        </a>
                    </div>
                </div>
            </div>
            <!-- Wave Separator -->
            <div class="absolute bottom-0 left-0 right-0 h-12 md:h-16 lg:h-20">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none" class="h-full w-full">
                    <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="#f9fafb" opacity=".8"></path>
                    <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="#f9fafb" opacity=".5"></path>
                    <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="#f9fafb"></path>
                </svg>
            </div>
        </section>

        <!-- Popular Sports Grounds Section -->
        <section id="grounds" class="py-12 md:py-20 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12" data-aos="fade-up">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Popular Sports Grounds</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Discover and book the best sports facilities in your area. All grounds are well-maintained and equipped with modern amenities.
                    </p>
                </div>

                <!-- Grounds Filter -->
                <div class="flex flex-wrap justify-center gap-3 mb-10" data-aos="fade-up" data-aos-delay="100">
                    <button class="ground-filter-btn active px-4 py-2 rounded-full bg-emerald-100 text-emerald-600 font-medium hover:bg-emerald-200 transition duration-300">
                        All Grounds
                    </button>
                    <button class="ground-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition duration-300">
                        Football
                    </button>
                    <button class="ground-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition duration-300">
                        Cricket
                    </button>
                    <button class="ground-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition duration-300">
                        Basketball
                    </button>
                    <button class="ground-filter-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition duration-300">
                        Tennis
                    </button>
                </div>

                <!-- Grounds Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Ground Card 1 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="150">
                        <div class="relative h-56 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1543326727-cf6c39e8f84c?q=80&w=2070" alt="Football Ground" class="object-cover w-full h-full">
                            <div class="absolute top-4 left-4 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                Football
                            </div>
                            <div class="absolute top-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                4.9 ★
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Emerald Football Arena</h3>
                            <div class="flex items-center text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt mr-2 text-emerald-500"></i>
                                <span>Central Park, South Delhi</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-gray-600">
                                    <i class="fas fa-users mr-2 text-blue-500"></i>
                                    <span>5v5, 7v7, 11v11</span>
                                </div>
                                <div class="text-gray-600">
                                    <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                                    <span>Flood Lights</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-emerald-600">₹1,200/hr</div>
                                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Book Now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Ground Card 2 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="200">
                        <div class="relative h-56 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1589661329742-713c46926e34?q=80&w=2070" alt="Cricket Ground" class="object-cover w-full h-full">
                            <div class="absolute top-4 left-4 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                Cricket
                            </div>
                            <div class="absolute top-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                4.7 ★
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Royal Cricket Stadium</h3>
                            <div class="flex items-center text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt mr-2 text-emerald-500"></i>
                                <span>Sports Complex, Noida</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-gray-600">
                                    <i class="fas fa-cricket-ball mr-2 text-red-500"></i>
                                    <span>Turf Wicket</span>
                                </div>
                                <div class="text-gray-600">
                                    <i class="fas fa-shower mr-2 text-blue-500"></i>
                                    <span>Changing Rooms</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-emerald-600">₹1,500/hr</div>
                                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Book Now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Ground Card 3 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="250">
                        <div class="relative h-56 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1518775053278-5a569f0be353?q=80&w=2070" alt="Basketball Court" class="object-cover w-full h-full">
                            <div class="absolute top-4 left-4 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                Basketball
                            </div>
                            <div class="absolute top-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                4.8 ★
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Slam Dunk Court</h3>
                            <div class="flex items-center text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt mr-2 text-emerald-500"></i>
                                <span>Urban Sports Park, Gurgaon</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-gray-600">
                                    <i class="fas fa-basketball-ball mr-2 text-orange-500"></i>
                                    <span>Indoor Court</span>
                                </div>
                                <div class="text-gray-600">
                                    <i class="fas fa-wifi mr-2 text-blue-500"></i>
                                    <span>Free WiFi</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-emerald-600">₹900/hr</div>
                                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Book Now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Ground Card 4 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="300">
                        <div class="relative h-56 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1526232373132-0e4ee643fa17?q=80&w=2029" alt="Tennis Court" class="object-cover w-full h-full">
                            <div class="absolute top-4 left-4 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                Tennis
                            </div>
                            <div class="absolute top-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                4.9 ★
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Grand Slam Tennis Club</h3>
                            <div class="flex items-center text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt mr-2 text-emerald-500"></i>
                                <span>Luxury Sports Club, South Delhi</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-gray-600">
                                    <i class="fas fa-tennis-ball mr-2 text-yellow-500"></i>
                                    <span>Hard Court</span>
                                </div>
                                <div class="text-gray-600">
                                    <i class="fas fa-coffee mr-2 text-brown-500"></i>
                                    <span>Café Available</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-emerald-600">₹1,000/hr</div>
                                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Book Now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Ground Card 5 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="350">
                        <div class="relative h-56 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1551958219-acbc608c6377?q=80&w=2070" alt="Football Ground" class="object-cover w-full h-full">
                            <div class="absolute top-4 left-4 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                Football
                            </div>
                            <div class="absolute top-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                4.6 ★
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Victory Football Ground</h3>
                            <div class="flex items-center text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt mr-2 text-emerald-500"></i>
                                <span>Victory Sports Complex, Ghaziabad</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-gray-600">
                                    <i class="fas fa-users mr-2 text-blue-500"></i>
                                    <span>5v5, 7v7</span>
                                </div>
                                <div class="text-gray-600">
                                    <i class="fas fa-parking mr-2 text-blue-500"></i>
                                    <span>Free Parking</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-emerald-600">₹1,100/hr</div>
                                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Book Now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Ground Card 6 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="400">
                        <div class="relative h-56 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1562136230-8df39f6e33f8?q=80&w=2086" alt="Cricket Ground" class="object-cover w-full h-full">
                            <div class="absolute top-4 left-4 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                Cricket
                            </div>
                            <div class="absolute top-4 right-4 bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                4.8 ★
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Premier Cricket Academy</h3>
                            <div class="flex items-center text-gray-600 mb-3">
                                <i class="fas fa-map-marker-alt mr-2 text-emerald-500"></i>
                                <span>Sports Village, Greater Noida</span>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-gray-600">
                                    <i class="fas fa-cricket-ball mr-2 text-red-500"></i>
                                    <span>Practice Nets</span>
                                </div>
                                <div class="text-gray-600">
                                    <i class="fas fa-user-tie mr-2 text-gray-500"></i>
                                    <span>Coach Available</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-emerald-600">₹1,300/hr</div>
                                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-10" data-aos="fade-up" data-aos-delay="450">
                    <a href="#" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                        <span>View All Grounds</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Upcoming Tournaments Section -->
        <section id="tournaments" class="py-12 md:py-20 bg-blue-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12" data-aos="fade-up">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Upcoming Tournaments</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Join exciting sports competitions or organize your own tournament. Build your team and compete with the best.
                    </p>
                </div>

                <!-- Tournament Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Tournament Card 1 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="100">
                        <div class="relative h-44 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1575361204480-aadea25e6e68?q=80&w=2071" alt="Football Tournament" class="object-cover w-full h-full">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <div class="text-xs font-medium uppercase mb-1">Football</div>
                                <h3 class="text-xl font-bold">Summer League Championship</h3>
                            </div>
                            <div class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                Registration Open
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="far fa-calendar mr-2 text-blue-500"></i>
                                    <span>15 May - 30 June, 2025</span>
                                </div>
                                <div class="text-sm font-medium text-emerald-600">24 Teams</div>
                            </div>
                            <div class="flex items-center text-gray-600 mb-4">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                <span>Multiple Venues, Delhi NCR</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-gray-900">₹8,000<span class="text-sm font-normal text-gray-600">/team</span></div>
                                <a href="#" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition duration-300">Register Now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Tournament Card 2 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="150">
                        <div class="relative h-44 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1531415074968-036ba1b575da?q=80&w=2067" alt="Cricket Tournament" class="object-cover w-full h-full">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <div class="text-xs font-medium uppercase mb-1">Cricket</div>
                                <h3 class="text-xl font-bold">T20 Corporate Shield</h3>
                            </div>
                            <div class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                Registration Open
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="far fa-calendar mr-2 text-blue-500"></i>
                                    <span>10 June - 25 July, 2025</span>
                                </div>
                                <div class="text-sm font-medium text-emerald-600">16 Teams</div>
                            </div>
                            <div class="flex items-center text-gray-600 mb-4">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                <span>Royal Cricket Stadium, Noida</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-gray-900">₹12,000<span class="text-sm font-normal text-gray-600">/team</span></div>
                                <a href="#" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition duration-300">Register Now</a>
                            </div>
                        </div>
                    </div>

                    <!-- Tournament Card 3 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="200">
                        <div class="relative h-44 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1548188500-ab08449b26d7?q=80&w=2071" alt="Basketball Tournament" class="object-cover w-full h-full">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <div class="text-xs font-medium uppercase mb-1">Basketball</div>
                                <h3 class="text-xl font-bold">Urban Streetball Challenge</h3>
                            </div>
                            <div class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                Registration Open
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center text-gray-600">
                                    <i class="far fa-calendar mr-2 text-blue-500"></i>
                                    <span>5 May - 10 May, 2025</span>
                                </div>
                                <div class="text-sm font-medium text-emerald-600">32 Teams</div>
                            </div>
                            <div class="flex items-center text-gray-600 mb-4">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                <span>Urban Sports Park, Gurgaon</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-lg font-bold text-gray-900">₹6,000<span class="text-sm font-normal text-gray-600">/team</span></div>
                                <a href="#" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition duration-300">Register Now</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-10" data-aos="fade-up" data-aos-delay="250">
                    <a href="#" class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                        <span>View All Tournaments</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-12 md:py-20 bg-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16" data-aos="fade-up">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose GetBooking</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        We provide the easiest way to find and book sports facilities near you with exclusive features and benefits.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12">
                    <!-- Feature 1 -->
                    <div class="flex flex-col items-center text-center" data-aos="fade-up" data-aos-delay="100">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-6">
                            <i class="fas fa-search text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Easy Search & Booking</h3>
                        <p class="text-gray-600">
                            Find the perfect sports ground in seconds with our advanced search filters. Book instantly without any hassle.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex flex-col items-center text-center" data-aos="fade-up" data-aos-delay="150">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mb-6">
                            <i class="fas fa-trophy text-emerald-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Join Tournaments</h3>
                        <p class="text-gray-600">
                            Participate in exciting tournaments across various sports or create your own tournament in minutes.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex flex-col items-center text-center" data-aos="fade-up" data-aos-delay="200">
                        <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mb-6">
                            <i class="fas fa-users text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Team Management</h3>
                        <p class="text-gray-600">
                            Create and manage your teams, invite players, and organize practice sessions with just a few clicks.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="flex flex-col items-center text-center" data-aos="fade-up" data-aos-delay="250">
                        <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mb-6">
                            <i class="fas fa-star text-yellow-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Verified Reviews</h3>
                        <p class="text-gray-600">
                            Read authentic reviews from other players to find the best quality grounds in your area.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="flex flex-col items-center text-center" data-aos="fade-up" data-aos-delay="300">
                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-6">
                            <i class="fas fa-wallet text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Secure Payments</h3>
                        <p class="text-gray-600">
                            Multiple payment options with secure gateways. Get instant booking confirmations and receipts.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="flex flex-col items-center text-center" data-aos="fade-up" data-aos-delay="350">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mb-6">
                            <i class="fas fa-mobile-alt text-indigo-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Mobile App</h3>
                        <p class="text-gray-600">
                            Manage all your bookings on the go with our easy-to-use mobile app available for iOS and Android.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="py-12 md:py-20 bg-gray-50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12" data-aos="fade-up">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">What Our Users Say</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Don't just take our word for it. See what players and teams have to say about their GetBooking experience.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Testimonial 1 -->
                    <div class="bg-white rounded-xl shadow-md p-6 relative" data-aos="fade-up" data-aos-delay="100">
                        <div class="absolute -top-5 left-6">
                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white">
                                <i class="fas fa-quote-right"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-gray-600 mb-6">
                                "GetBooking has transformed how our football team practices. Finding and booking grounds is now a breeze, and the transparent pricing helps us plan our budget effectively."
                            </p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="w-12 h-12 rounded-full object-cover">
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">Rahul Sharma</h4>
                                    <div class="text-sm text-gray-600">Football Team Captain</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="bg-white rounded-xl shadow-md p-6 relative" data-aos="fade-up" data-aos-delay="150">
                        <div class="absolute -top-5 left-6">
                            <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white">
                                <i class="fas fa-quote-right"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-gray-600 mb-6">
                                "As a cricket coach, I've organized several tournaments through GetBooking. The platform's tournament management tools saved me countless hours of administrative work."
                            </p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="w-12 h-12 rounded-full object-cover">
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">Priya Patel</h4>
                                    <div class="text-sm text-gray-600">Cricket Coach</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="bg-white rounded-xl shadow-md p-6 relative" data-aos="fade-up" data-aos-delay="200">
                        <div class="absolute -top-5 left-6">
                            <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center text-white">
                                <i class="fas fa-quote-right"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-gray-600 mb-6">
                                "I run a basketball academy and GetBooking has made court reservations incredibly simple. The customer service is phenomenal, and the app interface is intuitive."
                            </p>
                            <div class="flex items-center">
                                <img src="https://randomuser.me/api/portraits/men/67.jpg" alt="User" class="w-12 h-12 rounded-full object-cover">
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-gray-900">Ajay Verma</h4>
                                    <div class="text-sm text-gray-600">Basketball Academy Owner</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-12 md:py-20 bg-gradient-to-r from-blue-600 to-emerald-600 text-white">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-8 lg:mb-0 lg:w-2/3" data-aos="fade-right">
                        <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to Find Your Perfect Sports Venue?</h2>
                        <p class="text-lg text-white/90 max-w-2xl">
                            Join thousands of sports enthusiasts who find and book sports grounds with ease. Register now to get started!
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4" data-aos="fade-left">
                        <a href="#" class="px-6 py-3 bg-white text-blue-600 font-medium rounded-lg shadow-lg hover:bg-gray-100 transition duration-300 transform hover:scale-105 text-center">
                            Get Started Now
                        </a>
                        <a href="#" class="px-6 py-3 bg-transparent border-2 border-white text-white font-medium rounded-lg hover:bg-white/10 transition duration-300 transform hover:scale-105 text-center">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white pt-16 pb-8">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                    <!-- Company Info -->
                    <div>
                        <h3 class="text-xl font-bold mb-4">GetBooking</h3>
                        <p class="text-gray-400 mb-4">
                            The easiest way to find and book sports facilities near you. Join our community of sports enthusiasts today!
                        </p>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Find Grounds</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Tournaments</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Team Management</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">About Us</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Contact Us</a></li>
                        </ul>
                    </div>

                    <!-- Sports -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Sports</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Football</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Cricket</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Basketball</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Tennis</a></li>
                            <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">All Sports</a></li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li class="flex items-start">
                                <i class="fas fa-map-marker-alt mt-1 mr-3"></i>
                                <span>123 Sports Lane, Delhi NCR, India</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-phone-alt mr-3"></i>
                                <span>+91 98765 43210</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-envelope mr-3"></i>
                                <span>info@getbooking.com</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="pt-8 border-t border-gray-800 text-center text-gray-400 text-sm">
                    <p>&copy; {{ date('Y') }} GetBooking. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <script src="{{ asset('assets/welcome/js/main.js')}}"></script>
        <script src="{{ asset('assets/welcome/js/ajax.js')}}"></script>
    </body>
</html>
