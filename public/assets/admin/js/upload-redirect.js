// Handle direct upload functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log("Upload redirect script loaded");

    // Check if URL has upload=true parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('upload') && urlParams.get('upload') === 'true') {
        console.log("Upload parameter detected");
        // Extract ground ID from URL
        const pathParts = window.location.pathname.split('/');
        const groundId = pathParts[pathParts.length - 1];

        if (groundId) {
            console.log("Ground ID:", groundId);
            showUploadForm(groundId);
        }
    }

    function showUploadForm(groundId) {
        // Create a container for our form if it doesn't exist
        let formContainer = document.getElementById('direct-upload-container');
        if (!formContainer) {
            formContainer = document.createElement('div');
            formContainer.id = 'direct-upload-container';
            formContainer.style.cssText = 'background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 2px solid #4f46e5;';

            // Add the form HTML
            formContainer.innerHTML = `
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-upload text-indigo-500 mr-2"></i>
                    Upload Ground Images
                </h3>
                <form id="direct-upload-form" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <input type="hidden" name="ground_id" value="${groundId}">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Images</label>
                        <input type="file" name="images[]" id="direct-images" multiple accept="image/*"
                            class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">You can select multiple images (JPG, PNG, GIF)</p>
                    </div>
                    <div id="direct-preview" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4"></div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-upload-btn" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </button>
                        <button type="submit" id="submit-upload-btn" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Upload Images
                        </button>
                    </div>
                </form>
            `;

            // Insert at top of main content
            const mainContent = document.querySelector('main');
            if (mainContent && mainContent.firstChild) {
                mainContent.insertBefore(formContainer, mainContent.firstChild.nextSibling);
            } else {
                document.body.appendChild(formContainer);
            }

            // Add event listeners
            const form = document.getElementById('direct-upload-form');
            const cancelBtn = document.getElementById('cancel-upload-btn');
            const fileInput = document.getElementById('direct-images');
            const previewContainer = document.getElementById('direct-preview');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    // Remove upload parameter from URL and refresh
                    const url = new URL(window.location);
                    url.searchParams.delete('upload');
                    window.history.replaceState({}, '', url);
                    // Remove form
                    formContainer.remove();
                });
            }

            if (fileInput && previewContainer) {
                fileInput.addEventListener('change', function() {
                    // Clear previous previews
                    previewContainer.innerHTML = '';

                    // Generate previews for selected files
                    Array.from(this.files).forEach(file => {
                        if (!file.type.match('image.*')) return;

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.createElement('div');
                            preview.className = 'h-24 rounded-lg overflow-hidden bg-gray-100';
                            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                            previewContainer.appendChild(preview);
                        };
                        reader.readAsDataURL(file);
                    });
                });
            }

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = document.getElementById('submit-upload-btn');

                    if (fileInput.files.length === 0) {
                        alert('Please select at least one image to upload.');
                        return;
                    }

                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Uploading...';

                    // Send AJAX request
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
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Upload Images';

                        if (data.status === 'success') {
                            alert('Images uploaded successfully!');
                            // Remove upload parameter and refresh page
                            window.location.href = window.location.pathname;
                        } else {
                            alert('Error: ' + (data.message || 'Failed to upload images.'));
                        }
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Upload Images';
                        alert('An error occurred during upload. Please try again.');
                    });
                });
            }
        }
    }
});
