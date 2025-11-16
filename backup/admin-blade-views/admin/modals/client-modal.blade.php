<!-- Client Modal -->
<style>
    .modal-scroll-content::-webkit-scrollbar {
        width: 0px;
        background: transparent; /* make scrollbar transparent */
    }

    .modal-scroll-content {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none;  /* IE and Edge */
    }
</style>

<div id="client-modal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden backdrop-blur-sm flex items-center justify-center p-2">
    <div class="bg-white rounded-2xl w-full max-w-[98%] mx-auto shadow-2xl transform transition-all duration-300 overflow-hidden border border-gray-200">
        <div class="flex flex-col h-[95vh]">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-purple-600 p-6">
                <div class="flex justify-between items-center">
                    <h3 id="client-modal-title" class="text-3xl font-bold text-white drop-shadow-sm flex items-center">
                        <i class="fas fa-user-tie mr-3 text-white bg-white bg-opacity-20 p-2 rounded-lg"></i>
                        <span>Add New Client</span>
                    </h3>
                    <button class="text-white hover:text-gray-200 close-client-modal focus:outline-none bg-white bg-opacity-10 hover:bg-opacity-20 transition-all duration-200 p-2 rounded-lg" title="Close">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body with Scrolling -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50 modal-scroll-content">
                <form id="client-form" method="POST">
                    @csrf
                    <input type="hidden" name="client_id" id="client_id">

                    <!-- Form errors container -->
                    <div id="form-errors" class="hidden bg-red-50 border border-red-300 text-red-600 p-3 rounded-md mb-4 text-sm"></div>

                    <!-- Basic Info Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                            Personal Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="first_name" id="first_name"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" name="last_name" id="last_name"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-venus-mars text-gray-400"></i>
                                    </div>
                                    <select name="gender" id="gender"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none"
                                        required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-address-card text-indigo-600 mr-2"></i>
                            Contact Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" name="email" id="email"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="phone" id="phone"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-lock text-indigo-600 mr-2"></i>
                            Password
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="password" name="password" id="password"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        autocomplete="new-password" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        autocomplete="new-password" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-map-marker-alt text-indigo-600 mr-2"></i>
                            Address Details
                        </h4>
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Address</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                        <i class="fas fa-home text-gray-400"></i>
                                    </div>
                                    <textarea name="full_address" id="full_address" rows="2"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"></textarea>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Area</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-map text-gray-400"></i>
                                        </div>
                                        <input type="text" name="area" id="area"
                                            class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-city text-gray-400"></i>
                                        </div>
                                        <input type="text" name="city" id="city"
                                            class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pincode</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-thumbtack text-gray-400"></i>
                                        </div>
                                        <input type="text" name="pincode" id="pincode"
                                            class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">State</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-flag text-gray-400"></i>
                                    </div>
                                    <input type="text" name="state" id="state"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-toggle-on text-indigo-600 mr-2"></i>
                            Status
                        </h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-circle text-gray-400"></i>
                                </div>
                                <select name="status" id="status"
                                    class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="bg-white p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button"
                    class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 font-medium flex items-center close-client-modal transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="submit" form="client-form"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium flex items-center transition-all duration-200">
                    <i class="fas fa-save mr-2"></i> Save Client
                </button>
            </div>
        </div>
    </div>
</div>
