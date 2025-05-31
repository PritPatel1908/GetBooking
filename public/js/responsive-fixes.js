/**
 * Responsive fixes for GetBooking
 * This file contains JavaScript fixes for responsive behavior
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fix for dropdown menus on touch devices
    if (window.innerWidth <= 767) {
        const dropdownButtons = document.querySelectorAll('.group > button, .dropdown-toggle');

        dropdownButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const parent = this.closest('.group');

                // Close all other open dropdowns
                document.querySelectorAll('.group.active').forEach(activeGroup => {
                    if (activeGroup !== parent) {
                        activeGroup.classList.remove('active');
                    }
                });

                // Toggle current dropdown
                parent.classList.toggle('active');
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.group')) {
                document.querySelectorAll('.group.active').forEach(group => {
                    group.classList.remove('active');
                });
            }
        });
    }

    // Fix for iOS 100vh issue
    function setVhProperty() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    setVhProperty();
    window.addEventListener('resize', debounce(setVhProperty, 100));

    // Fix for tables on mobile
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (!table.closest('.table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('table-responsive');
            wrapper.style.overflowX = 'auto';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });

    // Fix for modals on mobile
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('touchmove', function(e) {
            e.stopPropagation();
        }, { passive: true });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden') &&
                window.getComputedStyle(modal).display !== 'none') {
                closeModal(modal);
            }
        });

        // Close modal function
        function closeModal(modal) {
            if (modal.style.display === 'flex' || modal.style.display === 'block') {
                modal.style.display = 'none';
            } else if (!modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        }

        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });

        // Find and attach close handlers
        const closeButtons = modal.querySelectorAll('.close-modal, .modal-close, [data-dismiss="modal"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                closeModal(modal);
            });
        });
    });

    // Add touch support for hover effects
    const hoverElements = document.querySelectorAll('.hover-effect');
    hoverElements.forEach(element => {
        element.addEventListener('touchstart', function() {
            this.classList.add('hover');
        }, { passive: true });

        element.addEventListener('touchend', function() {
            this.classList.remove('hover');
        }, { passive: true });
    });

    // Improve form element accessibility
    const formElements = document.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
        if (!element.getAttribute('id') && element.getAttribute('name')) {
            element.setAttribute('id', element.getAttribute('name'));
        }

        const label = element.previousElementSibling;
        if (label && label.tagName === 'LABEL' && !label.getAttribute('for') && element.getAttribute('id')) {
            label.setAttribute('for', element.getAttribute('id'));
        }
    });

    // Lazy loading images
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;

                    img.addEventListener('load', () => {
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    });
                }
            });
        });

        lazyImages.forEach(img => {
            img.classList.add('lazy-image');
            imageObserver.observe(img);
        });
    }

    // Responsive navigation drawer
    const drawerToggles = document.querySelectorAll('[data-toggle="drawer"]');
    const drawers = document.querySelectorAll('.nav-drawer');

    drawerToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const drawer = document.querySelector(targetId);

            if (drawer) {
                drawer.classList.toggle('active');
                document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
            }
        });
    });

    drawers.forEach(drawer => {
        drawer.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        const closeButtons = drawer.querySelectorAll('[data-dismiss="drawer"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                drawer.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    });

    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                e.preventDefault();

                const headerOffset = 80; // Adjust based on your fixed header height
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });

                // Update URL hash without scrolling
                history.pushState(null, null, targetId);
            }
        });
    });

    // Handle browser back/forward navigation for hash links
    window.addEventListener('popstate', function() {
        if (location.hash) {
            const targetElement = document.querySelector(location.hash);

            if (targetElement) {
                const headerOffset = 80;
                const elementPosition = targetElement.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }
    });

    // Responsive tabs
    const tabLinks = document.querySelectorAll('[data-toggle="tab"]');

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href') || this.getAttribute('data-target');
            const tabContent = document.querySelector(targetId);

            if (tabContent) {
                // Deactivate all tabs
                document.querySelectorAll('.tab-pane').forEach(tab => {
                    tab.classList.remove('active');
                });

                document.querySelectorAll('[data-toggle="tab"]').forEach(tab => {
                    tab.classList.remove('active');
                });

                // Activate current tab
                tabContent.classList.add('active');
                this.classList.add('active');
            }
        });
    });

    // Responsive accordion
    const accordionToggles = document.querySelectorAll('[data-toggle="collapse"]');

    accordionToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href') || this.getAttribute('data-target');
            const target = document.querySelector(targetId);

            if (target) {
                const isMultiple = this.closest('.accordion').getAttribute('data-multiple') === 'true';

                if (!isMultiple) {
                    // Close other items
                    const currentAccordion = this.closest('.accordion');
                    currentAccordion.querySelectorAll('.collapse.show').forEach(item => {
                        if (item !== target) {
                            item.classList.remove('show');

                            // Update toggle state
                            const itemToggle = currentAccordion.querySelector(`[data-target="#${item.id}"], [href="#${item.id}"]`);
                            if (itemToggle) {
                                itemToggle.classList.add('collapsed');
                                itemToggle.setAttribute('aria-expanded', 'false');
                            }
                        }
                    });
                }

                // Toggle current item
                target.classList.toggle('show');
                this.classList.toggle('collapsed', !target.classList.contains('show'));
                this.setAttribute('aria-expanded', target.classList.contains('show'));
            }
        });
    });
});

// Debounce function to improve performance
function debounce(func, wait = 20, immediate = true) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Throttle function to limit execution rate
function throttle(func, limit = 100) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Fix for position:sticky support
function stickyPolyfill() {
    const stickyElements = document.querySelectorAll('.sticky-top');

    if (CSS && CSS.supports && CSS.supports('position', 'sticky')) {
        return; // Native support, no need for polyfill
    }

    stickyElements.forEach(element => {
        const originalOffsetTop = element.offsetTop;

        window.addEventListener('scroll', throttle(function() {
            if (window.pageYOffset > originalOffsetTop) {
                element.style.position = 'fixed';
                element.style.top = '0';
                element.style.width = element.parentElement.offsetWidth + 'px';
            } else {
                element.style.position = '';
                element.style.top = '';
                element.style.width = '';
            }
        }, 16)); // ~60fps
    });
}

// Run polyfill after page load
window.addEventListener('load', stickyPolyfill);

// Detect touch devices
function isTouchDevice() {
    return (('ontouchstart' in window) ||
       (navigator.maxTouchPoints > 0) ||
       (navigator.msMaxTouchPoints > 0));
}

// Add touch indicator class to the body
if (isTouchDevice()) {
    document.body.classList.add('touch-device');
}

// Add resize handler for responsive elements
window.addEventListener('resize', debounce(function() {
    // Update any responsive elements that need JS adjustments
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);

    // Close mobile menu if window is resized to desktop
    if (window.innerWidth >= 768) {
        const mobileMenus = document.querySelectorAll('.mobile-menu-open');
        mobileMenus.forEach(menu => {
            menu.classList.remove('mobile-menu-open');
        });

        document.body.style.overflow = '';
    }
}, 100));
