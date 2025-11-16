document.addEventListener('DOMContentLoaded', function() {
    // Check for success_message in URL
    const successMessage = sessionStorage.getItem('success_message');

    if (successMessage) {
        // Remove the message from sessionStorage after reading it
        sessionStorage.removeItem('success_message');

        // Display success message
        showSuccess(successMessage);
    }
});

// Success popup function
function showSuccess(message) {
    const successPopup = $(`
        <div class="success-popup">
            <div class="success-popup-content">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
        </div>
    `);

    $('body').append(successPopup);

    // Show the popup with animation
    setTimeout(() => {
        successPopup.addClass('show');
    }, 50);

    // Hide and remove after 3 seconds
    setTimeout(() => {
        successPopup.removeClass('show');
        setTimeout(() => {
            successPopup.remove();
        }, 500); // Wait for fade out animation to complete
    }, 5000);
}
