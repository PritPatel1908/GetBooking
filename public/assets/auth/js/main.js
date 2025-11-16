document.addEventListener('DOMContentLoaded', function() {
    const cardWrapper = document.getElementById('cardWrapper');
    const showSignupBtn = document.getElementById('showSignup');
    const showLoginBtn = document.getElementById('showLogin');
    const loginBtn = document.getElementById('loginBtn');
    const registerBtn = document.getElementById('registerBtn');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const cardFront = document.querySelector('.card-front');
    const cardBack = document.querySelector('.card-back');

    // Add password visibility toggles
    addPasswordToggles();

    // Check if we should force vertical layout for mobile
    checkForMobileLayout();

    // Toggle between login and signup
    showSignupBtn.addEventListener('click', function() {
        if (window.innerWidth <= 576) {
            // On mobile, don't use flip animation, just swap displays
            cardFront.style.display = 'none';
            cardBack.style.display = 'flex';
            // Scroll to top
            window.scrollTo(0, 0);
        } else {
            // On desktop, use the card flip animation
            cardWrapper.classList.add('card-flip');
        }
    });

    showLoginBtn.addEventListener('click', function() {
        if (window.innerWidth <= 576) {
            // On mobile, don't use flip animation, just swap displays
            cardFront.style.display = 'flex';
            cardBack.style.display = 'none';
            // Scroll to top
            window.scrollTo(0, 0);
        } else {
            // On desktop, use the card flip animation
            cardWrapper.classList.remove('card-flip');
        }
    });

    // Form submission animation
    // loginBtn.addEventListener('click', function(e) {
    //     if(loginForm.checkValidity()) {
    //         this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
    //         this.disabled = true;
    //     }
    // });

    // registerBtn.addEventListener('click', function(e) {
    //     if(registerForm.checkValidity()) {
    //         this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
    //         this.disabled = true;
    //     }
    // });

    // Handle window resizing
    window.addEventListener('resize', function() {
        checkForMobileLayout();
    });

    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(this.value)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                if (this.value.length > 0) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            }
        });
    });

    // Function to check if we should use mobile layout
    function checkForMobileLayout() {
        if (window.innerWidth <= 576) {
            document.body.classList.add('mobile-view');

            // Disable 3D transforms on mobile
            cardWrapper.style.transform = 'none';

            // Set display based on which card should be shown
            if (cardWrapper.classList.contains('card-flip')) {
                cardFront.style.display = 'none';
                cardBack.style.display = 'flex';
            } else {
                cardFront.style.display = 'flex';
                cardBack.style.display = 'none';
            }
        } else {
            document.body.classList.remove('mobile-view');

            // Re-enable 3D transforms on desktop
            cardWrapper.style.transform = '';

            // Reset display properties
            cardFront.style.display = '';
            cardBack.style.display = '';
        }
    }

    // Add password visibility toggles to password fields
    function addPasswordToggles() {
        const passwordFields = document.querySelectorAll('input[type="password"]');

        passwordFields.forEach(field => {
            // Create toggle button
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'password-toggle';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.setAttribute('aria-label', 'Toggle password visibility');

            // Style the button
            toggleBtn.style.position = 'absolute';
            toggleBtn.style.right = '15px';
            toggleBtn.style.top = '15px';
            toggleBtn.style.background = 'transparent';
            toggleBtn.style.border = 'none';
            toggleBtn.style.color = '#6e8efb';
            toggleBtn.style.cursor = 'pointer';

            // Insert the button after the input
            field.parentNode.style.position = 'relative';
            field.parentNode.appendChild(toggleBtn);

            // Add click event
            toggleBtn.addEventListener('click', function() {
                if (field.type === 'password') {
                    field.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    field.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });
    }
});
