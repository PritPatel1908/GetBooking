<!-- Ground View Modal -->
<div id="ground-view-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div
        class="bg-white rounded-lg max-w-3xl w-full mx-auto my-12 shadow-xl transform transition-all duration-300 max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
            <div class="flex items-center">
                <div id="ground-image" class="h-14 w-14 rounded-lg bg-indigo-100 mr-4 overflow-hidden flex items-center justify-center">
                    <img id="ground-view-image" src="" alt="Ground Image" class="h-full w-full object-cover">
                    <span id="ground-initial" class="text-2xl font-bold text-indigo-500 hidden"></span>
                </div>
                <div>
                    <h3 id="ground-view-name" class="text-xl font-medium text-gray-800"></h3>
                    <p id="ground-view-location" class="text-sm text-gray-500"></p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span id="ground-view-status-badge" class="px-3 py-1 text-xs rounded-full font-medium"></span>
                <button class="text-gray-400 hover:text-gray-600 close-view-modal focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="p-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Ground Information -->
                <div class="bg-white rounded-lg border p-4">
                    <h4 class="text-lg font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-info-circle text-indigo-500 mr-2"></i>
                        Ground Information
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Ground Name</p>
                            <p id="ground-view-full-name" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p id="ground-view-location-full" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Price</p>
                            <p id="ground-view-price" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Capacity</p>
                            <p id="ground-view-capacity" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ground Type</p>
                            <p id="ground-view-type" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ground Category</p>
                            <p id="ground-view-category" class="font-medium"></p>
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div class="bg-white rounded-lg border p-4">
                    <h4 class="text-lg font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-clipboard-list text-indigo-500 mr-2"></i>
                        Additional Details
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Description</p>
                            <p id="ground-view-description" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Rules</p>
                            <p id="ground-view-rules" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Operating Hours</p>
                            <p id="ground-view-hours" class="font-medium"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Added On</p>
                            <p id="ground-view-created" class="font-medium"></p>
                        </div>
                        <div class="flex space-x-4">
                            <div>
                                <p class="text-sm text-gray-500">Ground Status</p>
                                <div class="mt-1">
                                    <span id="ground-view-is-new" class="hidden px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-medium">New</span>
                                    <span id="ground-view-is-featured" class="hidden px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700 font-medium">Featured</span>
                                    <span id="ground-view-is-standard" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 font-medium">Standard</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ground Slabs/Slots -->
                <div class="bg-white rounded-lg border p-4">
                    <h4 class="text-lg font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-layer-group text-indigo-500 mr-2"></i>
                        Ground Slabs
                    </h4>
                    <div id="ground-view-slabs-container">
                        <div class="text-center py-3 text-gray-500" id="no-slabs-message">
                            <p>No slabs available for this ground.</p>
                        </div>
                        <div id="ground-slabs-list" class="hidden">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="ground-slabs-grid"></div>
                        </div>
                    </div>
                </div>

                <!-- Booking History -->
                <div class="bg-white rounded-lg border p-4 md:col-span-2">
                    <h4 class="text-lg font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-calendar-check text-indigo-500 mr-2"></i>
                        Booking History
                    </h4>
                    <div id="ground-bookings-container">
                        <div class="text-center py-6 text-gray-500" id="no-bookings-message">
                            <i class="fas fa-calendar-times text-gray-300 text-4xl mb-2"></i>
                            <p>No bookings found for this ground.</p>
                        </div>
                        <div id="ground-bookings-list" class="hidden">
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
                                                Client</th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status</th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ground-bookings-table" class="bg-white divide-y divide-gray-200"></tbody>
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
                    <i class="fas fa-edit mr-2"></i> Edit Ground
                </button>
            </div>
        </div>
    </div>
</div>
