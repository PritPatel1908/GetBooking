console.log("Test modal script loaded!");

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded in test script");

    // Test button click
    document.querySelector('body').addEventListener('click', function(e) {
        if (e.target.closest('.upload-photos-btn')) {
            console.log("Upload button clicked via body delegate");
            const btn = e.target.closest('.upload-photos-btn');
            const groundId = btn.getAttribute('data-ground-id');
            console.log("Ground ID:", groundId);

            const modal = document.getElementById('ground-image-upload-modal');
            console.log("Modal element:", modal);

            if (modal) {
                console.log("Setting modal display style to block");
                modal.style.display = 'block';
                modal.classList.remove('hidden');

                // Set ground ID in form
                const groundIdInput = document.getElementById('upload_ground_id');
                if (groundIdInput) {
                    groundIdInput.value = groundId;
                }
            }

            e.preventDefault();
            e.stopPropagation();
        }
    });

    // Handle direct clicks on close buttons
    document.querySelector('body').addEventListener('click', function(e) {
        if (e.target.closest('.close-modal')) {
            console.log("Close button clicked");
            const modal = document.getElementById('ground-image-upload-modal');
            if (modal) {
                console.log("Closing modal");
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
            e.preventDefault();
        }
    });

    // Handle upload button click
    document.querySelector('body').addEventListener('click', function(e) {
        if (e.target.closest('#upload-images-btn')) {
            console.log("Upload images button clicked");
            e.preventDefault();

            const form = document.getElementById('ground-image-upload-form');
            const fileInput = document.getElementById('ground_images');

            if (!form || !fileInput || fileInput.files.length === 0) {
                alert('Please select at least one image to upload.');
                return;
            }

            const formData = new FormData(form);
            const uploadBtn = e.target.closest('#upload-images-btn');
            const originalText = uploadBtn.innerHTML;

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Uploading...';

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
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = originalText;

                if (data.status === 'success') {
                    const modal = document.getElementById('ground-image-upload-modal');
                    if (modal) {
                        modal.style.display = 'none';
                        modal.classList.add('hidden');
                    }

                    alert('Images uploaded successfully!');
                    window.location.reload();
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
    });

    // Handle file input change
    const fileInput = document.getElementById('ground_images');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            console.log("File input changed");
            const previewContainer = document.getElementById('image-preview-container');

            if (previewContainer) {
                previewContainer.innerHTML = '';

                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    if (!file.type.match('image.*')) continue;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'bg-gray-100 rounded-lg overflow-hidden h-24 relative';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-full object-cover';

                        previewDiv.appendChild(img);
                        previewContainer.appendChild(previewDiv);
                    };

                    reader.readAsDataURL(file);
                }
            }
        });
    }
});
