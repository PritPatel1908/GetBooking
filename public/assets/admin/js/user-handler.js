document.addEventListener('DOMContentLoaded', function() {
    // Check for message in localStorage (for showing toast after redirect)
    if (localStorage.getItem('userActionMessage')) {
        window.showToast(
            localStorage.getItem('userActionMessage'),
            localStorage.getItem('userActionType') || 'success'
        );

        // Clear the message from localStorage
        localStorage.removeItem('userActionMessage');
        localStorage.removeItem('userActionType');
    }

    // User form submission handling
    const userForm = document.getElementById('user-form');

    if (userForm) {
        // Remove any existing listeners to prevent duplication
        const newUserForm = userForm.cloneNode(true);
        userForm.parentNode.replaceChild(newUserForm, userForm);

        // Set the new reference
        const refreshedUserForm = document.getElementById('user-form');

        refreshedUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // Prevent other handlers from firing

            // Clear previous error messages
            clearErrorMessages();

            // Get the form data
            const formData = new FormData(refreshedUserForm);
            const userId = formData.get('user_id');

            // Set the request method and URL based on whether we're creating or updating
            const method = userId ? 'PUT' : 'POST';
            const url = userId ? `/admin/users/${userId}` : '/admin/users';

            // Disable submit button and show loading
            const submitBtn = document.querySelector('button[type="submit"][form="user-form"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

            // Send AJAX request
            fetch(url, {
                method: method,
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
                    const modal = document.getElementById('user-modal');
                    if (modal) {
                        modal.classList.remove('active');
                        setTimeout(() => {
                            modal.classList.add('hidden');
                        }, 300);
                    }

                    // Show success message
                    window.showToast(data.message, 'success');

                    // Check if we're on the users list page
                    const isUserListPage = window.location.pathname === '/admin/users' ||
                                         window.location.pathname === '/admin/users/';

                    if (isUserListPage) {
                        // Reload only the table instead of the entire page
                        reloadUserTable();
                    } else {
                        // If we're on the user detail page, reload the page
                        window.location.reload();
                    }

                    // Reset form
                    refreshedUserForm.reset();
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
                    const modal = document.getElementById('user-modal');
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.classList.add('active');
                    }

                    // Highlight the form to indicate errors
                    const formContainer = refreshedUserForm.closest('.p-6');
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
                displayFormErrors(error.message || 'An error occurred while saving the user.');
                window.showToast(error.message || 'An error occurred while saving the user.', 'error');
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
            const form = document.getElementById('user-form');
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

    // Function to add a new user to the table
    function addUserToTable(user) {
        const tbody = document.querySelector('table tbody');

        if (tbody) {
            // Check if we need to remove the "no users" message
            const emptyRow = tbody.querySelector('tr td[colspan="6"]');
            if (emptyRow) {
                emptyRow.parentNode.remove();
            }

            const newRow = document.createElement('tr');
            newRow.classList.add('hover:bg-gray-50', 'transition-colors', 'table-row-appear');
            newRow.dataset.userId = user.id;

            // Create avatar URL
            let avatarUrl;
            if (user.profile_photo_path) {
                avatarUrl = user.profile_photo_path;
            } else {
                // Use UI Avatars as fallback
                avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&color=7F9CF5&background=EBF4FF`;
            }

            newRow.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img class="h-10 w-10 rounded-full mr-3" src="${avatarUrl}" alt="${user.name}">
                        <div>
                            <div class="font-medium text-gray-900">${user.name}</div>
                            <div class="text-sm text-gray-500">${user.user_type ? user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1) : 'User'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">${user.email}</td>
                <td class="px-6 py-4 whitespace-nowrap">${user.phone || 'Not provided'}</td>
                <td class="px-6 py-4 whitespace-nowrap">${user.created_at || new Date().toLocaleDateString()}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full ${user.user_type === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'} font-medium">
                        ${user.user_type ? user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1) : 'User'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex space-x-2">
                        <a href="/admin/users/${user.id}" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="View User">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="text-blue-600 hover:text-blue-900 transition-colors edit-user-btn" title="Edit User" data-user-id="${user.id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900 transition-colors delete-user-btn" title="Delete User" data-user-id="${user.id}">
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

    // Function to update existing user in the table
    function updateUserInTable(user) {
        const row = document.querySelector(`tr[data-user-id="${user.id}"]`);

        if (row) {
            // Update the user data in the row
            const nameCell = row.querySelector('.font-medium.text-gray-900');
            if (nameCell) nameCell.textContent = user.name;

            const userTypeCell = row.querySelector('.text-sm.text-gray-500');
            if (userTypeCell) userTypeCell.textContent = user.user_type ? user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1) : 'User';

            const emailCell = row.querySelector('td:nth-child(2)');
            if (emailCell) emailCell.textContent = user.email;

            const phoneCell = row.querySelector('td:nth-child(3)');
            if (phoneCell) phoneCell.textContent = user.phone || 'Not provided';

            const statusBadge = row.querySelector('td:nth-child(5) span');
            if (statusBadge) {
                // Update user type badge
                statusBadge.className = `px-2 py-1 text-xs rounded-full ${user.user_type === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'} font-medium`;
                statusBadge.textContent = user.user_type ? user.user_type.charAt(0).toUpperCase() + user.user_type.slice(1) : 'User';
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
    var deleteUserId = null;

    // Add click handlers to all delete buttons
    var deleteButtons = document.querySelectorAll('.delete-user-btn');
    for (var i = 0; i < deleteButtons.length; i++) {
        deleteButtons[i].addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            deleteUserId = this.getAttribute('data-user-id');
            deletePopup.style.display = 'block';
        });
    }

    // Cancel button closes the popup
    if (cancelDelete) {
        cancelDelete.addEventListener('click', function() {
            deletePopup.style.display = 'none';
            deleteUserId = null;
        });
    }

    // Confirm delete
    if (confirmDelete) {
        confirmDelete.addEventListener('click', function() {
            if (!deleteUserId) return;

            // Change button text to loading
            this.innerHTML = 'Deleting...';
            this.disabled = true;

            // Delete via AJAX using fetch
            fetch(`/admin/users/${deleteUserId}`, {
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
                    // Hide popup
                    deletePopup.style.display = 'none';

                    // Check current URL pattern
                    const currentPath = window.location.pathname;
                    const isUserDetailPage = /^\/admin\/users\/\d+$/.test(currentPath);

                    if (isUserDetailPage) {
                        // If on user detail page, redirect to users list
                        localStorage.setItem('userActionMessage', data.message || 'User deleted successfully');
                        localStorage.setItem('userActionType', 'success');
                        window.location.href = '/admin/users';
                    } else {
                        // If on users list page, reload only the table
                        window.showToast(data.message || 'User deleted successfully', 'success');
                        reloadUserTable();
                    }
                } else {
                    // Show error
                    window.showToast(data.message || 'Error deleting user', 'error');
                }

                // Reset button
                confirmDelete.innerHTML = 'Delete User';
                confirmDelete.disabled = false;
                deleteUserId = null;
            })
            .catch(error => {
                console.error('Error:', error);
                window.showToast('An error occurred while deleting the user', 'error');

                // Reset button
                confirmDelete.innerHTML = 'Delete User';
                confirmDelete.disabled = false;
                deletePopup.style.display = 'none';
                deleteUserId = null;
            });
        });
    }

    // Close popup when clicking outside
    if (deletePopup) {
        deletePopup.addEventListener('click', function(e) {
            if (e.target === deletePopup) {
                deletePopup.style.display = 'none';
                deleteUserId = null;
            }
        });
    }

    // Set up event delegation for edit user buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-user-btn')) {
            const btn = e.target.closest('.edit-user-btn');
            const userId = btn.getAttribute('data-user-id');

            if (userId) {
                // Show loading state
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                // Fetch user data
                fetch(`/admin/users/${userId}/edit`, {
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
                        openEditUserModal(data.user);
                    } else {
                        window.showToast(data.message || 'Failed to load user data', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = '<i class="fas fa-edit"></i>';
                    btn.disabled = false;
                    window.showToast('An error occurred while loading user data', 'error');
                });
            }
        }

        // Handle add user button
        if (e.target.closest('.add-user-btn')) {
            openAddUserModal();
        }

        // Handle close modal buttons
        if (e.target.closest('.close-user-modal')) {
            closeUserModal();
        }
    });

    // Function to open the edit user modal
    function openEditUserModal(user) {
        const modal = document.getElementById('user-modal');
        if (!modal) return;

        // Set the modal title
        const modalTitle = document.getElementById('user-modal-title');
        if (modalTitle) {
            modalTitle.querySelector('span').textContent = 'Edit User';
        }

        // Set the user ID in the form
        document.getElementById('user_id').value = user.id;

        // Add class to form container for validation styling
        const userForm = document.getElementById('user-form');
        if (userForm) {
            // Add user-form-container class to the parent element for validation styling
            const formContainer = userForm.closest('.p-6');
            if (formContainer) {
                formContainer.classList.add('user-form-container');
            }
        }

        // Clear any previous error messages
        clearErrorMessages();

        // Populate the form fields
        document.getElementById('first_name').value = user.first_name || '';
        document.getElementById('last_name').value = user.last_name || '';

        // Set email (may be read-only when editing)
        const emailField = document.getElementById('email');
        emailField.value = user.email || '';

        // Set password help text for editing
        const passwordHelpText = document.getElementById('password-help-text');
        if (passwordHelpText) {
            passwordHelpText.textContent = 'Leave blank to keep the current password.';
        }

        document.getElementById('phone').value = user.phone || '';

        // Select user type
        const userTypeSelect = document.getElementById('user_type');
        if (userTypeSelect) {
            const userTypeOption = Array.from(userTypeSelect.options).find(option => option.value === user.user_type);
            if (userTypeOption) {
                userTypeOption.selected = true;
            }
        }

        // Fill address fields
        document.getElementById('address').value = user.address || '';
        document.getElementById('city').value = user.city || '';
        document.getElementById('state').value = user.state || '';
        document.getElementById('postal_code').value = user.postal_code || '';
        document.getElementById('country').value = user.country || '';

        // Show the modal - Fixed to properly display modal
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    // Function to open the add user modal
    function openAddUserModal() {
        const modal = document.getElementById('user-modal');
        if (!modal) return;

        // Set the modal title
        const modalTitle = document.getElementById('user-modal-title');
        if (modalTitle) {
            modalTitle.querySelector('span').textContent = 'Add New User';
        }

        // Reset the form
        const userForm = document.getElementById('user-form');
        if (userForm) {
            userForm.reset();
            document.getElementById('user_id').value = '';

            // Add class to form container for validation styling
            const formContainer = userForm.closest('.p-6');
            if (formContainer) {
                formContainer.classList.add('user-form-container');
            }
        }

        // Clear any previous error messages
        clearErrorMessages();

        // Update password help text for new users
        const passwordHelpText = document.getElementById('password-help-text');
        if (passwordHelpText) {
            passwordHelpText.textContent = 'Password must be at least 8 characters.';
        }

        // Show the modal - Fixed to properly display modal
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    // Function to close the user modal
    function closeUserModal() {
        const modal = document.getElementById('user-modal');
        if (modal) {
            // Check if there are validation errors
            const hasErrors = document.querySelectorAll('.error-message, #form-errors:not(.hidden)').length > 0;

            // Only close if there are no errors
            if (!hasErrors) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        }
    }

    // Function to reload only the user table by re-fetching and re-rendering data
    function reloadUserTable() {
        const tbody = document.querySelector('table tbody');
        if (!tbody) return;

        // Show loading state
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-10 text-center">
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

        // Fetch user data
        fetch('/admin/users/pagination', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Update the table with the new HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            // Update the table content
            const newTbody = tempDiv.querySelector('tbody');
            if (newTbody) {
                tbody.innerHTML = newTbody.innerHTML;
            }

            // Update pagination if available
            if (paginationContainer) {
                const newPagination = tempDiv.querySelector('.flex.items-center.justify-between.mt-6');
                if (newPagination) {
                    paginationContainer.innerHTML = newPagination.innerHTML;
                }
            }

            // Re-attach event listeners
            attachTableEventListeners();
        })
        .catch(error => {
            console.error('Error reloading table:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-500">
                        Failed to load users. Please refresh the page.
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
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                deleteUserId = this.getAttribute('data-user-id');
                deletePopup.style.display = 'block';
            });
        });
    }

    // Add CSS for row appear animation if not already in stylesheet
    if (!document.getElementById('user-animations')) {
        const style = document.createElement('style');
        style.id = 'user-animations';
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
});
