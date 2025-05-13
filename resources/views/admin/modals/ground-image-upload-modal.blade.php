<!-- Ground Image Upload Modal -->
<div id="ground-image-upload-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" style="display: none;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Modal header -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 relative">
                <!-- Close button -->
                <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 close-modal" type="button">
                    <i class="fas fa-times"></i>
                </button>

                <!-- Modal content -->
                <div class="sm:flex sm:items-start">
                    <!-- Icon -->
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-images text-indigo-600"></i>
                    </div>

                    <!-- Content -->
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <!-- Title -->
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Upload Ground Images
                        </h3>

                        <!-- Form -->
                        <form id="ground-image-upload-form" class="mt-4" enctype="multipart/form-data">
                            <!-- CSRF Token -->
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <!-- Ground ID -->
                            <input type="hidden" id="upload_ground_id" name="ground_id">

                            <!-- File input -->
                            <div class="mb-4">
                                <label for="ground_images" class="block text-sm font-medium text-gray-700 mb-1">
                                    Select Images
                                </label>
                                <input type="file" name="images[]" id="ground_images" multiple
                                    class="w-full border-gray-300 rounded-md shadow-sm p-2 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    accept="image/*">
                                <p class="text-xs text-gray-500 mt-1">
                                    You can select multiple images. Supported formats: JPG, PNG, GIF (max 2MB each)
                                </p>
                            </div>

                            <!-- Image preview -->
                            <div id="image-preview-container" class="grid grid-cols-3 gap-3 mt-4">
                                <!-- Preview images will appear here -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <!-- Upload button -->
                <button type="button" id="upload-images-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Upload Images
                </button>

                <!-- Cancel button -->
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create a simple button to directly open the modal -->
<button id="emergency-open-modal" style="position: fixed; bottom: 10px; right: 10px; z-index: 9999; background-color: red; color: white; padding: 10px; border-radius: 5px; display: none;">Emergency Open Modal</button>

<!-- Inline script to ensure modal works -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Inline modal script loaded");

    // Create emergency button for testing
    const emergencyBtn = document.getElementById('emergency-open-modal');
    if (emergencyBtn) {
        emergencyBtn.style.display = 'block';
        emergencyBtn.addEventListener('click', function() {
            forceOpenModal();
        });
    }

    // Add click handlers directly to upload buttons
    const uploadButtons = document.querySelectorAll('.upload-photos-btn');
    uploadButtons.forEach(button => {
        button.onclick = function(e) {
            e.preventDefault();
            console.log("Upload button clicked (inline)");

            const groundId = this.getAttribute('data-ground-id');
            forceOpenModal(groundId);
        };
    });

    // Close modal handlers
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.onclick = function(e) {
            e.preventDefault();
            console.log("Close button clicked (inline)");

            forceCloseModal();
        };
    });

    function forceOpenModal(groundId) {
        console.log("Force opening modal");

        const modal = document.getElementById('ground-image-upload-modal');
        if (!modal) {
            console.error("Modal not found!");
            return;
        }

        // Force modal to be visible with important styles
        modal.classList.remove('hidden');
        modal.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important; z-index: 9999 !important;');

        // Add overlay styles
        document.body.classList.add('overflow-hidden');

        // Set ground ID if provided
        if (groundId) {
            const groundIdInput = document.getElementById('upload_ground_id');
            if (groundIdInput) groundIdInput.value = groundId;
        }

        // Try alternative method - create modal directly
        if (!modal.offsetWidth) {
            console.log("Modal still not visible, trying alternate method");
            createAlternativeModal(groundId);
        }
    }

    function forceCloseModal() {
        console.log("Force closing modal");

        const modal = document.getElementById('ground-image-upload-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.setAttribute('style', 'display: none !important;');
        }

        document.body.classList.remove('overflow-hidden');

        // Also remove any alternative modal
        const altModal = document.getElementById('alternative-modal');
        if (altModal) altModal.remove();
    }

    function createAlternativeModal(groundId) {
        // Remove any existing alternative modal
        const existingAlt = document.getElementById('alternative-modal');
        if (existingAlt) existingAlt.remove();

        // Create a completely new modal from scratch
        const modalDiv = document.createElement('div');
        modalDiv.id = 'alternative-modal';
        modalDiv.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; justify-content: center; align-items: center;';

        const modalContent = document.createElement('div');
        modalContent.style.cssText = 'background: white; width: 400px; max-width: 90%; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;';

        modalContent.innerHTML = `
            <div style="padding: 20px; position: relative;">
                <button id="alt-close-btn" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
                <h3 style="margin-top: 0; font-size: 18px; font-weight: bold;">Upload Ground Images</h3>
                <form id="alt-upload-form" style="margin-top: 15px;">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="ground_id" value="${groundId || ''}">
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 14px;">Select Images</label>
                        <input type="file" name="images[]" multiple accept="image/*" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div id="alt-preview" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 10px;"></div>
                </form>
            </div>
            <div style="padding: 15px; background: #f9fafb; display: flex; justify-content: flex-end;">
                <button id="alt-upload-btn" style="background: #4f46e5; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Upload Images</button>
                <button id="alt-cancel-btn" style="background: white; color: #374151; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 4px; margin-left: 8px; cursor: pointer;">Cancel</button>
            </div>
        `;

        modalDiv.appendChild(modalContent);
        document.body.appendChild(modalDiv);

        // Add event listeners
        document.getElementById('alt-close-btn').onclick = forceCloseModal;
        document.getElementById('alt-cancel-btn').onclick = forceCloseModal;
        document.getElementById('alt-upload-btn').onclick = function() {
            const form = document.getElementById('alt-upload-form');
            const formData = new FormData(form);

            // Add loading state
            this.disabled = true;
            this.textContent = 'Uploading...';

            fetch('/admin/ground-images/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                this.textContent = 'Upload Images';

                if (data.status === 'success') {
                    forceCloseModal();
                    alert('Images uploaded successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to upload images.'));
                }
            })
            .catch(error => {
                this.disabled = false;
                this.textContent = 'Upload Images';
                console.error('Upload error:', error);
                alert('An error occurred during upload. Please try again.');
            });
        };
    }
});
</script>
