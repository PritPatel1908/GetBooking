// Ground Image Upload Handler
document.addEventListener('DOMContentLoaded', function() {
    console.log("Ground image upload handler loaded");
    initImageUploadHandlers();
});

function initImageUploadHandlers() {
    // Find all upload buttons
    const uploadButtons = document.querySelectorAll('.upload-photos-btn');

    console.log("Found upload buttons:", uploadButtons.length);

    // Add click event to all upload buttons
    uploadButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const groundId = this.getAttribute('data-ground-id');
            console.log("Upload button clicked for ground:", groundId);

            openUploadModal(groundId);
        });
    });

    // Handle modal close buttons
    const closeButtons = document.querySelectorAll('#ground-image-upload-modal .close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            closeUploadModal();
        });
    });

    // Handle file input change for preview
    const fileInput = document.getElementById('ground_images');
    if (fileInput) {
        fileInput.addEventListener('change', handleFilePreview);
    }

    // Handle form submission
    const uploadButton = document.getElementById('upload-images-btn');
    if (uploadButton) {
        uploadButton.addEventListener('click', handleImageUpload);
    }
}

function openUploadModal(groundId) {
    const modal = document.getElementById('ground-image-upload-modal');
    const groundIdInput = document.getElementById('upload_ground_id');

    if (!modal) {
        console.error("Modal element not found");
        alert("Error: Upload modal not found!");
        return;
    }

    // Set ground ID
    if (groundIdInput) {
        groundIdInput.value = groundId;
    }

    // Reset file input
    const fileInput = document.getElementById('ground_images');
    if (fileInput) {
        fileInput.value = '';
    }

    // Clear preview
    const previewContainer = document.getElementById('image-preview-container');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }

    // Display modal
    modal.classList.remove('hidden');
    modal.style.display = 'block';
    console.log("Modal opened");
}

function closeUploadModal() {
    const modal = document.getElementById('ground-image-upload-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
        console.log("Modal closed");
    }
}

function handleFilePreview(e) {
    const files = e.target.files;
    const previewContainer = document.getElementById('image-preview-container');

    if (!previewContainer) return;

    // Clear previous previews
    previewContainer.innerHTML = '';

    // Preview each selected file
    Array.from(files).forEach(file => {
        if (!file.type.match('image.*')) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            const previewDiv = document.createElement('div');
            previewDiv.className = 'bg-gray-100 rounded-lg overflow-hidden h-24 relative';

            const img = document.createElement('img');
            img.src = event.target.result;
            img.className = 'w-full h-full object-cover';

            previewDiv.appendChild(img);
            previewContainer.appendChild(previewDiv);
        };

        reader.readAsDataURL(file);
    });
}

function handleImageUpload(e) {
    e.preventDefault();

    const form = document.getElementById('ground-image-upload-form');
    const fileInput = document.getElementById('ground_images');

    if (!form || !fileInput || fileInput.files.length === 0) {
        alert('Please select at least one image to upload.');
        return;
    }

    // Prepare form data
    const formData = new FormData(form);

    // Update button state
    const uploadBtn = e.target;
    const originalText = uploadBtn.innerHTML;
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Uploading...';

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
        // Reset button
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;

        if (data.status === 'success') {
            // Close modal
            closeUploadModal();

            // Show success message
            alert('Images uploaded successfully!');

            // Update photos grid without page reload
            if (data.photos && data.photos.length > 0) {
                updatePhotosGrid(data.photos);
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to upload images.'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = originalText;
        alert('An error occurred during upload. Please try again.');
    });
}

function updatePhotosGrid(photos) {
    const photosSection = document.querySelector('.bg-white:has(.fa-images)');
    if (!photosSection) return;

    const container = photosSection.querySelector('.p-4');
    if (!container) return;

    // Check if we have an empty state or existing grid
    const emptyState = container.querySelector('.text-center');
    let photosGrid = container.querySelector('.grid');

    // If we have empty state and new photos, replace with grid
    if (emptyState && photos.length > 0) {
        emptyState.remove();
        photosGrid = document.createElement('div');
        photosGrid.className = 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';
        container.appendChild(photosGrid);
    }

    // If grid doesn't exist yet, create it
    if (!photosGrid && photos.length > 0) {
        photosGrid = document.createElement('div');
        photosGrid.className = 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';
        container.appendChild(photosGrid);
    }

    // Add photos to grid
    photos.forEach(photo => {
        const photoDiv = document.createElement('div');
        photoDiv.className = 'bg-gray-100 rounded-lg overflow-hidden h-36 relative group';
        photoDiv.innerHTML = `
            <img src="${photo.image_path}" alt="Ground Photo" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                <button class="p-2 bg-white rounded-full text-gray-700 hover:text-indigo-600 transition-colors view-photo-btn" data-image-path="${photo.image_path}">
                    <i class="fas fa-expand"></i>
                </button>
                <button class="p-2 bg-white rounded-full text-gray-700 hover:text-red-600 transition-colors delete-photo-btn" data-image-id="${photo.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        // Add to grid
        photosGrid.appendChild(photoDiv);

        // Add event listeners
        const viewBtn = photoDiv.querySelector('.view-photo-btn');
        if (viewBtn) {
            viewBtn.addEventListener('click', function() {
                window.open(this.getAttribute('data-image-path'), '_blank');
            });
        }

        const deleteBtn = photoDiv.querySelector('.delete-photo-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                handlePhotoDelete(this.getAttribute('data-image-id'), photoDiv);
            });
        }
    });
}

function handlePhotoDelete(photoId, photoElement) {
    if (!confirm('Are you sure you want to delete this photo?')) return;

    fetch(`/admin/ground-images/delete/${photoId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Remove photo from DOM
            if (photoElement && photoElement.parentNode) {
                photoElement.parentNode.removeChild(photoElement);
            }

            // If no photos left, show empty state
            const grid = document.querySelector('.grid-cols-2.md\\:grid-cols-3.lg\\:grid-cols-4');
            if (grid && grid.children.length === 0) {
                const container = grid.parentNode;
                grid.remove();

                // Create empty state
                const emptyState = document.createElement('div');
                emptyState.className = 'text-center py-8 text-gray-500';
                emptyState.innerHTML = `
                    <i class="fas fa-camera text-gray-300 text-4xl mb-3"></i>
                    <p>No photos available for this ground.</p>
                    <button class="mt-3 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 focus:outline-none upload-photos-btn"
                            data-ground-id="${document.getElementById('upload_ground_id')?.value || ''}">
                        <i class="fas fa-upload mr-2"></i> Upload Photos
                    </button>
                `;

                container.appendChild(emptyState);

                // Reinitialize handlers
                initImageUploadHandlers();
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to delete photo.'));
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        alert('An error occurred while deleting the photo.');
    });
}
