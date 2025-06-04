<!-- User View Modal -->
<div id="user-view-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div
        class="bg-white rounded-lg max-w-3xl w-full mx-auto my-12 shadow-xl transform transition-all duration-300 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
            <div class="flex items-center">
                <div id="user-avatar" class="h-14 w-14 rounded-full bg-indigo-100 mr-4 overflow-hidden">
                    <img id="user-view-avatar" src="" alt="User Avatar" class="h-full w-full object-cover">
                </div>
                <div>
                    <h3 id="user-view-name" class="text-xl font-medium text-gray-800"></h3>
                    <p id="user-view-email" class="text-sm text-gray-500"></p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span id="user-view-type-badge" class="px-3 py-1 text-xs rounded-full font-medium"></span>
                <button class="text-gray-400 hover:text-gray-600 close-view-modal focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="p-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-lg border p-4">
                    <h4 class="text-lg font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-user-circle text-indigo-500 mr-2"></i>
                        Personal Information
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Full Name</p>
                            <p id="user-view-full-name" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email Address</p>
                            <p id="user-view-email-full" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Phone Number</p>
                            <p id="user-view-phone" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">User Type</p>
                            <p id="user-view-type" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Registration Date</p>
                            <p id="user-view-registration" class="font-medium"></p>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="bg-white rounded-lg border p-4">
                    <h4 class="text-lg font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-map-marker-alt text-indigo-500 mr-2"></i>
                        Address Information
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Address</p>
                            <p id="user-view-address" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">City</p>
                            <p id="user-view-city" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">State</p>
                            <p id="user-view-state" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Postal Code</p>
                            <p id="user-view-postal-code" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Country</p>
                            <p id="user-view-country" class="font-medium"></p>
                        </div>
                    </div>
                </div>

                <!-- Booking History -->
                <div class="bg-white rounded-lg border p-4 md:col-span-2">
                    <h4 class="text-lg font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-calendar-check text-indigo-500 mr-2"></i>
                        Booking History
                    </h4>
                    <div id="user-bookings-container">
                        <div class="text-center py-6 text-gray-500" id="no-bookings-message">
                            <i class="fas fa-calendar-times text-gray-300 text-4xl mb-2"></i>
                            <p>No bookings found for this user.</p>
                        </div>
                        <div id="user-bookings-list" class="hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Booking ID</th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date</th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Time</th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Ground</th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="user-bookings-table" class="bg-white divide-y divide-gray-200"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 border-t bg-gray-50">
            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="px-4 py-2 bg-white border border-gray-300 rounded text-gray-700 hover:bg-gray-50 focus:outline-none close-view-modal">
                    Close
                </button>
                <button type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 focus:outline-none edit-from-view-btn">
                    <i class="fas fa-edit mr-2"></i> Edit User
                </button>
            </div>
        </div>
    </div>
</div>
