// Theme Toggle
const themeToggle = document.getElementById('theme-toggle');
const htmlElement = document.documentElement;

// Check for saved theme preference or use user's system preference
if (localStorage.getItem('theme') === 'dark' ||
    (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    htmlElement.classList.add('dark');
    htmlElement.classList.remove('light');
} else {
    htmlElement.classList.add('light');
    htmlElement.classList.remove('dark');
}

themeToggle.addEventListener('click', () => {
    if (htmlElement.classList.contains('dark')) {
        htmlElement.classList.remove('dark');
        htmlElement.classList.add('light');
        localStorage.setItem('theme', 'light');
    } else {
        htmlElement.classList.add('dark');
        htmlElement.classList.remove('light');
        localStorage.setItem('theme', 'dark');
    }
});

// Mobile Menu Toggle
const mobileMenu = document.querySelector('.mobile-menu');
const navLinks = document.querySelector('.nav-links');

mobileMenu.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    mobileMenu.innerHTML = navLinks.classList.contains('active')
        ? '<i class="fas fa-times"></i>'
        : '<i class="fas fa-bars"></i>';
});

// Card Animation on Scroll
const cards = document.querySelectorAll('.card');
const counterItems = document.querySelectorAll('.counter-item');

function animateOnScroll() {
    cards.forEach(card => {
        const cardPosition = card.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;

        if (cardPosition < screenPosition) {
            card.classList.add('animate');
        }
    });

    counterItems.forEach(item => {
        const itemPosition = item.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;

        if (itemPosition < screenPosition) {
            item.classList.add('animate');
        }
    });
}

// Improved Animated Counter
const counters = document.querySelectorAll('.counter-number');
let hasRun = false;

function runCounter() {
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));

        // Reset counter to zero
        counter.innerText = '0';

        // Calculate animation duration based on target value
        const duration = 2000; // 2 seconds for all counters
        const frameDuration = 1000 / 60; // 60fps
        const totalFrames = Math.round(duration / frameDuration);

        let frame = 0;

        // Create animation using requestAnimationFrame for smoother counting
        const animate = () => {
            frame++;
            const progress = frame / totalFrames;
            const currentCount = Math.round(progress * target);

            // Format numbers with commas for better readability
            counter.innerText = currentCount.toLocaleString();

            if (frame < totalFrames) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    });
}

// Run counter animation when section is in view
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
        rect.bottom >= 0 &&
        rect.left >= 0 &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

const counterSection = document.getElementById('counter');

function checkCounterVisibility() {
    if (isInViewport(counterSection) && !hasRun) {
        // Add animation class to each counter item
        counterItems.forEach((item, index) => {
            // Stagger the animations
            setTimeout(() => {
                item.classList.add('animate');
            }, index * 150);
        });

        // Start the counter animation after items have animated in
        setTimeout(() => {
            runCounter();
            hasRun = true;
        }, counterItems.length * 150);
    }
}

// Reset counter animation when section scrolls out of view
function resetCounterAnimation() {
    const counterSectionBottom = counterSection.getBoundingClientRect().bottom;

    // If counter section has scrolled completely out of view (upward)
    if (counterSectionBottom < 0) {
        // Reset the animation flag
        hasRun = false;

        // Reset counter values
        counters.forEach(counter => {
            counter.innerText = '0';
        });

        // Remove animation classes
        counterItems.forEach(item => {
            item.classList.remove('animate');
        });
    }
}

// Testimonial Slider
const testimonialSlides = document.querySelectorAll('.testimonial-slide');
const testimonialDots = document.querySelectorAll('.testimonial-dot');
let currentSlide = 0;

function showSlide(index) {
    testimonialSlides.forEach(slide => slide.classList.remove('active'));
    testimonialDots.forEach(dot => dot.classList.remove('active'));

    testimonialSlides[index].classList.add('active');
    testimonialDots[index].classList.add('active');
    currentSlide = index;
}

