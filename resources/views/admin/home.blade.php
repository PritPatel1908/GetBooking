@extends('layouts.admin')

{{-- @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
@endsection --}}
@section('title', 'Dashboard')
@section('content')
    <main class="flex-1 overflow-y-auto p-2 sm:p-4 page-transition">
        <div class="space-y-4 sm:space-y-6">
            <div class="mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 animate-fade-in">Namaste, Admin!</h2>
                <p class="text-sm sm:text-base text-gray-600 animate-fade-in" style="animation-delay: 0.1s">Here's what's happening
                    with your facilities today.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border-l-4 border-orange-500 transform transition-all duration-300 hover:scale-105 hover:shadow-md animate-slide-up"
                    style="animation-delay: 0.1s">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-xs sm:text-sm">Total Clients</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1 text-gray-800">142</h3>
                            <p class="text-green-500 text-xs sm:text-sm mt-2 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <span>12% increase</span>
                            </p>
                        </div>
                        <div class="h-10 w-10 sm:h-14 sm:w-14 rounded-full bg-orange-100 flex items-center justify-center">
                            <i class="fas fa-om text-orange-500 text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border-l-4 border-green-500 transform transition-all duration-300 hover:scale-105 hover:shadow-md animate-slide-up"
                    style="animation-delay: 0.2s">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-xs sm:text-sm">Total Bookings</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1 text-gray-800">856</h3>
                            <p class="text-green-500 text-xs sm:text-sm mt-2 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <span>18% increase</span>
                            </p>
                        </div>
                        <div class="h-10 w-10 sm:h-14 sm:w-14 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-dharmachakra text-green-500 text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border-l-4 border-purple-500 transform transition-all duration-300 hover:scale-105 hover:shadow-md animate-slide-up"
                    style="animation-delay: 0.3s">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-xs sm:text-sm">Total Revenue</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1 text-gray-800">₹24,500</h3>
                            <p class="text-green-500 text-xs sm:text-sm mt-2 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <span>7% increase</span>
                            </p>
                        </div>
                        <div class="h-10 w-10 sm:h-14 sm:w-14 rounded-full bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-rupee-sign text-purple-500 text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 border-l-4 border-red-500 transform transition-all duration-300 hover:scale-105 hover:shadow-md animate-slide-up"
                    style="animation-delay: 0.4s">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500 text-xs sm:text-sm">Active Tournaments</p>
                            <h3 class="text-xl sm:text-3xl font-bold mt-1 text-gray-800">12</h3>
                            <p class="text-green-500 text-xs sm:text-sm mt-2 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i>
                                <span>3 new this month</span>
                            </p>
                        </div>
                        <div class="h-10 w-10 sm:h-14 sm:w-14 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="fas fa-khanda text-red-500 text-lg sm:text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in" style="animation-delay: 0.5s">
                <div class="p-3 sm:p-4 border-b flex justify-between items-center">
                    <h2 class="text-lg sm:text-xl font-semibold">Recent Bookings</h2>
                    <a href="bookings.html"
                        class="text-indigo-600 hover:text-indigo-800 text-xs sm:text-sm font-medium flex items-center">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="p-2 sm:p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-responsive-card">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">
                                        Client
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell sortable">
                                        Ground
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">
                                        Date
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell sortable">
                                        Time
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable">
                                        Status
                                    </th>
                                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr class="hover:bg-gray-50 transition-colors table-row-appear"
                                    style="animation-delay: 0.1s">
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-6 w-6 sm:h-8 sm:w-8 rounded-full mr-2"
                                                src="https://randomuser.me/api/portraits/men/2.jpg" alt="">
                                            <span class="text-sm">Raj Sharma</span>
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden sm:table-cell text-sm">Delhi Cricket Ground</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm">18 Aug 2023</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden md:table-cell text-sm">15:00 - 17:00</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium status-badge success">
                                            Confirmed
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <button class="text-blue-500 hover:text-blue-700 transition-colors action-button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-500 hover:text-green-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-500 hover:text-red-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition-colors table-row-appear"
                                    style="animation-delay: 0.15s">
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-6 w-6 sm:h-8 sm:w-8 rounded-full mr-2"
                                                src="https://randomuser.me/api/portraits/women/3.jpg" alt="">
                                            <span class="text-sm">Priya Patel</span>
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden sm:table-cell text-sm">Mumbai Tennis Court</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm">19 Aug 2023</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden md:table-cell text-sm">10:00 - 12:00</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium status-badge warning">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <button class="text-blue-500 hover:text-blue-700 transition-colors action-button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-500 hover:text-green-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-500 hover:text-red-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition-colors table-row-appear"
                                    style="animation-delay: 0.2s">
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-6 w-6 sm:h-8 sm:w-8 rounded-full mr-2"
                                                src="https://randomuser.me/api/portraits/men/4.jpg" alt="">
                                            <span class="text-sm">Arjun Singh</span>
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden sm:table-cell text-sm">Bangalore Basketball Court</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm">20 Aug 2023</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden md:table-cell text-sm">14:00 - 16:00</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium status-badge success">
                                            Confirmed
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <button class="text-blue-500 hover:text-blue-700 transition-colors action-button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-500 hover:text-green-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-500 hover:text-red-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 transition-colors table-row-appear"
                                    style="animation-delay: 0.25s">
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img class="h-6 w-6 sm:h-8 sm:w-8 rounded-full mr-2"
                                                src="https://randomuser.me/api/portraits/women/5.jpg" alt="">
                                            <span class="text-sm">Ananya Mishra</span>
                                        </div>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden sm:table-cell text-sm">Chennai Cricket Ground</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm">21 Aug 2023</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap hidden md:table-cell text-sm">09:00 - 12:00</td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-medium status-badge danger">
                                            Cancelled
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <button class="text-blue-500 hover:text-blue-700 transition-colors action-button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-500 hover:text-green-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-500 hover:text-red-700 transition-colors action-button hidden sm:inline-block">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 table-pagination">
                        <div class="text-sm text-gray-500">Showing 4 of 856 bookings</div>
                        <div>
                            <button class="px-3 py-1 bg-indigo-100 text-indigo-600 rounded-md text-sm font-medium">
                                Load More <i class="fas fa-chevron-down ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity & Calendar Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in" style="animation-delay: 0.6s">
                    <div class="p-3 sm:p-4 border-b">
                        <h2 class="text-lg sm:text-xl font-semibold">Recent Activity</h2>
                    </div>
                    <div class="p-3 sm:p-4">
                        <ul class="space-y-3 sm:space-y-4">
                            <li class="flex items-start space-x-2 sm:space-x-3">
                                <div class="bg-orange-100 text-orange-600 p-1.5 sm:p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-hands-praying text-sm sm:text-base"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-sm sm:text-base">New client registered</p>
                                    <p class="text-xs sm:text-sm text-gray-500">Vikram Mehta registered as a new client
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-2 sm:space-x-3">
                                <div class="bg-green-100 text-green-600 p-1.5 sm:p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-peace text-sm sm:text-base"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-sm sm:text-base">Booking confirmed</p>
                                    <p class="text-xs sm:text-sm text-gray-500">Tennis Court #3 booked by Priya Patel</p>
                                    <p class="text-xs text-gray-400 mt-1">3 hours ago</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-2 sm:space-x-3">
                                <div class="bg-purple-100 text-purple-600 p-1.5 sm:p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-rupee-sign text-sm sm:text-base"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-sm sm:text-base">Payment received</p>
                                    <p class="text-xs sm:text-sm text-gray-500">₹8,500 received from Arjun Singh</p>
                                    <p class="text-xs text-gray-400 mt-1">5 hours ago</p>
                                </div>
                            </li>
                            <li class="flex items-start space-x-2 sm:space-x-3">
                                <div class="bg-red-100 text-red-600 p-1.5 sm:p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-lotus text-sm sm:text-base"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-sm sm:text-base">Tournament created</p>
                                    <p class="text-xs sm:text-sm text-gray-500">IPL-Style Cricket Tournament created</p>
                                    <p class="text-xs text-gray-400 mt-1">Yesterday</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Popular Grounds -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-fade-in" style="animation-delay: 0.7s">
                    <div class="p-3 sm:p-4 border-b">
                        <h2 class="text-lg sm:text-xl font-semibold">Popular Grounds</h2>
                    </div>
                    <div class="p-3 sm:p-4">
                        <div class="space-y-3 sm:space-y-4">
                            <div class="flex items-center">
                                <div class="w-1/4">
                                    <img src="img/ground1.jpg" alt="Ground" class="h-12 w-12 sm:h-16 sm:w-16 rounded-lg object-cover"
                                        onerror="this.src='https://placehold.co/64x64?text=🏏'">
                                </div>
                                <div class="w-2/4">
                                    <h4 class="font-medium text-sm sm:text-base">Delhi Cricket Stadium</h4>
                                    <div class="flex items-center text-yellow-500 text-xs sm:text-sm">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <span class="text-gray-500 ml-1">(4.5)</span>
                                    </div>
                                </div>
                                <div class="w-1/4 text-right">
                                    <span class="text-green-600 font-bold text-sm sm:text-base">84%</span>
                                    <p class="text-xs text-gray-500">Occupancy</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-1/4">
                                    <img src="img/ground2.jpg" alt="Ground" class="h-12 w-12 sm:h-16 sm:w-16 rounded-lg object-cover"
                                        onerror="this.src='https://placehold.co/64x64?text=🎾'">
                                </div>
                                <div class="w-2/4">
                                    <h4 class="font-medium text-sm sm:text-base">Mumbai Tennis Arena</h4>
                                    <div class="flex items-center text-yellow-500 text-xs sm:text-sm">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                        <span class="text-gray-500 ml-1">(4.0)</span>
                                    </div>
                                </div>
                                <div class="w-1/4 text-right">
                                    <span class="text-green-600 font-bold text-sm sm:text-base">76%</span>
                                    <p class="text-xs text-gray-500">Occupancy</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-1/4">
                                    <img src="img/ground3.jpg" alt="Ground" class="h-12 w-12 sm:h-16 sm:w-16 rounded-lg object-cover"
                                        onerror="this.src='https://placehold.co/64x64?text=🏏'">
                                </div>
                                <div class="w-2/4">
                                    <h4 class="font-medium text-sm sm:text-base">Chennai Cricket Ground</h4>
                                    <div class="flex items-center text-yellow-500 text-xs sm:text-sm">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <span class="text-gray-500 ml-1">(5.0)</span>
                                    </div>
                                </div>
                                <div class="w-1/4 text-right">
                                    <span class="text-green-600 font-bold text-sm sm:text-base">92%</span>
                                    <p class="text-xs text-gray-500">Occupancy</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<script src="{{ asset('assets/admin/js/main.js') }}"></script>
@endsection

