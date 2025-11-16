<!-- Ground Modal -->
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

<div id="ground-modal" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden backdrop-blur-sm flex items-center justify-center p-2">
    <div class="bg-white rounded-2xl w-full max-w-[98%] mx-auto shadow-2xl transform transition-all duration-300 overflow-hidden border border-gray-200">
        <div class="flex flex-col h-[95vh]">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-700 via-indigo-600 to-purple-600 p-6">
                <div class="flex justify-between items-center">
                    <h3 id="ground-modal-title" class="text-3xl font-bold text-white drop-shadow-sm flex items-center">
                        <i class="fas fa-map-marker-alt mr-3 text-white bg-white bg-opacity-20 p-2 rounded-lg"></i>
                        <span>Add New Ground</span>
                    </h3>
                    <button class="text-white hover:text-gray-200 close-ground-modal focus:outline-none bg-white bg-opacity-10 hover:bg-opacity-20 transition-all duration-200 p-2 rounded-lg" title="Close">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body with Scrolling -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50 modal-scroll-content">
                <form id="ground-form" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ground_id" id="ground_id">

                    <!-- Basic Info Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                            Basic Information
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ground Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-signature text-gray-400"></i>
                                    </div>
                                    <input type="text" name="name" id="name"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <input type="text" name="location" id="location"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ground Type</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hockey-puck text-gray-400"></i>
                                    </div>
                                    <select name="ground_type" id="ground_type"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none"
                                        required>
                                        <option value="">Select Ground Type</option>
                                        <option value="turf">Turf Ground</option>
                                        <option value="grass">Grass Ground</option>
                                        <option value="indoor">Indoor</option>
                                        <option value="concrete">Concrete</option>
                                        <option value="clay">Clay</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ground Category</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-list-alt text-gray-400"></i>
                                    </div>
                                    <select name="ground_category" id="ground_category"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none">
                                        <option value="allgrounds">All Grounds</option>
                                        <option value="football">Football</option>
                                        <option value="cricket">Cricket</option>
                                        <option value="basketball">Basketball</option>
                                        <option value="tennis">Tennis</option>
                                        <option value="volleyball">Volleyball</option>
                                        <option value="badminton">Badminton</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price Per Hour (₹)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-rupee-sign text-gray-400"></i>
                                    </div>
                                    <input type="number" name="price_per_hour" id="price_per_hour"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        placeholder="Price per hour (deprecated - use slot prices instead)" disabled>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-users text-gray-400"></i>
                                    </div>
                                    <input type="number" name="capacity" id="capacity"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-toggle-on text-gray-400"></i>
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

                        <!-- Ground Classification -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Ground Classification</label>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center space-x-8">
                                    <div class="flex items-center">
                                        <input type="radio" name="ground_classification" id="ground_classification_none" value="none" checked
                                            class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                        <label for="ground_classification_none" class="ml-2 block text-sm text-gray-700">Standard</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="ground_classification" id="ground_classification_new" value="new"
                                            class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                        <label for="ground_classification_new" class="ml-2 block text-sm text-gray-700">Mark as New</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="ground_classification" id="ground_classification_featured" value="featured"
                                            class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                        <label for="ground_classification_featured" class="ml-2 block text-sm text-gray-700">Featured Ground</label>
                                    </div>
                                </div>
                                <input type="hidden" name="is_new" id="is_new" value="0">
                                <input type="hidden" name="is_featured" id="is_featured" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-address-card text-indigo-600 mr-2"></i>
                            Contact Information
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                    <input type="text" name="phone" id="phone"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        required>
                                </div>
                            </div>
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Client</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user-tie text-gray-400"></i>
                                    </div>
                                    <select name="client_id" id="client_id"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white appearance-none">
                                        <option value="">Select Client</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Hours -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-clock text-indigo-600 mr-2"></i>
                            Operating Hours
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Opening Time</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-door-open text-gray-400"></i>
                                    </div>
                                    <input type="time" name="opening_time" id="opening_time"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Closing Time</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-door-closed text-gray-400"></i>
                                    </div>
                                    <input type="time" name="closing_time" id="closing_time"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-file-alt text-indigo-600 mr-2"></i>
                            Ground Details
                        </h4>
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                        <i class="fas fa-align-left text-gray-400"></i>
                                    </div>
                                    <textarea name="description" id="description" rows="3"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"></textarea>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rules</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                        <i class="fas fa-list-ul text-gray-400"></i>
                                    </div>
                                    <textarea name="rules" id="rules" rows="2"
                                        class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                                        placeholder="No smoking, Proper footwear required, etc."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Images Container (for edit mode) -->
                    <div id="current-images-container" class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300" style="display: none;">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-images text-indigo-600 mr-2"></i>
                            Current Images
                        </h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Images will be added here via JavaScript -->
                        </div>
                    </div>

                    <!-- Ground Images Section -->
                    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center">
                            <i class="fas fa-images text-indigo-600 mr-2"></i>
                            Ground Images
                        </h4>
                        <div class="flex flex-col space-y-4">
                            <!-- Modern file drop zone -->
                            <div class="space-y-4">
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-500 transition-colors cursor-pointer bg-gray-50" id="image-drop-zone">
                                    <input type="file" name="ground_images[]" id="ground_images"
                                        class="hidden multiple-image-input" multiple="multiple" accept="image/*">
                                    <div class="space-y-2">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                                        <div class="text-gray-600">
                                            <p class="font-medium">Click to browse or drag and drop images</p>
                                            <p class="text-sm text-gray-500">You can select multiple images (PNG, JPG, JPEG, GIF)</p>
                                        </div>
                                        <button type="button" id="browse-images-btn" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none">
                                            Browse Images
                                        </button>
                                    </div>
                                </div>

                                <!-- Selected files count -->
                                <div id="selected-files-counter" class="text-sm text-gray-600 hidden">
                                    <span id="file-count">0</span> images selected
                                </div>

                                <!-- Preview container with better styling -->
                                <div class="mt-4">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2" id="preview-heading">Image Previews</h5>
                                    <div id="images-preview-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Sections -->
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Ground Features Section -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                            <div class="flex justify-between items-center mb-4 border-b pb-2">
                                <h4 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-list-check text-indigo-600 mr-2"></i>
                                    Ground Features
                                </h4>
                                <button type="button" id="add-feature-btn"
                                    class="px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none flex items-center transition-colors duration-200">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            </div>
                            <div id="features-container">
                                <div class="feature-input-group grid grid-cols-2 gap-2 mb-2">
                                    <input type="text" name="feature_name[]" placeholder="Feature Name"
                                        class="rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <select name="feature_type[]"
                                        class="rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                        <option value="facility">Facility</option>
                                        <option value="equipment">Equipment</option>
                                        <option value="service">Service</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Ground Slots Section -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:border-indigo-200 transition-colors duration-300">
                            <div class="flex justify-between items-center mb-4 border-b pb-2">
                                <h4 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>
                                    Ground Slots
                                </h4>
                                <button type="button" id="add-slot-btn"
                                    class="px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none flex items-center transition-colors duration-200">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            </div>
                            <div id="slots-container">
                                <div class="slot-input-group grid grid-cols-6 gap-2 mb-2">
                                    <input type="text" name="slot_name[]" placeholder="Slot Name"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <select name="day_of_week[]"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                        <option value="">Select Day</option>
                                        <option value="monday">Monday</option>
                                        <option value="tuesday">Tuesday</option>
                                        <option value="wednesday">Wednesday</option>
                                        <option value="thursday">Thursday</option>
                                        <option value="friday">Friday</option>
                                        <option value="saturday">Saturday</option>
                                        <option value="sunday">Sunday</option>
                                    </select>
                                    <input type="time" name="start_time[]" placeholder="Start Time"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <input type="time" name="end_time[]" placeholder="End Time"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <input type="number" step="0.01" min="0" name="price_per_slot[]" placeholder="Price/Slot (₹)"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                    <div class="flex">
                                        <select name="slot_type[]"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                            <option value="morning">Morning</option>
                                            <option value="afternoon">Afternoon</option>
                                            <option value="evening">Evening</option>
                                            <option value="night">Night</option>
                                        </select>
                                        <button type="button" class="remove-btn ml-2 text-red-500 hover:text-red-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="bg-white p-6 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button"
                    class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 font-medium flex items-center close-ground-modal transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="submit" form="ground-form"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium flex items-center transition-all duration-200">
                    <i class="fas fa-save mr-2"></i> Save Ground
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prevent buttons from submitting the form
        document.querySelectorAll('#ground-modal button[type="button"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        // Browse Images button - directly trigger file input
        const browseImagesBtn = document.getElementById('browse-images-btn');
        const groundImagesInput = document.getElementById('ground_images');
        if (browseImagesBtn && groundImagesInput) {
            browseImagesBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                groundImagesInput.click();
            });
        }

        // Add Image button
        const addImageBtn = document.getElementById('add-image-btn');
        if (addImageBtn) {
            addImageBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const container = document.getElementById('images-container');
                const newInputGroup = document.createElement('div');
                newInputGroup.classList.add('image-input-group', 'mb-2', 'flex');

                newInputGroup.innerHTML = `
                    <input type="file" name="ground_images[]"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                        accept="image/*">
                    <button type="button" class="remove-btn ml-2 text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                container.appendChild(newInputGroup);

                // Add event listener to remove button
                newInputGroup.querySelector('.remove-btn').addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    container.removeChild(newInputGroup);
                });
            });
        }

        // Add Feature button
        const addFeatureBtn = document.getElementById('add-feature-btn');
        if (addFeatureBtn) {
            addFeatureBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const container = document.getElementById('features-container');
                const newInputGroup = document.createElement('div');
                newInputGroup.classList.add('feature-input-group', 'grid', 'grid-cols-2', 'gap-2', 'mb-2');

                newInputGroup.innerHTML = `
                    <div class="flex">
                        <input type="text" name="feature_name[]" placeholder="Feature Name"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    </div>
                    <div class="flex">
                        <select name="feature_type[]"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="facility">Facility</option>
                            <option value="equipment">Equipment</option>
                            <option value="service">Service</option>
                        </select>
                        <button type="button" class="remove-btn ml-2 text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                container.appendChild(newInputGroup);

                // Add event listener to remove button
                newInputGroup.querySelector('.remove-btn').addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    container.removeChild(newInputGroup);
                });
            });
        }

        // Function to update slot name based on start and end time
        function setupTimeRangeListener(container) {
            const startTimeInput = container.querySelector('input[name="start_time[]"]');
            const endTimeInput = container.querySelector('input[name="end_time[]"]');
            const slotNameInput = container.querySelector('input[name="slot_name[]"]');

            if (startTimeInput && endTimeInput && slotNameInput) {
                const updateSlotName = () => {
                    if (startTimeInput.value && endTimeInput.value) {
                        slotNameInput.value = startTimeInput.value + ' - ' + endTimeInput.value;
                    }
                };

                startTimeInput.addEventListener('change', updateSlotName);
                endTimeInput.addEventListener('change', updateSlotName);
            }
        }

        // Set up time range listeners for initial slot
        const initialSlots = document.querySelectorAll('.slot-input-group');
        initialSlots.forEach(slot => {
            setupTimeRangeListener(slot);
        });

        // Add Slot button
        const addSlotBtn = document.getElementById('add-slot-btn');
        if (addSlotBtn) {
            addSlotBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const container = document.getElementById('slots-container');
                const newInputGroup = document.createElement('div');
                newInputGroup.classList.add('slot-input-group', 'grid', 'grid-cols-6', 'gap-2', 'mb-2');

                newInputGroup.innerHTML = `
                    <input type="text" name="slot_name[]" placeholder="Slot Name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <select name="day_of_week[]"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Select Day</option>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                    <input type="time" name="start_time[]" placeholder="Start Time"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <input type="time" name="end_time[]" placeholder="End Time"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <input type=\"number\" step=\"0.01\" min=\"0\" name=\"price_per_slot[]\" placeholder=\"Price/Slot (₹)\"
                        class=\"w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white\">
                    <div class="flex">
                        <select name="slot_type[]"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                            <option value="night">Night</option>
                        </select>
                        <button type="button" class="remove-btn ml-2 text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                container.appendChild(newInputGroup);

                // Set up time range listener for the new slot
                setupTimeRangeListener(newInputGroup);

                // Add event listener to remove button
                newInputGroup.querySelector('.remove-btn').addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    container.removeChild(newInputGroup);
                });
            });
        }

        // Close modal buttons
        document.querySelectorAll('.close-ground-modal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('ground-modal').classList.add('hidden');
            });
        });

        // Function to show the modal
        window.showGroundModal = function() {
            document.getElementById('ground-modal').classList.remove('hidden');
        };

        // Function to populate the slot fields when editing a ground
        window.populateGroundSlots = function(slots) {
            const container = document.getElementById('slots-container');
            // Clear existing slots
            container.innerHTML = '';

            if (slots && slots.length > 0) {
                slots.forEach(slot => {
                    const slotDiv = document.createElement('div');
                    slotDiv.classList.add('slot-input-group', 'grid', 'grid-cols-6', 'gap-2', 'mb-2');

                    slotDiv.innerHTML = `
                        <input type="text" name="slot_name[]" placeholder="Slot Name" value="${slot.slot_name || ''}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <select name="day_of_week[]"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="">Select Day</option>
                            <option value="monday" ${slot.day_of_week === 'monday' ? 'selected' : ''}>Monday</option>
                            <option value="tuesday" ${slot.day_of_week === 'tuesday' ? 'selected' : ''}>Tuesday</option>
                            <option value="wednesday" ${slot.day_of_week === 'wednesday' ? 'selected' : ''}>Wednesday</option>
                            <option value="thursday" ${slot.day_of_week === 'thursday' ? 'selected' : ''}>Thursday</option>
                            <option value="friday" ${slot.day_of_week === 'friday' ? 'selected' : ''}>Friday</option>
                            <option value="saturday" ${slot.day_of_week === 'saturday' ? 'selected' : ''}>Saturday</option>
                            <option value="sunday" ${slot.day_of_week === 'sunday' ? 'selected' : ''}>Sunday</option>
                        </select>
                        <input type="time" name="start_time[]" placeholder="Start Time" value="${slot.start_time || ''}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <input type="time" name="end_time[]" placeholder="End Time" value="${slot.end_time || ''}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <input type=\"number\" step=\"0.01\" min=\"0\" name=\"price_per_slot[]\" placeholder=\"Price/Slot (₹)\" value=\"${slot.price_per_slot || ''}\"
                            class=\"w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white\">
                        <div class="flex">
                            <select name="slot_type[]"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="morning" ${slot.slot_type === 'morning' ? 'selected' : ''}>Morning</option>
                                <option value="afternoon" ${slot.slot_type === 'afternoon' ? 'selected' : ''}>Afternoon</option>
                                <option value="evening" ${slot.slot_type === 'evening' ? 'selected' : ''}>Evening</option>
                                <option value="night" ${slot.slot_type === 'night' ? 'selected' : ''}>Night</option>
                            </select>
                            <button type="button" class="remove-btn ml-2 text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;

                    container.appendChild(slotDiv);

                    // Setup time range listeners
                    setupTimeRangeListener(slotDiv);

                    // Add event listener to remove button
                    slotDiv.querySelector('.remove-btn').addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        container.removeChild(slotDiv);
                    });
                });
            } else {
                // Add at least one empty slot input group
                const emptySlot = document.createElement('div');
                emptySlot.classList.add('slot-input-group', 'grid', 'grid-cols-6', 'gap-2', 'mb-2');

                emptySlot.innerHTML = `
                    <input type="text" name="slot_name[]" placeholder="Slot Name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <select name="day_of_week[]"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Select Day</option>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                    <input type="time" name="start_time[]" placeholder="Start Time"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <input type="time" name="end_time[]" placeholder="End Time"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    <input type=\"number\" step=\"0.01\" min=\"0\" name=\"price_per_slot[]\" placeholder=\"Price/Slot (₹)\"
                        class=\"w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white\">
                    <div class="flex">
                        <select name="slot_type[]"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                            <option value="night">Night</option>
                        </select>
                        <button type="button" class="remove-btn ml-2 text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                container.appendChild(emptySlot);

                // Setup time range listeners
                setupTimeRangeListener(emptySlot);

                // Add event listener to remove button
                emptySlot.querySelector('.remove-btn').addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    container.removeChild(emptySlot);
                });
            }
        };
    });
</script>
