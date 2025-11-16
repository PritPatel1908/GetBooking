document.addEventListener('DOMContentLoaded', function() {
    // Check for message in localStorage (for showing toast after redirect)
    if (localStorage.getItem('clientActionMessage')) {
        window.showToast(
            localStorage.getItem('clientActionMessage'),
            localStorage.getItem('clientActionType') || 'success'
        );

        // Clear the message from localStorage
        localStorage.removeItem('clientActionMessage');
        localStorage.removeItem('clientActionType');
    }

    // Client form submission handling
    const clientForm = document.getElementById('client-form');

    if (clientForm) {
        // Remove any existing listeners to prevent duplication
        const newClientForm = clientForm.cloneNode(true);
        clientForm.parentNode.replaceChild(newClientForm, clientForm);

        // Set the new reference
        const refreshedClientForm = document.getElementById('client-form');

        refreshedClientForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // Prevent other handlers from firing

            // Clear previous error messages
            clearErrorMessages();

            // Get the form data
            const formData = new FormData(refreshedClientForm);

            // Disable submit button and show loading
            const submitBtn = document.querySelector('button[type="submit"][form="client-form"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

            // Send AJAX request
            fetch('/admin/clients/create', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        // Validation errors - will be handled in the next then block
                        return response.json();
                    } else if (response.status >= 500) {
                        throw new Error('Server error. Please try again later.');
                    } else {
                        throw new Error('An error occurred. Please try again.');
                    }
                }
                return response.json();
            })
            .then(data => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (data.status === 'success') {
                    // Close modal ONLY on success
                    const modal = document.getElementById('client-modal');
                    if (modal) {
                        modal.classList.remove('active');
                        setTimeout(() => {
                            modal.classList.add('hidden');
                        }, 300);
                    }

                    // Show success message
                    window.showToast(data.message, 'success');

                    // Check if we're on the clients list page
                    const isClientListPage = window.location.pathname === '/admin/clients' ||
                                           window.location.pathname === '/admin/clients/';

                    if (isClientListPage) {
                        // Reload only the table instead of the entire page
                        reloadClientTable();
                    } else {
                        // If it's a new client, add to table
                        if (!formData.get('client_id')) {
                            addClientToTable(data.client);
                        } else {
                            // Update client in table
                            updateClientInTable(data.client);
                        }
                    }

                    // Reset form
                    refreshedClientForm.reset();
                } else if (data.status === 'error') {
                    // Display validation errors
                    if (data.errors) {
                        displayValidationErrors(data.errors);
                    }

                    // Display general error message if provided
                    if (data.message) {
                        displayFormErrors(data.message);
                    }

                    // Ensure modal stays open
                    const modal = document.getElementById('client-modal');
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.classList.add('active');
                    }

                    // Highlight the form to indicate errors
                    const formContainer = refreshedClientForm.closest('.p-6');
                    if (formContainer) {
                        formContainer.classList.add('has-validation-errors');
                        setTimeout(() => {
                            formContainer.classList.remove('has-validation-errors');
                        }, 2000);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                // Display the error message
                displayFormErrors(error.message || 'An error occurred while saving the client.');
                window.showToast(error.message || 'An error occurred while saving the client.', 'error');
            });
        });
    }

    // Function to clear error messages
    function clearErrorMessages() {
        // Remove all error message elements
        document.querySelectorAll('.error-message').forEach(el => el.remove());

        // Reset all form inputs with error styling
        document.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
            el.classList.add('border-gray-300');
        });

        // Also clear any error classes from select elements and other form elements
        document.querySelectorAll('select.border-red-500, textarea.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
            el.classList.add('border-gray-300');
        });

        // Remove any form-level error messages
        const formErrors = document.querySelector('#form-errors');
        if (formErrors) {
            formErrors.innerHTML = '';
            formErrors.classList.add('hidden');
        }
    }

    // Function to display validation errors
    function displayValidationErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            // Try finding the input field by id first
            let inputField = document.getElementById(field);

            // If not found by id, try finding by name attribute
            if (!inputField) {
                inputField = document.querySelector(`[name="${field}"]`);
            }

            // For array fields like feature_name.0, feature_name.1, etc.
            if (!inputField && field.includes('.')) {
                const [baseName, index] = field.split('.');
                inputField = document.querySelector(`[name="${baseName}[${index}]"]`);
            }

            if (inputField) {
                // Add red border to the input field
                inputField.classList.remove('border-gray-300');
                inputField.classList.add('border-red-500');

                // Add error message below the input
                const errorMessage = document.createElement('p');
                errorMessage.classList.add('text-red-500', 'text-xs', 'mt-1', 'error-message');
                errorMessage.textContent = messages[0]; // Get first error message

                // If this is a select field or radio/checkbox that might be wrapped in a container
                const container = inputField.closest('.form-group') || inputField.parentNode;

                // Insert error message after the input field or its container
                container.insertBefore(errorMessage, inputField.nextSibling);

                // Focus on the first field with an error
                if (document.querySelectorAll('.error-message').length === 1) {
                    inputField.focus();
                }
            } else {
                // If field not found, show a general error
                console.warn(`Field ${field} not found in the form`);
            }
        }
    }

    // Function to display general form errors
    function displayFormErrors(errorMessage) {
        // Look for a form-errors container or create one
        let formErrors = document.querySelector('#form-errors');

        if (!formErrors) {
            // Create a container for form-level errors
            formErrors = document.createElement('div');
            formErrors.id = 'form-errors';
            formErrors.className = 'bg-red-50 border border-red-300 text-red-600 p-3 rounded-md mb-4 text-sm';

            // Insert at the top of the form
            const form = document.getElementById('client-form');
            if (form) {
                form.insertBefore(formErrors, form.firstChild);
            }
        } else {
            // Make sure it's visible
            formErrors.classList.remove('hidden');
        }

        // Set the error message with an icon
        formErrors.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                <p>${errorMessage}</p>
            </div>
        `;

        // Add animation
        formErrors.style.animation = 'none';
        setTimeout(() => {
            formErrors.style.animation = 'fadeIn 0.3s ease-in-out';
        }, 10);
    }

    // Function to add a new client to the table
    function addClientToTable(client) {
        const tbody = document.querySelector('table tbody');

        if (tbody) {
            // Check if we need to remove the "no clients" message
            const emptyRow = tbody.querySelector('tr td[colspan="7"]');
            if (emptyRow) {
                emptyRow.parentNode.remove();
            }

            const newRow = document.createElement('tr');
            newRow.classList.add('hover:bg-gray-50', 'transition-colors', 'table-row-appear');
            newRow.dataset.clientId = client.id;

            // Create avatar URL
            let avatarUrl;
            if (client.profile_picture) {
                avatarUrl = client.profile_picture;
            } else {
                // Use UI Avatars as fallback
                avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(client.name)}&color=7F9CF5&background=EBF4FF`;
            }

            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img class="h-10 w-10 rounded-full mr-3" src="${avatarUrl}" alt="${client.name}">
                        <div>
                            <div class="font-medium text-gray-900">${client.name}</div>
                            <div class="text-sm text-gray-500">${client.gender ? client.gender.charAt(0).toUpperCase() + client.gender.slice(1) : 'Individual'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${client.email}</td>
                <td class="px-6 py-4 whitespace-nowrap">${client.phone}</td>
                <td class="px-6 py-4 whitespace-nowrap">${client.registration_date}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">0 bookings</div>
                    <div class="text-xs text-gray-500">No bookings yet</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full ${client.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'} font-medium">
                        ${client.status.charAt(0).toUpperCase() + client.status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
                        <a href="/admin/clients/${client.id}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Client">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="text-blue-600 hover:text-blue-900 transition-colors edit-client-btn" title="Edit Client" data-client-id="${client.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900 transition-colors delete-client-btn" title="Delete Client" data-client-id="${client.id}">
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
        }
    }

    // Function to update existing client in the table
    function updateClientInTable(client) {
        const row = document.querySelector(`tr[data-client-id="${client.id}"]`);

        if (row) {
            // Update the client data in the row
            const nameCell = row.querySelector('.font-medium.text-gray-900');
            if (nameCell) nameCell.textContent = client.name;

            const emailCell = row.querySelector('td:nth-child(2)');
            if (emailCell) emailCell.textContent = client.email;

            const phoneCell = row.querySelector('td:nth-child(3)');
            if (phoneCell) phoneCell.textContent = client.phone;

            const statusBadge = row.querySelector('td:nth-child(6) span');
            if (statusBadge) {
                // Update status badge
                statusBadge.className = `px-2 py-1 text-xs rounded-full ${client.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'} font-medium`;
                statusBadge.textContent = client.status.charAt(0).toUpperCase() + client.status.slice(1);
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

    var deletePopup = document.getElementById('delete-popup');
    var cancelDelete = document.getElementById('cancel-delete');
    var confirmDelete = document.getElementById('confirm-delete');
    var deleteClientId = null;

    // Add click handlers to all delete buttons
    var deleteButtons = document.querySelectorAll('.delete-client-btn');
    for (var i = 0; i < deleteButtons.length; i++) {
        deleteButtons[i].addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            deleteClientId = this.getAttribute('data-client-id');
            deletePopup.style.display = 'block';
        });
    }

    // Cancel button closes the popup
    cancelDelete.addEventListener('click', function() {
        deletePopup.style.display = 'none';
        deleteClientId = null;
    });

    // Confirm delete
    confirmDelete.addEventListener('click', function() {
        if (!deleteClientId) return;

        // Change button text to loading
        this.innerHTML = 'Deleting...';
        this.disabled = true;

        // Delete via AJAX using vanilla JS
        var xhr = new XMLHttpRequest();
        xhr.open('DELETE', '/admin/clients/' + deleteClientId, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);

                if (response.status === 'success') {
                    // Hide popup
                    deletePopup.style.display = 'none';

                    // Check current URL pattern
                    const currentPath = window.location.pathname;
                    const isClientDetailPage = /^\/admin\/clients\/\d+$/.test(currentPath);

                    if (isClientDetailPage) {
                        // If on client detail page, redirect to clients list
                        // Store the message in localStorage to show after redirect
                        localStorage.setItem('clientActionMessage', response.message || 'Client deleted successfully');
                        localStorage.setItem('clientActionType', 'success');
                        window.location.href = '/admin/clients';
                    } else {
                        // If on clients list page, reload only the table
                        window.showToast(response.message || 'Client deleted successfully', 'success');
                        reloadClientTable();
                    }
                } else {
                    // Show error
                    alert(response.message || 'Error deleting client');
                }
            } else {
                alert('An error occurred while deleting the client');
            }

            // Reset button
            confirmDelete.innerHTML = 'Delete Client';
            confirmDelete.disabled = false;
            deleteClientId = null;
        };

        xhr.onerror = function() {
            alert('An error occurred while deleting the client');
            confirmDelete.innerHTML = 'Delete Client';
            confirmDelete.disabled = false;
            deletePopup.style.display = 'none';
            deleteClientId = null;
        };

        xhr.send();
    });

    // Close popup when clicking outside
    deletePopup.addEventListener('click', function(e) {
        if (e.target === deletePopup) {
            deletePopup.style.display = 'none';
            deleteClientId = null;
        }
    });

    // Set up event delegation for edit client buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-client-btn')) {
            const btn = e.target.closest('.edit-client-btn');
            const clientId = btn.getAttribute('data-client-id');

            if (clientId) {
                // Show loading state
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                // Fetch client data
                fetch(`/admin/clients/${clientId}/edit`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    btn.innerHTML = '<i class="fas fa-edit"></i>';
                    btn.disabled = false;

                    if (data.status === 'success') {
                        // Open and populate the modal directly
                        openEditClientModal(data.client);
                    } else {
                        window.showToast(data.message || 'Failed to load client data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = '<i class="fas fa-edit"></i>';
                    btn.disabled = false;
                    window.showToast('An error occurred while loading client data', 'error');
                });
            }
        }

        // Handle view client button clicks
        if (e.target.closest('.view-client-btn')) {
            const btn = e.target.closest('.view-client-btn');
            const clientId = btn.getAttribute('data-client-id');

            if (clientId) {
                // Show loading state
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                // Fetch client data
                fetch(`/admin/clients/${clientId}/view`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    btn.innerHTML = '<i class="fas fa-eye"></i>';
                    btn.disabled = false;

                    if (data.status === 'success') {
                        // Open and populate the view modal
                        openViewClientModal(data.client, data.bookings, data.bookings_count);
                    } else {
                        window.showToast(data.message || 'Failed to load client data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = '<i class="fas fa-eye"></i>';
                    btn.disabled = false;
                    window.showToast('An error occurred while loading client data', 'error');
                });
            }
        }

        // Handle add client button
        if (e.target.closest('.add-client-btn')) {
            openAddClientModal();
        }

        // Handle close modal buttons
        if (e.target.closest('.close-client-modal')) {
            closeClientModal();
        }

        // Handle close view modal buttons
        if (e.target.closest('.close-view-modal')) {
            closeViewModal();
        }

        // Handle edit from view button
        if (e.target.closest('.edit-from-view-btn')) {
            const clientId = document.querySelector('#client-view-modal').getAttribute('data-client-id');
            if (clientId) {
                closeViewModal();

                // Show loading state on edit button
                const editBtn = document.querySelector(`.edit-client-btn[data-client-id="${clientId}"]`);
                if (editBtn) {
                    editBtn.click();
                }
            }
        }

        // Handle delete from view modal
        if (e.target.closest('#delete-client-btn')) {
            const clientId = document.querySelector('#client-view-modal').getAttribute('data-client-id');
            if (clientId) {
                // Change button text to loading
                const deleteBtn = e.target.closest('#delete-client-btn');
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Deleting...';
                deleteBtn.disabled = true;

                // Delete via AJAX
                fetch(`/admin/clients/${clientId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Close the modal
                        closeViewModal();

                        // Show success message
                        window.showToast(data.message || 'Client deleted successfully', 'success');

                        // Check current URL pattern
                        const currentPath = window.location.pathname;
                        const isClientDetailPage = /^\/admin\/clients\/\d+$/.test(currentPath);

                        if (isClientDetailPage) {
                            // If on client detail page, redirect to clients list
                            // Store the message in localStorage to show after redirect
                            localStorage.setItem('clientActionMessage', data.message || 'Client deleted successfully');
                            localStorage.setItem('clientActionType', 'success');
                            window.location.href = '/admin/clients';
                        } else {
                            // If on clients list page, reload only the table
                            reloadClientTable();
                        }
                    } else {
                        // Show error
                        window.showToast(data.message || 'Error deleting client', 'error');

                        // Reset button
                        deleteBtn.innerHTML = '<i class="fas fa-trash-alt mr-2"></i> Delete Client';
                        deleteBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.showToast('An error occurred while deleting the client', 'error');

                    // Reset button
                    deleteBtn.innerHTML = '<i class="fas fa-trash-alt mr-2"></i> Delete Client';
                    deleteBtn.disabled = false;
                });
            }
        }

        // For testing - Add a reload table button handler if it exists
        if (e.target.closest('#reload-table-btn')) {
            e.preventDefault();
            window.showToast('Reloading client table...', 'info');
            reloadClientTable();
        }
    });

    // Function to open the edit client modal
    function openEditClientModal(client) {
        const modal = document.getElementById('client-modal');
        if (!modal) return;

        // Set the modal title
        const modalTitle = document.getElementById('client-modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Edit Client';
        }

        // Set the client ID in the form
        document.getElementById('client_id').value = client.id;

        // Add class to form container for validation styling
        const clientForm = document.getElementById('client-form');
        if (clientForm) {
            // Add client-form-container class to the parent element for validation styling
            const formContainer = clientForm.closest('.p-6');
            if (formContainer) {
                formContainer.classList.add('client-form-container');
            }
        }

        // Clear any previous error messages
        clearErrorMessages();

        // Populate the form fields
        document.getElementById('first_name').value = client.first_name || '';
        document.getElementById('last_name').value = client.last_name || '';

        // Make email field read-only when editing
        const emailField = document.getElementById('email');
        emailField.value = client.email || '';
        emailField.readOnly = true;
        emailField.classList.add('bg-gray-100');
        // Add note about email not being editable
        let emailNote = document.getElementById('email-note');
        if (!emailNote) {
            emailNote = document.createElement('p');
            emailNote.id = 'email-note';
            emailNote.classList.add('text-xs', 'text-gray-500', 'mt-1');
            emailNote.textContent = 'Email cannot be changed after registration';
            emailField.parentNode.appendChild(emailNote);
        } else {
            emailNote.classList.remove('hidden');
        }

        document.getElementById('phone').value = client.phone || '';

        // Select gender
        const genderSelect = document.getElementById('gender');
        if (genderSelect) {
            const genderOption = Array.from(genderSelect.options).find(option => option.value === client.gender);
            if (genderOption) {
                genderOption.selected = true;
            }
        }

        // Fill address fields
        document.getElementById('full_address').value = client.full_address || '';
        document.getElementById('area').value = client.area || '';
        document.getElementById('city').value = client.city || '';
        document.getElementById('pincode').value = client.pincode || '';
        document.getElementById('state').value = client.state || '';

        // Select status
        const statusSelect = document.getElementById('status');
        if (statusSelect) {
            const statusOption = Array.from(statusSelect.options).find(option => option.value === client.status);
            if (statusOption) {
                statusOption.selected = true;
            }
        }

        // Show the modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);

        // Set up the form submission to redirect back to client view page if we're on the view page
        const isClientViewPage = window.location.pathname.match(/\/admin\/clients\/\d+$/);
        if (isClientViewPage) {
            const clientForm = document.getElementById('client-form');
            if (clientForm) {
                const originalSubmitHandler = clientForm.onsubmit;
                clientForm.onsubmit = function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation(); // Prevent other handlers from firing

                    // Clear previous error messages
                    clearErrorMessages();

                    // Get the form data
                    const formData = new FormData(clientForm);

                    // Disable submit button and show loading
                    const submitBtn = document.querySelector('button[type="submit"][form="client-form"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

                    // Send AJAX request
                    fetch('/admin/clients/create', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 422) {
                                // Validation errors - will be handled in the next then block
                                return response.json();
                            } else if (response.status >= 500) {
                                throw new Error('Server error. Please try again later.');
                            } else {
                                throw new Error('An error occurred. Please try again.');
                            }
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Re-enable submit button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;

                        if (data.status === 'success') {
                            // Close modal ONLY on success
                            const modal = document.getElementById('client-modal');
                            if (modal) {
                                modal.classList.remove('active');
                                setTimeout(() => {
                                    modal.classList.add('hidden');
                                }, 300);
                            }

                            // Show success message
                            window.showToast(data.message, 'success');

                            // Check if we're on the clients list page
                            const isClientListPage = window.location.pathname === '/admin/clients' ||
                                                   window.location.pathname === '/admin/clients/';

                            if (isClientListPage) {
                                // Reload only the table instead of the entire page
                                reloadClientTable();
                            } else {
                                // If it's a new client, add to table
                                if (!formData.get('client_id')) {
                                    addClientToTable(data.client);
                                } else {
                                    // Update client in table
                                    updateClientInTable(data.client);
                                }
                            }

                            // Reset form
                            clientForm.reset();
                        } else if (data.status === 'error') {
                            // Display validation errors
                            if (data.errors) {
                                displayValidationErrors(data.errors);
                            }

                            // Display general error message if provided
                            if (data.message) {
                                displayFormErrors(data.message);
                            }

                            // Ensure modal stays open
                            const modal = document.getElementById('client-modal');
                            if (modal) {
                                modal.classList.remove('hidden');
                                modal.classList.add('active');
                            }

                            // Highlight the form to indicate errors
                            const formContainer = clientForm.closest('.p-6');
                            if (formContainer) {
                                formContainer.classList.add('has-validation-errors');
                                setTimeout(() => {
                                    formContainer.classList.remove('has-validation-errors');
                                }, 2000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;

                        // Display the error message
                        displayFormErrors(error.message || 'An error occurred while saving the client.');
                        window.showToast(error.message || 'An error occurred while saving the client.', 'error');
                    });
                };
            }
        }
    }

    // Function to open the add client modal
    function openAddClientModal() {
        const modal = document.getElementById('client-modal');
        if (!modal) return;

        // Set the modal title
        const modalTitle = document.getElementById('client-modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add New Client';
        }

        // Reset the form
        const clientForm = document.getElementById('client-form');
        if (clientForm) {
            clientForm.reset();
            document.getElementById('client_id').value = '';

            // Add class to form container for validation styling
            const formContainer = clientForm.closest('.p-6');
            if (formContainer) {
                formContainer.classList.add('client-form-container');
            }
        }

        // Clear any previous error messages
        clearErrorMessages();

        // Make sure email field is editable for new clients
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.readOnly = false;
            emailField.classList.remove('bg-gray-100');

            // Hide any email note
            const emailNote = document.getElementById('email-note');
            if (emailNote) {
                emailNote.classList.add('hidden');
            }
        }

        // Show the modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    }

    // Function to close the client modal
    function closeClientModal() {
        const modal = document.getElementById('client-modal');
        if (modal) {
            // Check if there are validation errors
            const hasErrors = document.querySelectorAll('.error-message, #form-errors:not(.hidden)').length > 0;

            // Only close if there are no errors
            if (!hasErrors) {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }
    }

    // Override any global event handlers for close buttons
    document.addEventListener('DOMContentLoaded', function() {
        const closeButtons = document.querySelectorAll('.close-client-modal');
        closeButtons.forEach(button => {
            // Remove existing event listeners by cloning
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            // Add our controlled event listener
            newButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Check for validation errors
                const hasErrors = document.querySelectorAll('.error-message, #form-errors:not(.hidden)').length > 0;

                // Only close if there are no errors
                if (!hasErrors) {
                    closeClientModal();
                }
            });
        });
    });

    // Function to open the view client modal
    function openViewClientModal(client, bookings, bookingsCount) {
        const modal = document.getElementById('client-view-modal');
        if (!modal) return;

        // Store client ID for edit function
        modal.setAttribute('data-client-id', client.id);

        // Set client avatar
        const avatarUrl = client.profile_picture ?
            client.profile_picture :
            `https://ui-avatars.com/api/?name=${encodeURIComponent(client.name)}&color=7F9CF5&background=EBF4FF`;
        document.getElementById('client-view-avatar').src = avatarUrl;

        // Set header details
        document.getElementById('client-view-name').textContent = client.name;
        document.getElementById('client-view-email').textContent = client.email;

        // Set status badge
        const statusBadge = document.getElementById('client-view-status-badge');
        statusBadge.textContent = client.status.charAt(0).toUpperCase() + client.status.slice(1);
        statusBadge.className = `px-3 py-1 text-xs rounded-full font-medium ${client.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`;

        // Set personal information
        document.getElementById('client-view-full-name').textContent = client.name;
        document.getElementById('client-view-email-full').textContent = client.email;
        document.getElementById('client-view-phone').textContent = client.phone || 'Not provided';
        document.getElementById('client-view-gender').textContent = client.gender ? (client.gender.charAt(0).toUpperCase() + client.gender.slice(1)) : 'Not specified';
        document.getElementById('client-view-registration').textContent = client.registration_date;

        // Set address information
        document.getElementById('client-view-address').textContent = client.full_address || 'Not provided';
        document.getElementById('client-view-area').textContent = client.area || 'Not provided';
        document.getElementById('client-view-city').textContent = client.city || 'Not provided';
        document.getElementById('client-view-pincode').textContent = client.pincode || 'Not provided';
        document.getElementById('client-view-state-country').textContent =
            (client.state ? client.state + ', ' : '') + (client.country || 'India');

        // Handle bookings display
        if (bookingsCount > 0) {
            document.getElementById('no-bookings-message').classList.add('hidden');
            document.getElementById('client-bookings-list').classList.remove('hidden');

            const bookingsTable = document.getElementById('client-bookings-table');
            bookingsTable.innerHTML = '';

            bookings.forEach(booking => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                row.innerHTML = `
                    <td class="px-3 py-2 whitespace-nowrap text-sm">#${booking.id}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">${booking.date}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">${booking.time}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">${booking.service}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">
                        <span class="px-2 py-1 text-xs rounded-full ${getStatusClass(booking.status)}">${booking.status}</span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-sm">₹${booking.amount}</td>
                `;

                bookingsTable.appendChild(row);
            });
        } else {
            document.getElementById('no-bookings-message').classList.remove('hidden');
            document.getElementById('client-bookings-list').classList.add('hidden');
        }

        // Show the modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
    }

    // Helper function to get status class for booking
    function getStatusClass(status) {
        switch(status.toLowerCase()) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'confirmed':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    // Function to close the view modal
    function closeViewModal() {
        const modal = document.getElementById('client-view-modal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }

    // Add CSS for row appear animation if not already in stylesheet
    if (!document.getElementById('client-animations')) {
        const style = document.createElement('style');
        style.id = 'client-animations';
        style.textContent = `
            .table-row-appear {
                opacity: 0;
                transform: translateY(-10px);
                animation: rowAppear 0.3s forwards;
            }

            @keyframes rowAppear {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Delete confirmation animation */
            #delete-modal-content {
                transition: opacity 0.3s ease, transform 0.3s ease;
            }

            /* Validation error animations */
            .has-validation-errors {
                animation: shakeForm 0.4s ease-in-out;
            }

            @keyframes shakeForm {
                0%, 100% { transform: translateX(0); }
                20%, 60% { transform: translateX(-5px); }
                40%, 80% { transform: translateX(5px); }
            }

            .error-message {
                animation: fadeIn 0.3s ease-in-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-5px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Enhanced validation styles */
            .border-red-500 {
                box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.5);
            }

            #form-errors {
                animation: pulse 1.5s ease-in-out;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.85; }
            }
        `;
        document.head.appendChild(style);
    }

    // Function to reload only the client table by re-fetching and re-rendering data
    function reloadClientTable() {
        const tbody = document.querySelector('table tbody');
        if (!tbody) return;

        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-10 text-center">
                    <div class="flex justify-center items-center">
                        <i class="fas fa-spinner fa-spin text-indigo-500 text-3xl"></i>
                    </div>
                </td>
            </tr>
        `;

        // Also show loading for pagination
        const paginationContainer = document.querySelector('.flex.items-center.justify-between.mt-6');
        if (paginationContainer) {
            paginationContainer.innerHTML = `
                <div class="w-full text-center py-2">
                    <i class="fas fa-spinner fa-spin text-indigo-500"></i>
                </div>
            `;
        }

        // Fetch client data
        fetch('/admin/clients?ajax=1', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.clients && data.clients.length > 0) {
                // Clear the table
                tbody.innerHTML = '';

                // Add each client to the table
                data.clients.forEach(client => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50 transition-colors';
                    tr.dataset.clientId = client.id;

                    // Create avatar URL
                    let avatarUrl;
                    if (client.profile_picture) {
                        avatarUrl = client.profile_picture;
                    } else {
                        // Use UI Avatars as fallback
                        avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(client.name)}&color=7F9CF5&background=EBF4FF`;
                    }

                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="h-10 w-10 rounded-full mr-3" src="${avatarUrl}" alt="${client.name}">
                                <div>
                                    <div class="font-medium text-gray-900">${client.name}</div>
                                    <div class="text-sm text-gray-500">${client.gender ? client.gender.charAt(0).toUpperCase() + client.gender.slice(1) : 'Individual'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">${client.email}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${client.phone}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${client.created_at}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${client.bookings_count || 0} bookings</div>
                            <div class="text-xs text-gray-500">${client.last_booking_date || 'No bookings'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full ${client.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'} font-medium">
                                ${client.status.charAt(0).toUpperCase() + client.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <a href="/admin/clients/${client.id}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View Client">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="text-blue-600 hover:text-blue-900 transition-colors edit-client-btn" title="Edit Client" data-client-id="${client.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900 transition-colors delete-client-btn" title="Delete Client" data-client-id="${client.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    `;

                    tbody.appendChild(tr);
                });

                // Re-attach event listeners for the new buttons
                attachTableEventListeners();

                // Update pagination section if available
                if (paginationContainer && data.pagination) {
                    // Fetch the updated pagination HTML
                    fetch('/admin/clients/pagination?page=' + data.pagination.current_page, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        paginationContainer.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error fetching pagination:', error);
                        // Fallback to basic pagination info
                        paginationContainer.innerHTML = `
                            <div class="flex items-center justify-between w-full">
                                <div class="text-sm text-gray-600">
                                    Showing ${data.pagination.from || 0} to ${data.pagination.to || 0} of ${data.pagination.total || 0} entries
                                </div>
                                <div>
                                    <!-- Simple pagination placeholder -->
                                    <span class="text-gray-500">Page ${data.pagination.current_page} of ${data.pagination.last_page}</span>
                                </div>
                            </div>
                        `;
                    });
                }
            } else {
                // No clients found
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No clients found. Add your first client by clicking the "Add Client" button.
                        </td>
                    </tr>
                `;

                // Reset pagination if available
                if (paginationContainer) {
                    paginationContainer.innerHTML = `
                        <div class="flex items-center justify-between w-full">
                            <div class="text-sm text-gray-600">
                                Showing 0 to 0 of 0 entries
                            </div>
                            <div></div>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error reloading table:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-red-500">
                        Failed to load clients. Please refresh the page.
                    </td>
                </tr>
            `;

            // Reset pagination on error
            if (paginationContainer) {
                paginationContainer.innerHTML = '';
            }
        });
    }

    // Function to re-attach event listeners after table reload
    function attachTableEventListeners() {
        // Re-attach delete button event listeners
        document.querySelectorAll('.delete-client-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteClientId = this.getAttribute('data-client-id');
                deletePopup.style.display = 'block';
            });
        });
    }

    // Prevent modal from closing when clicking outside if there are validation errors
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('client-modal');
        if (modal && e.target === modal) {
            // Check for validation errors
            const hasErrors = document.querySelectorAll('.error-message, #form-errors:not(.hidden)').length > 0;

            // Only allow closing if there are no errors
            if (hasErrors) {
                e.stopPropagation();
            }
        }
    }, true); // Use capture phase to intercept events before they reach other handlers
});
