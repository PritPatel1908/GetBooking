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
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar"
            class="bg-gradient-to-b from-indigo-800 to-indigo-900 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-10 shadow-xl">
            <div class="flex items-center justify-between px-4 mb-6">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-futbol text-xl text-white bg-indigo-600 p-2 rounded-full"></i>
                    <span class="text-xl font-bold">Get Booking</span>
                </div>
                <button id="close-sidebar" class="md:hidden text-white focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav>
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home mr-2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.clients') }}"
                    class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item {{ request()->is('admin/clients') ? 'active' : '' }}">
                    <i class="fas fa-users mr-2"></i>
                    <span>Clients</span>
                </a>
                {{-- <a href="tournaments.html"
                    class="block py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-trophy mr-2"></i>Tournaments
                </a> --}}
                <a href="{{ route('admin.grounds') }}"
                    class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    <span>Grounds</span>
                </a>
                <a href="{{ route('admin.bookings') }}"
                    class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-calendar-check mr-2"></i>
                    <span>Bookings</span>
                </a>
                <a href="payments.html"
                    class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    <span>Payments</span>
                </a>
                <a href="users.html"
                    class="flex items-center py-2.5 px-4 rounded transition duration-200 hover:bg-indigo-700 hover:pl-6 sidebar-item">
                    <i class="fas fa-user-cog mr-2"></i>
                    <span>Users</span>
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
                            class="p-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="text-xl font-semibold hidden sm:inline-block">@yield('title')</h1>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <div class="relative hidden sm:block">
                            <input type="text" placeholder="Search..."
                                class="w-32 md:w-48 rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-100">
                            <i class="fas fa-search absolute right-3 top-2.5 text-gray-500"></i>
                        </div>
                        <div class="relative">
                            <button
                                class="p-2 rounded-full hover:bg-gray-200 relative focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-bell"></i>
                                <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                            </button>
                        </div>
                        <div class="flex items-center space-x-2 ml-2 border-l pl-2 sm:pl-4">
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
            <div class="overflow-y-auto flex-1">
                @yield('content')
            </div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar functionality
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const closeSidebar = document.getElementById('close-sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('active');
            });

            closeSidebar.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('active');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('active');
            });

            // Responsive behavior on resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('active');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });

            // Enhanced responsive tables functionality
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                // Add responsive table classes
                table.classList.add('table-responsive-card');

                // Check if we're on mobile to apply card view
                if (window.innerWidth <= 640) {
                    table.classList.add('card-view');
                }

                // Add data-label attributes for mobile view based on headers
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        if (headers[index]) {
                            cell.setAttribute('data-label', headers[index]);

                            // Identify special cells
                            if (cell.querySelector('img') && cell.textContent.includes('client') ||
                                cell.querySelector('.flex') && cell.querySelector('img')) {
                                cell.classList.add('client-cell');
                                cell.classList.add('no-label');
                            }

                            // Action buttons cell
                            if (cell.querySelectorAll('button').length > 0 ||
                                cell.querySelectorAll('a').length > 0) {
                                cell.classList.add('actions-cell');

                                // Add action button class to buttons and links
                                const actionButtons = cell.querySelectorAll('button, a');
                                actionButtons.forEach(btn => {
                                    btn.classList.add('action-button');

                                    // Make hidden buttons visible on mobile
                                    if (btn.classList.contains('hidden')) {
                                        btn.classList.remove('hidden');
                                        btn.classList.remove('sm:inline-block');
                                        btn.classList.add('inline-block');
                                    }
                                });
                            }

                            // Status badges
                            const statusSpan = cell.querySelector('span.rounded-full');
                            if (statusSpan) {
                                const statusText = statusSpan.textContent.trim().toLowerCase();
                                statusSpan.classList.add('status-badge');

                                if (statusText.includes('confirmed') || statusText.includes('active') ||
                                    statusText.includes('completed') || statusText.includes('success')) {
                                    statusSpan.classList.add('success');
                                } else if (statusText.includes('pending') || statusText.includes('waiting')) {
                                    statusSpan.classList.add('warning');
                                } else if (statusText.includes('cancelled') || statusText.includes('failed') ||
                                          statusText.includes('rejected')) {
                                    statusSpan.classList.add('danger');
                                } else {
                                    statusSpan.classList.add('info');
                                }
                            }
                        }
                    });
                });

                // Create table scroll container if not already wrapped
                const parent = table.parentElement;
                if (!parent.classList.contains('overflow-x-auto') && !parent.classList.contains('table-scroll-container')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'overflow-x-auto table-scroll-container relative';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);

                    // Create loading overlay
                    const loadingOverlay = document.createElement('div');
                    loadingOverlay.className = 'table-loading-overlay hidden';
                    const spinner = document.createElement('div');
                    spinner.className = 'table-loading-spinner';
                    loadingOverlay.appendChild(spinner);
                    wrapper.appendChild(loadingOverlay);

                    // Check if table overflows and add indicator
                    const checkTableOverflow = () => {
                        if (table.scrollWidth > wrapper.clientWidth) {
                            wrapper.classList.add('has-overflow');
                        } else {
                            wrapper.classList.remove('has-overflow');
                        }
                    };

                    // Check on load and on resize
                    checkTableOverflow();
                    window.addEventListener('resize', checkTableOverflow);
                }
            });

            // Handle window resize for responsive tables
            window.addEventListener('resize', function() {
                tables.forEach(table => {
                    if (window.innerWidth <= 640) {
                        table.classList.add('card-view');
                    } else {
                        table.classList.remove('card-view');
                    }
                });
            });

            // Long text truncation for better mobile experience
            const tableCells = document.querySelectorAll('td');
            tableCells.forEach(cell => {
                const text = cell.textContent;
                if (text && text.length > 30 && !cell.querySelector('img') &&
                    !cell.classList.contains('actions-cell') && !cell.classList.contains('client-cell')) {
                    cell.classList.add('truncate-text');
                    cell.setAttribute('title', text);
                }
            });

            // Better touch experience for mobile
            if ('ontouchstart' in document.documentElement) {
                const clickableElements = document.querySelectorAll('button, a, input[type="checkbox"], input[type="radio"]');
                clickableElements.forEach(el => {
                    el.classList.add('touch-target');
                });

                // Adjust form spacing for touch inputs
                const formGroups = document.querySelectorAll('.form-group');
                formGroups.forEach(group => {
                    group.classList.add('mobile-stack');
                });
            }

            // Apply responsive classes to form grids
            const formGrids = document.querySelectorAll('.grid-cols-2, .grid-cols-3');
            formGrids.forEach(grid => {
                grid.classList.add('responsive-form-grid');
            });

            // Make action button groups more mobile-friendly
            const actionGroups = document.querySelectorAll('.action-group');
            actionGroups.forEach(group => {
                group.classList.add('touch-space');
            });

            // Add sorting functionality to sortable table headers
            const sortableHeaders = document.querySelectorAll('th.sortable');
            sortableHeaders.forEach(header => {
                // Create sort icon
                const sortIcon = document.createElement('span');
                sortIcon.className = 'sort-icon';
                sortIcon.innerHTML = '<i class="fas fa-sort"></i>';
                header.appendChild(sortIcon);

                header.addEventListener('click', function() {
                    const table = header.closest('table');
                    const tbody = table.querySelector('tbody');
                    const rows = Array.from(tbody.querySelectorAll('tr'));
                    const index = Array.from(header.parentNode.children).indexOf(header);
                    const isAsc = header.classList.contains('sort-asc');

                    // Remove sort classes from all headers
                    sortableHeaders.forEach(h => {
                        h.classList.remove('sort-asc', 'sort-desc');
                        h.querySelector('.sort-icon').innerHTML = '<i class="fas fa-sort"></i>';
                    });

                    // Set new sort direction
                    if (isAsc) {
                        header.classList.add('sort-desc');
                        sortIcon.innerHTML = '<i class="fas fa-sort-down"></i>';
                    } else {
                        header.classList.add('sort-asc');
                        sortIcon.innerHTML = '<i class="fas fa-sort-up"></i>';
                    }

                    // Show loading overlay
                    const tableWrapper = table.closest('.table-scroll-container');
                    const loadingOverlay = tableWrapper.querySelector('.table-loading-overlay');
                    loadingOverlay.classList.remove('hidden');

                    // Sort the rows
                    setTimeout(() => {
                        rows.sort((a, b) => {
                            const aValue = a.children[index].textContent.trim();
                            const bValue = b.children[index].textContent.trim();

                            if (isAsc) {
                                return bValue.localeCompare(aValue);
                            } else {
                                return aValue.localeCompare(bValue);
                            }
                        });

                        // Clear and append rows in new order
                        rows.forEach(row => tbody.appendChild(row));

                        // Hide loading overlay
                        loadingOverlay.classList.add('hidden');
                    }, 300);
                });
            });

            // Add row action animations
            const actionButtons = document.querySelectorAll('td.actions-cell');
            actionButtons.forEach(cell => {
                cell.classList.add('row-actions');
            });
        });
    </script>
    @yield('scripts')
</body>

</html>