testimonialDots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        showSlide(index);
    });
});

// Auto slide every 5 seconds
setInterval(() => {
    let nextSlide = (currentSlide + 1) % testimonialSlides.length;
    showSlide(nextSlide);
}, 5000);

// Go to top button
const goTopButton = document.getElementById('goTop');

function scrollFunction() {
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        goTopButton.classList.add('active');
    } else {
        goTopButton.classList.remove('active');
    }
}

goTopButton.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});

// Event Listeners
window.addEventListener('scroll', () => {
    scrollFunction();
    animateOnScroll();
    checkCounterVisibility();
    resetCounterAnimation();
});

// Initialize animations on page load
document.addEventListener('DOMContentLoaded', function() {
    animateOnScroll();
    checkCounterVisibility();

    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });

            // Close mobile menu if open
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                mobileMenu.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    });
});

// Function to fetch ground details and show in modal
function viewGroundDetails(id) {
    // Show loading spinner
    document.getElementById('ground-modal-content').innerHTML = '<div class="loading-spinner"></div>';

    // Display the modal
    document.getElementById('ground-modal').style.display = 'flex';

    // Fetch ground details from the server
    fetch(`/ground-details/${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const ground = data.ground;

                // Create modal content
                let modalContent = `
                    <div class="modal-header">
                        <h2>${ground.name}</h2>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div class="ground-image">
                            <img src="${data.imageUrl}" alt="${ground.name}">
                        </div>
                        <div class="ground-info">
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${ground.location}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>₹${ground.price_per_hour} per hour</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Capacity: ${ground.capacity} people</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-futbol"></i>
                                <span>Type: ${ground.ground_type}</span>
                            </div>
                        </div>

                        <div class="ground-description">
                            <h3>Description</h3>
                            <p>${ground.description}</p>
                        </div>

                        <div class="ground-features">
                            <h3>Amenities</h3>
                            <p>${ground.amenities || 'No amenities listed'}</p>
                        </div>

                        <div class="ground-rules">
                            <h3>Rules</h3>
                            <p>${ground.rules || 'No specific rules listed'}</p>
                        </div>

                        <div class="ground-hours">
                            <h3>Operating Hours</h3>
                            <p>${ground.opening_time} - ${ground.closing_time}</p>
                        </div>

                        <div class="ground-contact">
                            <h3>Contact Information</h3>
                            <p><i class="fas fa-phone"></i> ${ground.phone}</p>
                            <p><i class="fas fa-envelope"></i> ${ground.email}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="/view-ground/${ground.id}" class="btn">Book Now</a>
                    </div>
                `;

                // Update modal content
                document.getElementById('ground-modal-content').innerHTML = modalContent;

                // Add event listener to close button
                document.querySelector('.close-modal').addEventListener('click', function() {
                    document.getElementById('ground-modal').style.display = 'none';
                });
            } else {
                document.getElementById('ground-modal-content').innerHTML = `
                    <div class="modal-header">
                        <h2>Error</h2>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p>Sorry, we couldn't find details for this sports ground.</p>
                    </div>
                `;

                // Add event listener to close button
                document.querySelector('.close-modal').addEventListener('click', function() {
                    document.getElementById('ground-modal').style.display = 'none';
                });
            }
        })
        .catch(error => {
            console.error('Error fetching ground details:', error);
            document.getElementById('ground-modal-content').innerHTML = `
                <div class="modal-header">
                    <h2>Error</h2>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <p>Sorry, something went wrong. Please try again later.</p>
                </div>
            `;

            // Add event listener to close button
            document.querySelector('.close-modal').addEventListener('click', function() {
                document.getElementById('ground-modal').style.display = 'none';
            });
        });
}

// Close modal when clicking outside the content
window.addEventListener('click', function(event) {
    const modal = document.getElementById('ground-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Add event listeners to view ground buttons
document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-ground-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const groundId = this.getAttribute('data-id');
            viewGroundDetails(groundId);
        });
    });
});
