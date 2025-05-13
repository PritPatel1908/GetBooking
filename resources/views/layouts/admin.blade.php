<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get Booking - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    @yield('styles')
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar"
            class="bg-gradient-to-b from-indigo-800 to-indigo-900 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-10 shadow-xl">
            <div class="flex items-center space-x-4 px-4 mb-6">
                <i class="fas fa-futbol text-2xl text-white bg-indigo-600 p-2 rounded-full"></i>
                <span class="text-2xl font-bold">Get Booking</span>
            </div>

            <nav>
                <a href="{{ route('admin.dashboard') }}"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="{{ route('admin.clients') }}"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item {{ request()->is('admin/clients') ? 'active' : '' }}">
                    <i class="fas fa-users mr-2"></i>Clients
                </a>
                <a href="tournaments.html"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-trophy mr-2"></i>Tournaments
                </a>
                <a href="{{ route('admin.grounds') }}"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-map-marker-alt mr-2"></i>Grounds
                </a>
                <a href="bookings.html"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-calendar-check mr-2"></i>Bookings
                </a>
                <a href="payments.html"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-money-bill-wave mr-2"></i>Payments
                </a>
                <a href="users.html"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-user-cog mr-2"></i>Users
                </a>
            </nav>

            <div class="px-4 mt-12">
                <div class="bg-indigo-700 rounded-lg p-4 text-center">
                    <p class="text-sm opacity-75 mb-2">Need help?</p>
                    <button
                        class="bg-white text-indigo-800 px-4 py-2 rounded-lg w-full text-sm font-medium hover:bg-indigo-100 transition-colors">
                        Support Center
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center space-x-3">
                        <button id="sidebar-toggle"
                            class="md:hidden p-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="text-xl font-semibold">@yield('title')</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" placeholder="Search..."
                                class="w-48 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-100">
                            <i class="fas fa-search absolute right-3 top-2.5 text-gray-500"></i>
                        </div>
                        <div class="relative">
                            <button
                                class="p-2 rounded-full hover:bg-gray-200 relative focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-bell"></i>
                                <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2 ml-2 border-l pl-4">
                            <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Admin"
                                class="w-8 h-8 rounded-full border-2 border-indigo-500">
                            <div class="hidden md:block">
                                <span class="block text-sm font-semibold">Admin User</span>
                                <span class="block text-xs text-gray-500">Administrator</span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            @yield('content')
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast"
        class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 hidden transform transition-all duration-300 scale-95 opacity-0 z-50 max-w-md">
        <div class="flex items-center space-x-2">
            <i id="toast-icon" class="fas fa-check-circle text-green-500 text-xl"></i>
            <div class="flex-1">
                <p id="toast-message" class="font-medium"></p>
            </div>
            <button class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    @yield('modals')

    <script src="{{ asset('assets/admin/js/page-handler.js') }}"></script>
    @yield('scripts')
</body>

</html>
