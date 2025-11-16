// ============================================
// Sports Ground Booking - Main JavaScript
// ============================================

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
            navMenu.classList.remove('active');
        }
    });

    // Initialize date picker
    initDatePicker();
    
    // Initialize slot selection
    initSlotSelection();
    
    // Initialize payment method selection
    initPaymentMethod();
    
    // Initialize forms
    initForms();
    
    // Initialize hero carousel
    initHeroCarousel();
});

// Date Picker Functionality
function initDatePicker() {
    const dateOptions = document.querySelectorAll('.date-option');
    
    dateOptions.forEach(option => {
        option.addEventListener('click', function() {
            dateOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            
            // Update available slots based on selected date
            updateAvailableSlots(this.dataset.date);
        });
    });
}

// Slot Selection Functionality
function initSlotSelection() {
    const slotButtons = document.querySelectorAll('.slot-btn:not(.disabled)');
    
    slotButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('disabled')) {
                this.classList.toggle('selected');
            }
        });
    });
}

// Payment Method Selection
function initPaymentMethod() {
    const paymentMethods = document.querySelectorAll('.payment-method');
    
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            paymentMethods.forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
}

// Update Available Slots
function updateAvailableSlots(date) {
    // This would typically fetch data from an API
    // For now, we'll just simulate it
    const slotButtons = document.querySelectorAll('.slot-btn');
    
    slotButtons.forEach(button => {
        // Simulate random availability
        const isAvailable = Math.random() > 0.3;
        
        if (isAvailable) {
            button.classList.remove('disabled');
        } else {
            button.classList.add('disabled');
        }
    });
}

// Form Validation
function initForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = 'var(--danger-color)';
                } else {
                    input.style.borderColor = '';
                }
            });
            
            if (isValid) {
                // Show success message
                showNotification('Form submitted successfully!', 'success');
                
                // In a real application, you would submit the form data here
                // For now, we'll just log it
                const formData = new FormData(form);
                console.log('Form data:', Object.fromEntries(formData));
            } else {
                showNotification('Please fill in all required fields', 'error');
            }
        });
    });
}

// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? 'var(--secondary-color)' : 'var(--danger-color)'};
        color: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Search Functionality
function initSearch() {
    const searchInput = document.querySelector('#searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.ground-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
}

// Filter Functionality
function initFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterGrounds(filter);
        });
    });
}

function filterGrounds(filter) {
    const cards = document.querySelectorAll('.ground-card');
    
    cards.forEach(card => {
        if (filter === 'all') {
            card.style.display = '';
        } else {
            const cardType = card.dataset.type;
            if (cardType === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        }
    });
}

// Initialize search and filters when page loads
document.addEventListener('DOMContentLoaded', function() {
    initSearch();
    initFilters();
});

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Format Time
function formatTime(timeString) {
    return timeString; // You can enhance this to format time properly
}

// Calculate Total Price
function calculateTotal() {
    const selectedSlots = document.querySelectorAll('.slot-btn.selected');
    const basePrice = parseFloat(document.querySelector('.ground-price')?.textContent.replace(/[^0-9.]/g, '')) || 0;
    const totalPrice = selectedSlots.length * basePrice;
    
    const totalElement = document.querySelector('#totalPrice');
    if (totalElement) {
        totalElement.textContent = `₹${totalPrice.toFixed(2)}`;
    }
    
    return totalPrice;
}

// Update total when slots are selected
document.addEventListener('DOMContentLoaded', function() {
    const slotButtons = document.querySelectorAll('.slot-btn');
    
    slotButtons.forEach(button => {
        button.addEventListener('click', function() {
            setTimeout(calculateTotal, 100);
        });
    });
});

// Hero Carousel Functionality
function initHeroCarousel() {
    const carousel = document.querySelector('.hero-carousel');
    if (!carousel) return;
    
    const slides = carousel.querySelectorAll('.hero-slide');
    const indicators = carousel.querySelectorAll('.indicator');
    const prevBtn = carousel.querySelector('.carousel-prev');
    const nextBtn = carousel.querySelector('.carousel-next');
    
    if (slides.length === 0) {
        console.log('No slides found');
        return;
    }
    
    let currentSlide = 0;
    let autoSlideInterval = null;
    
    // Find initial active slide
    slides.forEach((slide, index) => {
        if (slide.classList.contains('active')) {
            currentSlide = index;
        }
    });
    
    // Function to show slide
    function showSlide(index) {
        if (index < 0 || index >= slides.length) return;
        
        // Remove active class from all slides and indicators
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Add active class to current slide and indicator
        slides[index].classList.add('active');
        if (indicators[index]) {
            indicators[index].classList.add('active');
        }
        
        currentSlide = index;
    }
    
    // Function to go to next slide
    function goToNextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }
    
    // Function to go to previous slide
    function goToPrevSlide() {
        const prev = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prev);
    }
    
    // Auto slide functionality
    function startAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
        }
        autoSlideInterval = setInterval(goToNextSlide, 5000); // Change slide every 5 seconds
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) {
            clearInterval(autoSlideInterval);
            autoSlideInterval = null;
        }
    }
    
    // Initialize - show first slide if none is active
    if (!slides[currentSlide].classList.contains('active')) {
        showSlide(0);
    }
    
    // Start auto slide
    startAutoSlide();
    
    // Pause on hover
    carousel.addEventListener('mouseenter', stopAutoSlide);
    carousel.addEventListener('mouseleave', startAutoSlide);
    
    // Next button click
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            stopAutoSlide();
            goToNextSlide();
            setTimeout(startAutoSlide, 5000); // Resume after 5 seconds
        });
    }
    
    // Previous button click
    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            stopAutoSlide();
            goToPrevSlide();
            setTimeout(startAutoSlide, 5000); // Resume after 5 seconds
        });
    }
    
    // Indicator click
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', (e) => {
            e.preventDefault();
            stopAutoSlide();
            showSlide(index);
            setTimeout(startAutoSlide, 5000); // Resume after 5 seconds
        });
    });
    
    // Keyboard navigation (only when carousel is in view)
    let isCarouselFocused = false;
    carousel.addEventListener('mouseenter', () => { isCarouselFocused = true; });
    carousel.addEventListener('mouseleave', () => { isCarouselFocused = false; });
    
    document.addEventListener('keydown', (e) => {
        if (!isCarouselFocused) return;
        
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            stopAutoSlide();
            goToPrevSlide();
            setTimeout(startAutoSlide, 5000);
        } else if (e.key === 'ArrowRight') {
            e.preventDefault();
            stopAutoSlide();
            goToNextSlide();
            setTimeout(startAutoSlide, 5000);
        }
    });
    
    console.log('Hero carousel initialized with', slides.length, 'slides');
}

