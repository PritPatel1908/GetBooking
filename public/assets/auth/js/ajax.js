document.addEventListener('DOMContentLoaded', function() {
    // AJAX form submission for login
    $(loginForm).on('submit', function(e) {
        e.preventDefault(); // Prevent normal form submission

        const loginBtn = $('#loginBtn');
        const formData = $(this).serialize(); // Serialize form data

        // Show spinner and disable button
        loginBtn.html('<i class="fas fa-spinner fa-spin"></i> Signing in...');
        loginBtn.prop('disabled', true);
        // AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function(response) {
                // If successful, redirect to the intended page
                if (response.success) {
                // Store message in session storage instead of URL
                sessionStorage.setItem('success_message', 'Login successful!');
                    window.location.href = response.redirect;
                } else {
                    // Show error message if there's an error in response
                    showError(response.message || 'An error occurred during login.');

                    // Reset button
                    loginBtn.html('Sign In <i class="fas fa-arrow-right ms-2"></i>');
                    loginBtn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                // Handle validation errors or other HTTP errors
                let errorMsg = 'An error occurred during login.';

                if (xhr.status === 422) { // Validation error
                    const response = xhr.responseJSON;
                    if (response.errors) {
                        // Get the first validation error
                        const firstError = Object.values(response.errors)[0];
                        errorMsg = firstError[0] || errorMsg;
                    } else if (response.message) {
                        errorMsg = response.message;
                    }
                } else if (xhr.status === 401) { // Unauthorized
                    errorMsg = 'Invalid email or password.';
                } else if (xhr.status === 404) { // Not Found
                    errorMsg = 'User not found.';
                }

                showError(errorMsg);

                // Reset button
                loginBtn.html('Sign In <i class="fas fa-arrow-right ms-2"></i>');
                loginBtn.prop('disabled', false);
            }
        });
    });

    $(registerForm).on('submit', function(e) {
        e.preventDefault(); // Prevent normal form submission

        const registerBtn = $('#registerBtn');
        const formData = $(this).serialize(); // Serialize form data

        // Show spinner and disable button
        registerBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        registerBtn.prop('disabled', true);

        // AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function(response) {
                // If successful, redirect to the intended page
                if (response.success) {
                // Store message in session storage instead of URL
                    sessionStorage.setItem('success_message', 'Registration successful!');
                    window.location.href = response.redirect;
                } else {
                    // Show error message if there's an error in response
                    showError(response.message || 'An error occurred during login.');

                    // Reset button
                    registerBtn.html('Create Account <i class="fas fa-user-plus ms-2"></i>');
                    registerBtn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                console.error(xhr);
                // Handle validation errors or other HTTP errors
                let errorMsg = 'An error occurred during login.';

                if (xhr.status === 422) { // Validation error
                    const response = xhr.responseJSON;
                    if (response.errors) {
                        // Get the first validation error
                        const firstError = Object.values(response.errors)[0];
                        errorMsg = firstError[0] || errorMsg;
                    } else if (response.message) {
                        errorMsg = response.message;
                    }
                } else if (xhr.status === 401) { // Unauthorized
                    errorMsg = 'Invalid email or password.';
                } else if (xhr.status === 409) { // Unauthorized
                    errorMsg = 'Already have a account of this email.';
                }

                showError(errorMsg);

                // Reset button
                registerBtn.html('Create Account <i class="fas fa-user-plus ms-2"></i>');
                registerBtn.prop('disabled', false);
            }
        });
    });

    // Function to show error popup
    function showError(message) {
        // Remove any existing error popups
        $('.error-popup').remove();

        // Create and append error popup
        const errorPopup = $(`
            <div class="error-popup">
                <div class="error-popup-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${message}</span>
                </div>
            </div>
        `);

        $('body').append(errorPopup);

        // Show the popup with animation
        setTimeout(() => {
            errorPopup.addClass('show');
        }, 10);

        // Hide and remove after 3 seconds
        setTimeout(() => {
            errorPopup.removeClass('show');
            setTimeout(() => {
                errorPopup.remove();
            }, 300); // Wait for fade out animation to complete
        }, 3000);
    }
});
