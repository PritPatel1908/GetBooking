document.addEventListener('DOMContentLoaded', function() {
    // Define form elements
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    // AJAX form submission for login
    $(loginForm).on('submit', function(e) {
        e.preventDefault(); // Prevent normal form submission

        const loginBtn = $('#loginBtn');
        const formData = $(this).serialize(); // Serialize form data

        // Get CSRF token from form input or meta tag
        const csrfToken = $('input[name="_token"]').val() || 
                          $('meta[name="csrf-token"]').attr('content') || 
                          '';
        
        if (!csrfToken) {
            showError('CSRF token not found. Please refresh the page and try again.');
            loginBtn.html('Sign In <i class="fas fa-arrow-right ms-2"></i>');
            loginBtn.prop('disabled', false);
            return;
        }

        // Show spinner and disable button
        loginBtn.html('<i class="fas fa-spinner fa-spin"></i> Signing in...');
        loginBtn.prop('disabled', true);
        
        // AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // If successful, redirect to the intended page
                if (response.success) {
                    // Store message in session storage
                    sessionStorage.setItem('success_message', response.message || 'Login successful!');
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

                // Log full error for debugging
                console.error('Full XHR error object:', xhr);
                console.error('Status:', xhr.status);
                console.error('Status Text:', xhr.statusText);
                console.error('Response Text:', xhr.responseText);
                console.error('Response JSON:', xhr.responseJSON);

                // Check if response is HTML (error page) instead of JSON
                let response = null;
                let isHtmlResponse = false;
                
                try {
                    // Check if response is HTML
                    if (xhr.responseText && xhr.responseText.trim().startsWith('<!DOCTYPE')) {
                        isHtmlResponse = true;
                        console.error('Received HTML response instead of JSON:', xhr.responseText.substring(0, 500));
                        
                        // Try to extract error message from HTML
                        const htmlText = xhr.responseText;
                        const titleMatch = htmlText.match(/<title>([^<]+)<\/title>/i);
                        const errorMatch = htmlText.match(/<h1[^>]*>([^<]+)<\/h1>/i);
                        const pMatch = htmlText.match(/<p[^>]*>([^<]+)<\/p>/i);
                        
                        if (titleMatch) {
                            errorMsg = titleMatch[1];
                        } else if (errorMatch) {
                            errorMsg = errorMatch[1];
                        } else if (pMatch) {
                            errorMsg = pMatch[1];
                        }
                    } else {
                        // Try to get responseJSON first (jQuery sets this automatically)
                        if (xhr.responseJSON) {
                            response = xhr.responseJSON;
                        } else if (xhr.responseText) {
                            // Try to parse responseText
                            const text = xhr.responseText.trim();
                            if (text.startsWith('{') || text.startsWith('[')) {
                                response = JSON.parse(text);
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    console.error('Response text (first 500 chars):', xhr.responseText ? xhr.responseText.substring(0, 500) : 'No response text');
                    
                    // If it's HTML, set flag
                    if (xhr.responseText && xhr.responseText.trim().startsWith('<!DOCTYPE')) {
                        isHtmlResponse = true;
                    }
                }

                console.error('Parsed response:', response);
                console.error('Is HTML response:', isHtmlResponse);

                // Handle different status codes
                if (isHtmlResponse) {
                    // If we got HTML response, it means server returned an error page
                    if (xhr.status === 419) {
                        errorMsg = errorMsg || 'CSRF token mismatch. Please refresh the page and try again.';
                    } else if (xhr.status === 500) {
                        errorMsg = errorMsg || 'Server error occurred. Please try again later.';
                    } else if (xhr.status === 404) {
                        errorMsg = errorMsg || 'Page not found.';
                    } else {
                        errorMsg = errorMsg || 'An error occurred. The server returned an error page instead of JSON.';
                    }
                } else if (xhr.status === 0) {
                    errorMsg = 'Network error. Please check your internet connection.';
                } else if (xhr.status === 419) { // CSRF Token Mismatch
                    errorMsg = response && response.message ? response.message : 
                              'CSRF token mismatch. Please refresh the page and try again.';
                } else if (xhr.status === 422) { // Validation error
                    if (response && response.errors) {
                        // Get the first validation error
                        const firstError = Object.values(response.errors)[0];
                        errorMsg = Array.isArray(firstError) ? firstError[0] : firstError;
                    } else if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'Please check your input fields.';
                    }
                } else if (xhr.status === 401) { // Unauthorized
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'Invalid email or password.';
                    }
                } else if (xhr.status === 404) { // Not Found
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'User not found.';
                    }
                } else if (xhr.status === 500) { // Server Error
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'Server error. Please try again later.';
                    }
                } else if (response && response.message) {
                    errorMsg = response.message;
                } else if (xhr.responseText) {
                    // Try to extract error from HTML response (if any)
                    const htmlMatch = xhr.responseText.match(/<title>([^<]+)<\/title>/i);
                    if (htmlMatch) {
                        errorMsg = htmlMatch[1];
                    }
                }

                // Final fallback - always show something meaningful
                if (errorMsg === 'An error occurred during login.') {
                    if (xhr.status === 0) {
                        errorMsg = 'Network error. Please check your internet connection.';
                    } else if (xhr.statusText) {
                        errorMsg = 'Error: ' + xhr.statusText + ' (Status: ' + xhr.status + ')';
                    } else if (xhr.status) {
                        errorMsg = 'Error occurred (Status: ' + xhr.status + ')';
                    } else {
                        errorMsg = 'Unable to connect to server. Please try again.';
                    }
                }

                console.error('Final error message:', errorMsg);
                console.error('Showing error popup with message:', errorMsg);
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

        // Get CSRF token from form input or meta tag
        const csrfToken = $('input[name="_token"]').val() || 
                          $('meta[name="csrf-token"]').attr('content') || 
                          '';
        
        if (!csrfToken) {
            showError('CSRF token not found. Please refresh the page and try again.');
            registerBtn.html('Create Account <i class="fas fa-user-plus ms-2"></i>');
            registerBtn.prop('disabled', false);
            return;
        }

        // Show spinner and disable button
        registerBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating...');
        registerBtn.prop('disabled', true);

        // AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
                console.error('Registration error:', xhr);
                // Handle validation errors or other HTTP errors
                let errorMsg = 'An error occurred during registration.';

                // Check if response is JSON
                let response = null;
                try {
                    response = xhr.responseJSON || JSON.parse(xhr.responseText);
                } catch (e) {
                    // If response is not JSON, try to extract error from responseText
                    if (xhr.responseText) {
                        console.error('Non-JSON response:', xhr.responseText);
                    }
                }

                if (xhr.status === 422) { // Validation error
                    if (response && response.errors) {
                        // Get the first validation error
                        const firstError = Object.values(response.errors)[0];
                        errorMsg = firstError[0] || errorMsg;
                    } else if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'Please check your input fields.';
                    }
                } else if (xhr.status === 401) { // Unauthorized
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'Invalid credentials.';
                    }
                } else if (xhr.status === 409) { // Conflict - User exists
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'An account with this email already exists.';
                    }
                } else if (xhr.status === 500) { // Server Error
                    if (response && response.message) {
                        errorMsg = response.message;
                    } else {
                        errorMsg = 'Server error. Please try again later.';
                    }
                } else if (response && response.message) {
                    errorMsg = response.message;
                }

                console.error('Registration error:', xhr.status, errorMsg);
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
