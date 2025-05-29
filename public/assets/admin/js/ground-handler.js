document.addEventListener('DOMContentLoaded', function() {
    // Check for message in localStorage (for showing toast after redirect)
    if (localStorage.getItem('groundActionMessage')) {
        window.showToast(
            localStorage.getItem('groundActionMessage'),
            localStorage.getItem('groundActionType') || 'success'
        );

        // Clear the message from localStorage
        localStorage.removeItem('groundActionMessage');
        localStorage.removeItem('groundActionType');
    }

    // Handle ground classification radio buttons
    const classificationRadios = document.querySelectorAll('input[name="ground_classification"]');
    const isNewField = document.getElementById('is_new');
    const isFeaturedField = document.getElementById('is_featured');

    if (classificationRadios.length > 0 && isNewField && isFeaturedField) {
        classificationRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Reset both fields
                isNewField.value = '0';
                isFeaturedField.value = '0';

                // Set the appropriate field based on selection
                if (this.value === 'new') {
                    isNewField.value = '1';
                } else if (this.value === 'featured') {
                    isFeaturedField.value = '1';
                }
                // 'none' option keeps both fields at 0
            });
        });
    }

    // Ground form submission handling
    const groundForm = document.getElementById('ground-form');

    if (groundForm) {
        groundForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Clear previous error messages
            clearErrorMessages();

            // Create a FormData object directly from the form
            const formData = new FormData(groundForm);

            // Manually add files from our selectedFiles array
            if (selectedFiles.length > 0) {
                // Remove any existing ground_images entries from formData
                if (formData.has('ground_images[]')) {
                    formData.delete('ground_images[]');
                }

                // Add each file from our custom array to the FormData
                selectedFiles.forEach((fileObj, index) => {
                    formData.append('ground_images[]', fileObj.file);
                    console.log(`Manually adding file ${index}: ${fileObj.file.name}`);
                });
            }

            // Log total files being uploaded
            console.log(`Uploading ${selectedFiles.length} images in total`);

            // Disable submit button and show loading
            const submitBtn = document.querySelector('button[type="submit"][form="ground-form"]');
            let originalBtnText = '';

            if (submitBtn) {
                originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
            }

            // Send AJAX request
            fetch('/admin/grounds/create', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }

                if (data.status === 'success') {
                    // Close modal
                    window.closeModalWithAnimation(document.getElementById('ground-modal'));

                    // Show success message
                    window.showToast(data.message, 'success');

                    // Always reload the table to show the latest data
                        reloadGroundTable();

                    // Reset form
                    groundForm.reset();

                    // Reset input file fields
                    resetFileInputs();

                    // Reset dynamic fields (ground images, features, slots)
                    resetDynamicFields();

                    // Reset selected files array and previews
                    resetSelectedFiles();
                } else if (data.status === 'error') {
                    // Display validation errors
                    displayValidationErrors(data.errors);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
                window.showToast('An error occurred while saving the ground.', 'error');
            });
        });
    }

    // Function to reset file inputs
    function resetFileInputs() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.value = '';
        });
    }

    // Function to reset dynamic fields (images, features, slots)
    function resetDynamicFields() {
        // Reset images container
        const imagesContainer = document.getElementById('images-container');
        if (imagesContainer) {
            // Keep only the first image input and remove others
            const imageInputs = imagesContainer.querySelectorAll('.image-input-group');
            for (let i = 1; i < imageInputs.length; i++) {
                imagesContainer.removeChild(imageInputs[i]);
            }
        }

        // Reset features container
        const featuresContainer = document.getElementById('features-container');
        if (featuresContainer) {
            // Keep only the first feature input and remove others
            const featureInputs = featuresContainer.querySelectorAll('.feature-input-group');
            for (let i = 1; i < featureInputs.length; i++) {
                featuresContainer.removeChild(featureInputs[i]);
            }
            // Clear the first feature input values
            const firstFeature = featuresContainer.querySelector('.feature-input-group');
            if (firstFeature) {
                firstFeature.querySelector('input[name="feature_name[]"]').value = '';
            }
        }

        // Reset slots container
        const slotsContainer = document.getElementById('slots-container');
        if (slotsContainer) {
            // Keep only the first slot input and remove others
            const slotInputs = slotsContainer.querySelectorAll('.slot-input-group');
            for (let i = 1; i < slotInputs.length; i++) {
                slotsContainer.removeChild(slotInputs[i]);
            }
            // Clear the first slot input values
            const firstSlot = slotsContainer.querySelector('.slot-input-group');
            if (firstSlot) {
                firstSlot.querySelector('input[name="slot_name[]"]').value = '';
            }
        }
    }

    // Function to clear error messages
    function clearErrorMessages() {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
            el.classList.add('border-gray-300');
        });
    }

    // Function to display validation errors
    function displayValidationErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            // Handle array fields like feature_name.0, feature_name.1, etc.
            const fieldName = field.split('.');
            const baseField = fieldName[0];
            const index = fieldName.length > 1 ? fieldName[1] : null;

            if (index !== null && ['feature_name', 'feature_type', 'slot_name', 'slot_type', 'ground_images'].includes(baseField)) {
                // For array inputs, find the specific element at the given index
                let inputField;
                if (baseField === 'feature_name' || baseField === 'feature_type') {
                    const featureGroups = document.querySelectorAll('.feature-input-group');
                    if (featureGroups.length > index) {
                        inputField = featureGroups[index].querySelector(`[name="${baseField}[]"]`);
                    }
                } else if (baseField === 'slot_name' || baseField === 'slot_type') {
                    const slotGroups = document.querySelectorAll('.slot-input-group');
                    if (slotGroups.length > index) {
                        inputField = slotGroups[index].querySelector(`[name="${baseField}[]"]`);
                    }
                } else if (baseField === 'ground_images') {
                    const imageGroups = document.querySelectorAll('.image-input-group');
                    if (imageGroups.length > index) {
                        inputField = imageGroups[index].querySelector('input[type="file"]');
                    }
                }

                if (inputField) {
                    // Add red border to the input field
                    inputField.classList.remove('border-gray-300');
                    inputField.classList.add('border-red-500');

                    // Add error message below the input
                    const errorMessage = document.createElement('p');
                    errorMessage.classList.add('text-red-500', 'text-xs', 'mt-1', 'error-message');
                    errorMessage.textContent = messages[0]; // Get first error message

                    // Insert error message after the input field
                    inputField.parentNode.insertBefore(errorMessage, inputField.nextSibling);
                }
            } else {
                // For regular inputs, find by ID
                const inputField = document.getElementById(field);

                if (inputField) {
                    // Add red border to the input field
                    inputField.classList.remove('border-gray-300');
                    inputField.classList.add('border-red-500');

                    // Add error message below the input
                    const errorMessage = document.createElement('p');
                    errorMessage.classList.add('text-red-500', 'text-xs', 'mt-1', 'error-message');
                    errorMessage.textContent = messages[0]; // Get first error message

                    // Insert error message after the input field
                    inputField.parentNode.insertBefore(errorMessage, inputField.nextSibling);
                }
            }
        }
    }

    // Function to get the appropriate image content for a ground
    function getGroundImageContent(ground) {
        let imageContent;

        if (ground.ground_image) {
            // If there's a main ground image, use it
            imageContent = `<img class="h-10 w-10 rounded-lg mr-3 object-cover" src="${ground.ground_image}" alt="${ground.name}">`;
        } else if (ground.images && ground.images.length > 0) {
            // If there are additional images, pick one randomly
            const randomIndex = Math.floor(Math.random() * ground.images.length);
            imageContent = `<img class="h-10 w-10 rounded-lg mr-3 object-cover" src="${ground.images[randomIndex].image_path}" alt="${ground.name}">`;
        } else {
            // If no images, use the first letter of the ground name
            const firstLetter = ground.name.charAt(0).toUpperCase();
            imageContent = `
                <div class="h-10 w-10 rounded-lg mr-3 bg-indigo-100 flex items-center justify-center">
                    <span class="text-lg font-bold text-indigo-500">${firstLetter}</span>
                </div>
            `;
        }

        return imageContent;
    }

    // Function to add a new ground to the table
    function addGroundToTable(ground) {
        const tbody = document.querySelector('table tbody');

        if (tbody) {
            // Check if we need to remove the "no grounds" message
            const emptyRow = tbody.querySelector('tr td[colspan="7"]');
            if (emptyRow) {
                emptyRow.parentNode.remove();
            }

            const newRow = document.createElement('tr');
            newRow.classList.add('hover:bg-gray-50', 'transition-colors', 'table-row-appear');
            newRow.dataset.groundId = ground.id;

            // Get the appropriate image content
            const imageContent = getGroundImageContent(ground);

            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        ${imageContent}
                        <div>
                            <div class="font-medium text-gray-900">${ground.name}</div>
                            <div class="text-sm text-gray-500">${ground.ground_type ? ground.ground_type : 'Standard'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${ground.location}</td>
                <td class="px-6 py-4 whitespace-nowrap">₹${ground.price_per_hour}</td>
                <td class="px-6 py-4 whitespace-nowrap">${ground.capacity} people</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">0 bookings</div>
                    <div class="text-xs text-gray-500">No bookings yet</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(ground.status)} font-medium">
                        ${ground.status.charAt(0).toUpperCase() + ground.status.slice(1).replace('_', ' ')}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
                        <a href="/admin/grounds/${ground.id}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Ground">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="text-blue-600 hover:text-blue-900 transition-colors edit-ground-btn" title="Edit Ground" data-ground-id="${ground.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900 transition-colors delete-ground-btn" title="Delete Ground" data-ground-id="${ground.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            // Add to the top of the table
            tbody.insertBefore(newRow, tbody.firstChild);

            // Animate the new row
            setTimeout(() => {
                newRow.classList.add('active');
            }, 10);

            // Update count in pagination info
            updatePaginationInfo(1);

            // Attach event listeners to the new row
            attachTableEventListeners();
        }
    }

    // Function to update existing ground in the table
    function updateGroundInTable(ground) {
        const row = document.querySelector(`tr[data-ground-id="${ground.id}"]`);

        if (row) {
            // Update the ground data in the row
            const nameCell = row.querySelector('.font-medium.text-gray-900');
            if (nameCell) nameCell.textContent = ground.name;

            const typeCell = row.querySelector('.text-sm.text-gray-500');
            if (typeCell) typeCell.textContent = ground.ground_type ? ground.ground_type : 'Standard';

            const locationCell = row.querySelector('td:nth-child(2)');
            if (locationCell) locationCell.textContent = ground.location;

            const priceCell = row.querySelector('td:nth-child(3)');
            if (priceCell) priceCell.textContent = `₹${ground.price_per_hour}`;

            const capacityCell = row.querySelector('td:nth-child(4)');
            if (capacityCell) capacityCell.textContent = `${ground.capacity} people`;

            const statusBadge = row.querySelector('td:nth-child(6) span');
            if (statusBadge) {
                // Update status badge
                statusBadge.className = `px-2 py-1 text-xs rounded-full ${getStatusClass(ground.status)} font-medium`;
                statusBadge.textContent = ground.status.charAt(0).toUpperCase() + ground.status.slice(1).replace('_', ' ');
            }

            // Update ground image area
            const imageArea = row.querySelector('td:nth-child(1) .flex.items-center');
            if (imageArea) {
                // Remove existing image or initial
                const existingImage = imageArea.querySelector('img, div.h-10.w-10');
                if (existingImage) {
                    existingImage.remove();
                }

                // Get new image content HTML
                const imageContent = getGroundImageContent(ground);

                // Create a temporary container to hold the HTML
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = imageContent.trim();

                // Get the first element (the image or initial div)
                const newImageElement = tempContainer.firstChild;

                // Insert at the beginning of the image area
                imageArea.insertBefore(newImageElement, imageArea.firstChild);
            }

            // Highlight the updated row briefly
            row.classList.add('bg-indigo-50');
            setTimeout(() => {
                row.classList.remove('bg-indigo-50');
            }, 2000);
        }
    }

    // Function to update pagination info
    function updatePaginationInfo(addedCount) {
        const paginationInfo = document.querySelector('.text-sm.text-gray-600');
        if (paginationInfo) {
            const text = paginationInfo.textContent;
            const matches = text.match(/Showing \d+ to \d+ of (\d+) entries/);

            if (matches && matches[1]) {
                const totalCount = parseInt(matches[1]) + addedCount;
                paginationInfo.textContent = text.replace(/of \d+ entries/, `of ${totalCount} entries`);
            }
        }
    }

    // Function to reload ground table
    function reloadGroundTable() {
        fetch('/admin/grounds/pagination', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('table tbody');
            if (!tbody) return;

            // Clear current table rows
            tbody.innerHTML = '';

            if (data.grounds && data.grounds.length > 0) {
                data.grounds.forEach(ground => {
                    const row = document.createElement('tr');
                    row.classList.add('hover:bg-gray-50', 'transition-colors');
                    row.dataset.groundId = ground.id;

                    // Get image content
                    const imageContent = getGroundImageContent(ground);

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                ${imageContent}
                                <div>
                                    <div class="font-medium text-gray-900">${ground.name}</div>
                                    <div class="text-sm text-gray-500">${ground.ground_type || 'Standard'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">${ground.location}</td>
                        <td class="px-6 py-4 whitespace-nowrap">₹${ground.price_per_hour}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${ground.capacity} people</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${ground.bookings_count || 0} bookings</div>
                            <div class="text-xs text-gray-500">${ground.last_booking_date || 'No bookings'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(ground.status)} font-medium">
                                ${ground.status.charAt(0).toUpperCase() + ground.status.slice(1).replace('_', ' ')}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <a href="/admin/grounds/${ground.id}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Ground">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="text-blue-600 hover:text-blue-900 transition-colors edit-ground-btn" title="Edit Ground" data-ground-id="${ground.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 transition-colors delete-ground-btn" title="Delete Ground" data-ground-id="${ground.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;

                    tbody.appendChild(row);
                });

                // Update pagination info
                const paginationInfo = document.querySelector('.text-sm.text-gray-600');
                if (paginationInfo && data.pagination) {
                    paginationInfo.textContent = `Showing ${data.pagination.from || 0} to ${data.pagination.to || 0} of ${data.pagination.total || 0} entries`;
                }

                // Reattach event listeners
                attachTableEventListeners();
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No grounds found. Add your first ground by clicking the "Add Ground" button.
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showToast('An error occurred while loading grounds.', 'error');
        });
    }

    var deletePopup = document.getElementById('delete-popup');
    var cancelDelete = document.getElementById('cancel-delete');
    var confirmDelete = document.getElementById('confirm-delete');
    var deleteGroundId = null;

    // Add click handlers to all delete buttons
    var deleteButtons = document.querySelectorAll('.delete-ground-btn');
    for (var i = 0; i < deleteButtons.length; i++) {
        deleteButtons[i].addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            deleteGroundId = this.getAttribute('data-ground-id');
            deletePopup.style.display = 'block';
        });
    }

    // Cancel delete button click handler
    if (cancelDelete) {
        cancelDelete.addEventListener('click', function(e) {
            e.preventDefault();
            deletePopup.style.display = 'none';
            deleteGroundId = null;
        });
    }

    // Confirm delete button click handler
    if (confirmDelete) {
        confirmDelete.addEventListener('click', function(e) {
            e.preventDefault();
            if (deleteGroundId) {
                // Send delete request
                fetch(`/admin/grounds/${deleteGroundId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    deletePopup.style.display = 'none';

                    if (data.status === 'success') {
                        // If we're on the single ground view page, redirect to list
                        if (window.location.pathname.includes('/admin/grounds/') &&
                            window.location.pathname.match(/\/admin\/grounds\/\d+$/)) {
                            localStorage.setItem('groundActionMessage', data.message);
                            localStorage.setItem('groundActionType', 'success');
                            window.location.href = '/admin/grounds';
                        } else {
                            // Remove the row from the table with animation
                            const row = document.querySelector(`tr[data-ground-id="${deleteGroundId}"]`);
                            if (row) {
                                row.classList.add('fade-out');
                                setTimeout(() => {
                                    row.remove();

                                    // Check if table is empty
                                    const tbody = document.querySelector('table tbody');
                                    if (tbody && tbody.children.length === 0) {
                                        tbody.innerHTML = `
                                            <tr>
                                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                                    No grounds found. Add your first ground by clicking the "Add Ground" button.
                                                </td>
                                            </tr>
                                        `;
                                    }

                                    // Update pagination info
                                    updatePaginationInfo(-1);
                                }, 300);
                            }

                            // Show success message
                            window.showToast(data.message, 'success');
                        }
                    } else {
                        window.showToast(data.message || 'An error occurred while deleting the ground.', 'error');
                    }

                    deleteGroundId = null;
                })
                .catch(error => {
                    console.error('Error:', error);
                    deletePopup.style.display = 'none';
                    window.showToast('An error occurred while deleting the ground.', 'error');
                    deleteGroundId = null;
                });
            }
        });
    }

    // Edit ground button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-ground-btn')) {
            e.preventDefault();
            const groundId = e.target.closest('.edit-ground-btn').getAttribute('data-ground-id');

            // Show loading state
            const editBtn = e.target.closest('.edit-ground-btn');
            const originalHtml = editBtn.innerHTML;
            editBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            editBtn.disabled = true;

            // Fetch ground data
            fetch(`/admin/grounds/${groundId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                editBtn.innerHTML = originalHtml;
                editBtn.disabled = false;

                if (data.status === 'success') {
                    // Show the modal with ground data
                    openEditGroundModal(data.ground);
                } else {
                    window.showToast(data.message || 'Failed to fetch ground data.', 'error');
                }
            })
            .catch(error => {
                // Reset button state
                editBtn.innerHTML = originalHtml;
                editBtn.disabled = false;

                console.error('Error:', error);
                window.showToast('An error occurred while fetching ground data.', 'error');
            });
        }
    });

    // Add ground button click handler
    const addGroundBtn = document.querySelector('.add-ground-btn');
    if (addGroundBtn) {
        addGroundBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openAddGroundModal();
        });
    }

    // Function to open edit ground modal
    function openEditGroundModal(ground) {
        // Load clients for dropdown before filling form data
        loadClients().then(() => {
            // Update modal title
            const modalTitle = document.getElementById('ground-modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Edit Ground';
            }

            // Fill form with ground data
            document.getElementById('ground_id').value = ground.id;
            document.getElementById('name').value = ground.name;
            document.getElementById('location').value = ground.location;
            document.getElementById('price_per_hour').value = ground.price_per_hour;
            document.getElementById('capacity').value = ground.capacity;
            document.getElementById('ground_type').value = ground.ground_type || '';
            document.getElementById('description').value = ground.description || '';
            document.getElementById('rules').value = ground.rules || '';
            document.getElementById('opening_time').value = ground.opening_time || '';
            document.getElementById('closing_time').value = ground.closing_time || '';
            document.getElementById('status').value = ground.status;
            document.getElementById('phone').value = ground.phone || '';
            document.getElementById('email').value = ground.email || '';

            // Set hidden field values
            document.getElementById('is_new').value = ground.is_new === true || ground.is_new === 1 ? '1' : '0';
            document.getElementById('is_featured').value = ground.is_featured === true || ground.is_featured === 1 ? '1' : '0';

            // Set the appropriate radio button
            if (ground.is_new === true || ground.is_new === 1) {
                document.getElementById('ground_classification_new').checked = true;
            } else if (ground.is_featured === true || ground.is_featured === 1) {
                document.getElementById('ground_classification_featured').checked = true;
            } else {
                document.getElementById('ground_classification_none').checked = true;
            }

            // Set client if available
            if (ground.client_id && document.getElementById('client_id')) {
                document.getElementById('client_id').value = ground.client_id;
            }

            // Display existing images in preview
            const imagesPreviewContainer = document.getElementById('images-preview-container');
            if (imagesPreviewContainer) {
                // Clear any existing previews
                imagesPreviewContainer.innerHTML = '';

                // Add previews for existing images
                if (ground.images && ground.images.length > 0) {
                    ground.images.forEach(image => {
                        // Create preview container
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'relative';
                        previewDiv.dataset.imageId = image.id;

                        // Create image preview
                        const img = document.createElement('img');
                        img.src = image.image_path;
                        img.className = 'w-full h-24 object-cover rounded-lg';
                        img.alt = 'Ground image';

                        // Create remove button
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600';
                        removeBtn.innerHTML = '<i class="fas fa-times"></i>';

                        // Add a label to indicate it's an existing image
                        const existingLabel = document.createElement('span');
                        existingLabel.className = 'absolute bottom-1 left-1 bg-blue-600 text-white text-xs px-2 py-1 rounded-md';
                        existingLabel.textContent = 'Saved';

                        // Append elements to the preview div
                        previewDiv.appendChild(img);
                        previewDiv.appendChild(removeBtn);
                        previewDiv.appendChild(existingLabel);

                        // Append preview div to the container
                        imagesPreviewContainer.appendChild(previewDiv);

                        // Add event listener to remove button
                        removeBtn.addEventListener('click', function() {
                            if (confirm('Are you sure you want to remove this image? This will be permanent after saving.')) {
                                // Add a hidden input to track deleted images
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'delete_images[]';
                                hiddenInput.value = image.id;
                                document.getElementById('ground-form').appendChild(hiddenInput);

                                // Remove the preview
                                previewDiv.remove();
                            }
                        });
                    });
                }
            }

            // Reset dynamic fields before populating
            resetDynamicFields();

            // Populate slots if available
            if (ground.slots && ground.slots.length > 0) {
                // Use our new function to populate slots
                window.populateGroundSlots(ground.slots);
            }

            // Populate features if available
            if (ground.features && ground.features.length > 0) {
                const featuresContainer = document.getElementById('features-container');
                if (featuresContainer) {
                    // Remove the default first feature input that comes with resetDynamicFields()
                    const firstFeature = featuresContainer.querySelector('.feature-input-group');
                    if (firstFeature) {
                        featuresContainer.removeChild(firstFeature);
                    }

                    // Add each feature
                    ground.features.forEach((feature, index) => {
                        // Create feature input group
                        const featureGroup = document.createElement('div');
                        featureGroup.className = 'feature-input-group flex items-center space-x-2 mb-2';

                        featureGroup.innerHTML = `
                            <input type="text" name="feature_name[]" value="${feature.feature_name}"
                                placeholder="Feature Name" class="flex-grow rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <select name="feature_type[]" class="rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                                <option value="facility" ${feature.feature_type === 'facility' ? 'selected' : ''}>Facility</option>
                                <option value="equipment" ${feature.feature_type === 'equipment' ? 'selected' : ''}>Equipment</option>
                                <option value="service" ${feature.feature_type === 'service' ? 'selected' : ''}>Service</option>
                            </select>
                            <button type="button" class="remove-feature-btn px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:outline-none">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;

                        featuresContainer.appendChild(featureGroup);

                        // Add event listener to the remove button
                        const removeBtn = featureGroup.querySelector('.remove-feature-btn');
                        if (removeBtn) {
                            removeBtn.addEventListener('click', function() {
                                featureGroup.remove();
                            });
                        }
                    });
                }
            }

            // Display current images if available
            if (ground.images && ground.images.length > 0) {
                const currentImagesContainer = document.getElementById('current-images-container');
                if (currentImagesContainer) {
                    // Clear container
                    currentImagesContainer.innerHTML = '';

                    // Add heading
                    const heading = document.createElement('h4');
                    heading.className = 'text-sm font-medium text-gray-700 mb-2';
                    heading.textContent = 'Current Images';
                    currentImagesContainer.appendChild(heading);

                    // Create image grid
                    const imageGrid = document.createElement('div');
                    imageGrid.className = 'grid grid-cols-3 gap-2 mb-4';
                    currentImagesContainer.appendChild(imageGrid);

                    // Add each image
                    ground.images.forEach(image => {
                        const imageCard = document.createElement('div');
                        imageCard.className = 'relative border rounded-lg overflow-hidden';
                        imageCard.innerHTML = `
                            <img src="${image.image_path}" alt="Ground Image" class="w-full h-24 object-cover">
                            <button type="button" data-image-id="${image.id}" class="remove-image-btn absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        imageGrid.appendChild(imageCard);

                        // Add event listener to remove button
                        const removeBtn = imageCard.querySelector('.remove-image-btn');
                        if (removeBtn) {
                            removeBtn.addEventListener('click', function() {
                                const imageId = this.getAttribute('data-image-id');
                                // Add confirmation if needed
                                deleteGroundImage(imageId, imageCard);
                            });
                        }
                    });

                    // Make the container visible
                    currentImagesContainer.style.display = 'block';
                }
            }

            // Show the modal using our new function
            if (window.showGroundModal) {
                window.showGroundModal();
            } else {
                // Fallback
                const modal = document.getElementById('ground-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                }
            }
        });
    }

    // Function to load clients for dropdown
    function loadClients() {
        return new Promise((resolve, reject) => {
            const clientDropdown = document.getElementById('client_id');
            if (clientDropdown) {
                // Clear existing options except first one
                while (clientDropdown.options.length > 1) {
                    clientDropdown.remove(1);
                }

                // Fetch clients from API
                fetch('/api/clients', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    data.forEach(client => {
                        const option = document.createElement('option');
                        option.value = client.id;
                        option.textContent = client.name;
                        clientDropdown.appendChild(option);
                    });
                    resolve();
                })
                .catch(error => {
                    console.error('Error loading clients:', error);
                    reject(error);
                });
            } else {
                resolve(); // Resolve immediately if dropdown doesn't exist
            }
        });
    }

    // Function to delete a ground image
    function deleteGroundImage(imageId, imageElement) {
        fetch(`/admin/ground-images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove the image from the DOM
                if (imageElement) {
                    imageElement.remove();
                }
                window.showToast(data.message || 'Image deleted successfully', 'success');

                // If no more images, hide the container
                const imageGrid = document.querySelector('#current-images-container .grid');
                if (imageGrid && imageGrid.children.length === 0) {
                    document.getElementById('current-images-container').style.display = 'none';
                }
            } else {
                window.showToast(data.message || 'Failed to delete image', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showToast('An error occurred while deleting the image', 'error');
        });
    }

    // Function to open add ground modal
    function openAddGroundModal() {
        // Update modal title
        const modalTitle = document.getElementById('ground-modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add New Ground';
        }

        // Reset form
        document.getElementById('ground-form').reset();
        document.getElementById('ground_id').value = '';

        // Reset dynamic fields
        resetDynamicFields();

        // Clear current images container if it exists
        const currentImagesContainer = document.getElementById('current-images-container');
        if (currentImagesContainer) {
            currentImagesContainer.innerHTML = '';
            currentImagesContainer.style.display = 'none';
        }

        // Load clients for the dropdown
        loadClients().then(() => {
            // Show the modal using our new function
            if (window.showGroundModal) {
                window.showGroundModal();
            } else {
                // Fallback
                const modal = document.getElementById('ground-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                }
            }
        });
    }

    // Close ground modal
    const closeButtons = document.querySelectorAll('.close-ground-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            closeGroundModal();
        });
    });

    function closeGroundModal() {
        const modal = document.getElementById('ground-modal');
        if (modal) {
            window.closeModalWithAnimation(modal);
        }
    }

    // View ground modal handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.view-ground-btn')) {
            e.preventDefault();
            const groundId = e.target.closest('.view-ground-btn').getAttribute('data-ground-id');

            // Fetch ground data
            fetch(`/admin/grounds/${groundId}/view`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    openViewGroundModal(data.ground, data.bookings || [], data.bookings_count || 0);
                } else {
                    window.showToast(data.message || 'Failed to fetch ground data.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showToast('An error occurred while fetching ground data.', 'error');
            });
        }
    });

    function openViewGroundModal(ground, bookings, bookingsCount) {
        // Set ground details in the modal
        document.getElementById('ground-view-name').textContent = ground.name;
        document.getElementById('ground-view-location').textContent = ground.location;

        // Set ground image
        const groundImage = document.getElementById('ground-view-image');
        const groundInitial = document.getElementById('ground-initial');

        // Get all ground images if available
        let imageUrl = null;

        if (ground.ground_image) {
            // Use the main ground image if it exists
            imageUrl = ground.ground_image;
            groundImage.src = imageUrl;
            groundImage.classList.remove('hidden');
            groundInitial.classList.add('hidden');
        } else if (ground.images && ground.images.length > 0) {
            // If main image doesn't exist but we have additional images
            // Get a random image from the array
            const randomIndex = Math.floor(Math.random() * ground.images.length);
            imageUrl = ground.images[randomIndex].image_path;
            groundImage.src = imageUrl;
            groundImage.classList.remove('hidden');
            groundInitial.classList.add('hidden');
        } else {
            // No image at all, show the first letter of the ground name
            groundImage.classList.add('hidden');
            groundInitial.classList.remove('hidden');
            groundInitial.textContent = ground.name.charAt(0).toUpperCase();
        }

        // Set status badge
        const statusBadge = document.getElementById('ground-view-status-badge');
        if (statusBadge) {
            statusBadge.className = `px-3 py-1 text-xs rounded-full font-medium ${getStatusClass(ground.status)}`;
            statusBadge.textContent = ground.status.charAt(0).toUpperCase() + ground.status.slice(1);
        }

        // Fill in other details
        document.getElementById('ground-view-full-name').textContent = ground.name;
        document.getElementById('ground-view-location-full').textContent = ground.location;
        document.getElementById('ground-view-price').textContent = `₹${ground.price_per_hour} per hour`;
        document.getElementById('ground-view-capacity').textContent = `${ground.capacity} people`;
        document.getElementById('ground-view-type').textContent = ground.ground_type || 'Standard';
        document.getElementById('ground-view-description').textContent = ground.description || 'No description provided';
        document.getElementById('ground-view-rules').textContent = ground.rules || 'No specific rules';
        document.getElementById('ground-view-hours').textContent = ground.opening_time && ground.closing_time ?
            `${ground.opening_time} - ${ground.closing_time}` : 'Not specified';
        document.getElementById('ground-view-created').textContent = ground.created_at || 'Unknown';

        // Set status badges for new/featured
        const isNewBadge = document.getElementById('ground-view-is-new');
        const isFeaturedBadge = document.getElementById('ground-view-is-featured');
        const isStandardBadge = document.getElementById('ground-view-is-standard');

        if (isNewBadge && isFeaturedBadge && isStandardBadge) {
            isNewBadge.classList.add('hidden');
            isFeaturedBadge.classList.add('hidden');
            isStandardBadge.classList.add('hidden');

            if (ground.is_new) {
                isNewBadge.classList.remove('hidden');
            } else if (ground.is_featured) {
                isFeaturedBadge.classList.remove('hidden');
            } else {
                isStandardBadge.classList.remove('hidden');
            }
        }

        // Handle slabs (slots)
        const noSlabsMessage = document.getElementById('no-slabs-message');
        const slabsList = document.getElementById('ground-slabs-list');
        const slabsGrid = document.getElementById('ground-slabs-grid');

        if (ground.slots && ground.slots.length > 0) {
            noSlabsMessage.classList.add('hidden');
            slabsList.classList.remove('hidden');

            // Clear previous slabs
            slabsGrid.innerHTML = '';

            // Add slabs to grid
            ground.slots.forEach(slot => {
                const slabItem = document.createElement('div');
                slabItem.className = 'bg-indigo-50 border border-indigo-100 rounded-md p-3';

                // Get color for slot type
                let typeColorClass = '';
                switch(slot.slot_type) {
                    case 'morning':
                        typeColorClass = 'bg-yellow-100 text-yellow-800';
                        break;
                    case 'afternoon':
                        typeColorClass = 'bg-orange-100 text-orange-800';
                        break;
                    case 'evening':
                        typeColorClass = 'bg-purple-100 text-purple-800';
                        break;
                    case 'night':
                        typeColorClass = 'bg-blue-100 text-blue-800';
                        break;
                    default:
                        typeColorClass = 'bg-gray-100 text-gray-800';
                }

                slabItem.innerHTML = `
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium">${slot.slot_name}</span>
                        <span class="px-2 py-1 text-xs rounded-full ${typeColorClass}">
                            ${slot.slot_type.charAt(0).toUpperCase() + slot.slot_type.slice(1)}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500">
                        ${slot.slot_status === 'active' ?
                            '<span class="text-green-600"><i class="fas fa-circle text-xs mr-1"></i>Available</span>' :
                            '<span class="text-red-600"><i class="fas fa-circle text-xs mr-1"></i>Unavailable</span>'}
                    </div>
                `;

                slabsGrid.appendChild(slabItem);
            });
        } else {
            noSlabsMessage.classList.remove('hidden');
            slabsList.classList.add('hidden');
        }

        // Handle bookings
        const noBookingsMessage = document.getElementById('no-bookings-message');
        const bookingsList = document.getElementById('ground-bookings-list');
        const bookingsTable = document.getElementById('ground-bookings-table');

        if (bookings && bookings.length > 0) {
            noBookingsMessage.classList.add('hidden');
            bookingsList.classList.remove('hidden');

            // Clear previous bookings
            bookingsTable.innerHTML = '';

            // Add bookings to table
            bookings.forEach(booking => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                row.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-sm">#${booking.id}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">${booking.date}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">${booking.time}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">${booking.client_name}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(booking.status)} font-medium">
                            ${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">₹${booking.amount}</td>
                `;

                bookingsTable.appendChild(row);
            });
        } else {
            noBookingsMessage.classList.remove('hidden');
            bookingsList.classList.add('hidden');
        }

        // Set data-ground-id attribute on edit button
        const editButton = document.querySelector('.edit-from-view-btn');
        if (editButton && ground.id) {
            editButton.setAttribute('data-ground-id', ground.id);
        }

        // Show the modal
        const modal = document.getElementById('ground-view-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'active':
                return 'bg-green-100 text-green-800';
            case 'inactive':
                return 'bg-yellow-100 text-yellow-800';
            case 'under_maintenance':
                return 'bg-orange-100 text-orange-800';
            case 'pending':
                return 'bg-blue-100 text-blue-800';
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    // Close view modal
    const closeViewButtons = document.querySelectorAll('.close-view-modal');
    closeViewButtons.forEach(button => {
        button.addEventListener('click', function() {
            closeViewModal();
        });
    });

    function closeViewModal() {
        const modal = document.getElementById('ground-view-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Edit from view button handler
    const editFromViewBtn = document.querySelector('.edit-from-view-btn');
    if (editFromViewBtn) {
        editFromViewBtn.addEventListener('click', function() {
            // Close view modal
            closeViewModal();

            // Get ground ID from the button
            const groundId = this.getAttribute('data-ground-id');

            if (!groundId) {
                console.error("No ground ID found on edit button");
                window.showToast('Error: Could not determine which ground to edit', 'error');
                return;
            }

            // Show loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Loading...';
            this.disabled = true;

            // Fetch ground data and open edit modal
            fetch(`/admin/grounds/${groundId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                this.innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Ground';
                this.disabled = false;

                if (data.status === 'success') {
                    openEditGroundModal(data.ground);
                } else {
                    window.showToast(data.message || 'Failed to fetch ground data.', 'error');
                }
            })
            .catch(error => {
                // Reset button state
                this.innerHTML = '<i class="fas fa-edit mr-2"></i> Edit Ground';
                this.disabled = false;

                console.error('Error:', error);
                window.showToast('An error occurred while fetching ground data.', 'error');
            });
        });
    }

    // Attach event listeners to table elements
    function attachTableEventListeners() {
        // Delete buttons
        const deleteButtons = document.querySelectorAll('.delete-ground-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteGroundId = this.getAttribute('data-ground-id');
                deletePopup.style.display = 'block';
            });
        });
    }

    // Initial attachment of event listeners
    attachTableEventListeners();

    // Handle drag and drop / multiple images preview
    const imagesInput = document.getElementById('ground_images');
    const imagesPreviewContainer = document.getElementById('images-preview-container');
    const imageDropZone = document.getElementById('image-drop-zone');
    const selectedFilesCounter = document.getElementById('selected-files-counter');
    const fileCountElement = document.getElementById('file-count');
    const previewHeading = document.getElementById('preview-heading');

    // Array to store all selected files
    let selectedFiles = [];

    if (imagesInput && imagesPreviewContainer && imageDropZone) {
        // Click on drop zone to trigger file input
        imageDropZone.addEventListener('click', function(e) {
            // Prevent click from propagating if it's on the button
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                console.log('Button inside drop zone clicked - not triggering file input directly');
                // The button has its own click handler
                return;
            } else if (e.target === imageDropZone || imageDropZone.contains(e.target)) {
                console.log('Drop zone clicked - triggering file input');
                imagesInput.click();
            }
        });

        // Handle drag and drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            imageDropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop zone when dragging over it
        ['dragenter', 'dragover'].forEach(eventName => {
            imageDropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            imageDropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            imageDropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        }

        function unhighlight() {
            imageDropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        }

        // Handle dropped files
        imageDropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                console.log(`Dropped ${files.length} files`);

                // Process each dropped file
                for (let i = 0; i < files.length; i++) {
                    if (files[i].type.match('image.*')) {
                        // Generate a unique ID for this file
                        const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substring(2, 10) + '_' + i;

                        // Store the file with its ID for later reference
                        selectedFiles.push({
                            id: fileId,
                            file: files[i]
                        });

                        // Create preview for this file
                        createFilePreview(files[i], fileId);

                        console.log(`Added dropped file: ${files[i].name}`);
            } else {
                        console.log(`Dropped file ${files[i].name} is not an image, skipping`);
                    }
                }

                // Update files counter
                updateFileCounter();
            }
        }

        // Handle file selection via input
        imagesInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                console.log(`Selected ${this.files.length} files via input`);

                // Process each selected file
                for (let i = 0; i < this.files.length; i++) {
                    if (this.files[i].type.match('image.*')) {
                        // Generate a unique ID for this file
                        const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substring(2, 10) + '_' + i;

                        // Store the file with its ID for later reference
                        selectedFiles.push({
                            id: fileId,
                            file: this.files[i]
                        });

                        // Create preview for this file
                        createFilePreview(this.files[i], fileId);

                        console.log(`Added selected file: ${this.files[i].name}`);
        } else {
                        console.log(`File ${this.files[i].name} is not an image, skipping`);
                    }
                }

                // Update files counter
                updateFileCounter();

                // Clear the input so the change event fires even if the same file is selected again
                this.value = '';
            }
        });

        // Function to create a preview for a file
        function createFilePreview(file, fileId) {
            const reader = new FileReader();

            reader.onload = function(e) {
                // Create preview container
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group';
                previewDiv.dataset.fileId = fileId;

                // Create image preview
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'w-full h-24 object-cover rounded-lg border border-gray-200';
                img.alt = file.name;

                // Create remove button that appears on hover
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.title = 'Remove image';

                // Add file name tooltip
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute bottom-0 left-0 right-0 bg-black bg-opacity-70 text-white text-xs p-1 rounded-b-lg truncate opacity-0 group-hover:opacity-100 transition-opacity';
                tooltip.textContent = file.name;

                // Append image and buttons to the preview div
                previewDiv.appendChild(img);
                previewDiv.appendChild(removeBtn);
                previewDiv.appendChild(tooltip);

                // Append preview div to the container
                imagesPreviewContainer.appendChild(previewDiv);

                // Add event listener to remove button
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Remove this file from selectedFiles array
                    selectedFiles = selectedFiles.filter(item => item.id !== fileId);

                    // Remove the preview from DOM
                    previewDiv.remove();

                    // Update file counter
                    updateFileCounter();
                });
            };

            reader.readAsDataURL(file);
        }

        // Function to update the file counter
        function updateFileCounter() {
            if (selectedFiles.length > 0) {
                fileCountElement.textContent = selectedFiles.length;
                selectedFilesCounter.classList.remove('hidden');
                previewHeading.classList.remove('hidden');

                // Remove any "no images" message
                const noImagesMsg = imagesPreviewContainer.querySelector('.text-center.text-gray-500');
                if (noImagesMsg) {
                    noImagesMsg.remove();
                }
            } else {
                selectedFilesCounter.classList.add('hidden');
                previewHeading.classList.add('hidden');

                // Clear the preview container
                imagesPreviewContainer.innerHTML = '';

                // Add "no images selected" message
                const noImagesMsg = document.createElement('p');
                noImagesMsg.className = 'text-center text-gray-500 my-3';
                noImagesMsg.textContent = 'No images selected';
                imagesPreviewContainer.appendChild(noImagesMsg);
            }
        }
    }

    // Function to reset selected files array and previews
    function resetSelectedFiles() {
        // Clear the selected files array
        selectedFiles = [];

        // Clear the previews container
        if (imagesPreviewContainer) {
            imagesPreviewContainer.innerHTML = '';
        }

        // Update the file counter
        if (typeof updateFileCounter === 'function') {
            updateFileCounter();
        } else {
            // Fallback if updateFileCounter is not accessible
            if (selectedFilesCounter) selectedFilesCounter.classList.add('hidden');
            if (previewHeading) previewHeading.classList.add('hidden');
            if (fileCountElement) fileCountElement.textContent = '0';
        }
    }
});
