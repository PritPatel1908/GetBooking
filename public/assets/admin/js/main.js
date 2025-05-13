document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    const toastIcon = document.getElementById('toast-icon');

    // Initialize modals management
    initModals();

    // Initialize page animations
    initPageAnimations();

    // Toggle sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    // Highlight active sidebar item based on current page
    // highlightActiveSidebarItem();

    function highlightActiveSidebarItem() {
        // Get current page path
        const currentPath = window.location.pathname;
        const pageName = currentPath.split('/').pop();

        // Clear active class from all items
        sidebarItems.forEach(item => {
            item.classList.remove('active');
        });

        // Add active class to current page link
        sidebarItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href === pageName) {
                item.classList.add('active');
            }
        });
    }

    function initPageAnimations() {
        // Animate table rows if any
        const tableRows = document.querySelectorAll('table tbody tr');
        tableRows.forEach((row, index) => {
            row.classList.add('table-row-appear');
            row.style.animationDelay = `${0.1 + (index * 0.05)}s`;
        });

        // Animate cards and other elements
        const animatedElements = document.querySelectorAll('.animate-fade-in, .animate-slide-up');
        animatedElements.forEach((element, index) => {
            if (!element.style.animationDelay) {
                element.style.animationDelay = `${0.1 + (index * 0.1)}s`;
            }
        });
    }

    function initModals() {
        // Get all modals and their related buttons
        const modals = document.querySelectorAll('[id$="-modal"]');

        modals.forEach(modal => {
            const modalId = modal.id;
            const entityType = modalId.split('-')[0];

            const openBtns = document.querySelectorAll(`.add-${entityType}-btn, .edit-${entityType}-btn`);
            const closeBtns = modal.querySelectorAll(`.close-${entityType}-modal`);
            const form = document.getElementById(`${entityType}-form`);

            // Convert static modal to dynamic modal with backdrop
            modal.classList.add('modal-backdrop');

            // Find modal dialog
            const modalDialog = modal.querySelector('div');
            if (modalDialog) {
                modalDialog.classList.add('modal-dialog');
            }

            // Set up open buttons
            openBtns.forEach(btn => {
                if (!btn) return;

                btn.addEventListener('click', () => {
                    // Open modal with animation
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.add('active');
                    }, 10);

                    // Handle form reset and title for add vs edit
                    if (form && btn.classList.contains(`add-${entityType}-btn`)) {
                        const modalTitle = document.getElementById(`${modalId}-title`);
                        if (modalTitle) {
                            modalTitle.textContent = `Add New ${entityType.charAt(0).toUpperCase() + entityType.slice(1)}`;
                        }
                        form.reset();
                    }
                });
            });

            // Set up close buttons
            closeBtns.forEach(btn => {
                if (!btn) return;

                btn.addEventListener('click', () => {
                    closeModalWithAnimation(modal);
                });
            });

            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModalWithAnimation(modal);
                }
            });

            // NOTE: Form submission is handled in specific handler files (e.g., ground-handler.js)
            // We don't want to add generic form submission handlers here
        });
    }

    function closeModalWithAnimation(modal) {
        if (!modal) return;

        modal.classList.add('modal-exit');
        modal.classList.remove('active');

        // Wait for animation to complete
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('modal-exit');
        }, 300);
    }

    // Click outside modal to close
    const allModals = document.querySelectorAll('.modal-backdrop');
    allModals.forEach(modal => {
        modal?.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModalWithAnimation(modal);
            }
        });
    });

    // Close toast button
    const toastCloseBtn = document.querySelector('#toast button');
    if (toastCloseBtn) {
        toastCloseBtn.addEventListener('click', () => {
            hideToast();
        });
    }

    // Toast notification functions
    window.showToast = function(message, type = 'success') {
        if (!toast || !toastMessage || !toastIcon) return;

        // Set message and icon based on type
        toastMessage.textContent = message;

        // Set icon and color based on type
        if (type === 'success') {
            toastIcon.className = 'fas fa-check-circle text-green-500 text-xl';
        } else if (type === 'error') {
            toastIcon.className = 'fas fa-exclamation-circle text-red-500 text-xl';
        } else if (type === 'warning') {
            toastIcon.className = 'fas fa-exclamation-triangle text-yellow-500 text-xl';
        } else if (type === 'info') {
            toastIcon.className = 'fas fa-info-circle text-blue-500 text-xl';
        }

        // Show toast with animation
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.remove('scale-95', 'opacity-0');
        }, 10);

        // Auto hide after 3 seconds
        setTimeout(() => {
            hideToast();
        }, 3000);
    };

    function hideToast() {
        if (!toast) return;

        toast.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }

    // Theme toggle functionality
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');

            // Store theme preference
            const isDarkTheme = document.body.classList.contains('dark-theme');
            localStorage.setItem('darkTheme', isDarkTheme);

            // Update button icon
            const themeIcon = themeToggleBtn.querySelector('i');
            if (themeIcon) {
                themeIcon.className = isDarkTheme ? 'fas fa-sun' : 'fas fa-moon';
            }

            showToast(`${isDarkTheme ? 'Dark' : 'Light'} theme enabled`, 'info');
        });

        // Check saved theme preference
        const savedDarkTheme = localStorage.getItem('darkTheme') === 'true';
        if (savedDarkTheme) {
            document.body.classList.add('dark-theme');
            const themeIcon = themeToggleBtn.querySelector('i');
            if (themeIcon) {
                themeIcon.className = 'fas fa-sun';
            }
        }
    }

    // Make functions available globally
    window.closeModalWithAnimation = closeModalWithAnimation;
    window.showToast = showToast;
    window.hideToast = hideToast;
});
