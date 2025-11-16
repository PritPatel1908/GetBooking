@extends('layouts.policy')

@section('content')
<div class="policy-modern">
    <!-- Animated Background Elements -->
    <div class="bg-animation"></div>
    <div class="bg-animation"></div>
    <div class="bg-animation"></div>

    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="policy-header fade-in">
            <div class="policy-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h1 class="policy-title">Contact Us</h1>
            <div class="policy-badge">
                <i class="fas fa-calendar-alt mr-2"></i>
                Last updated on Sep 27th 2025
            </div>
        </div>

        <!-- Main Content -->
        <div class="policy-content slide-up">
            <!-- Header Section -->
            <div class="policy-content-header">
                <h2 class="policy-content-title">Get in Touch</h2>
                <p class="policy-content-subtitle">We're here to help and answer any question you might have</p>
            </div>

            <div class="p-8">
                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Contact Information -->
                    <div class="space-y-6">
                        <div class="policy-card">
                            <div class="policy-card-icon" style="background: var(--primary-gradient);">
                                <i class="fas fa-building"></i>
                            </div>
                            <h3 class="policy-card-title">Company Information</h3>
                            <div class="space-y-4">
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-tag text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white mb-1">Legal Entity Name</h4>
                                        <p class="text-white/80 text-lg font-medium">HEMSAN TECHNO</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white mb-1">Registered Address</h4>
                                        <p class="text-white/80">B1-208, VISHWAS CITY 1, OPP SHAYONA CITY, GHATLODIA, AHMEDABAD AHMEDABAD GUJARAT 380061</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-building text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white mb-1">Operational Address</h4>
                                        <p class="text-white/80">B1-208, VISHWAS CITY 1, OPP SHAYONA CITY, GHATLODIA, AHMEDABAD AHMEDABAD GUJARAT 380061</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-envelope text-white"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white mb-1">Email Address</h4>
                                        <a href="mailto:hemsantechno@gmail.com" class="text-blue-300 hover:text-blue-200 text-lg font-medium transition duration-300">
                                            hemsantechno@gmail.com
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Contact Options -->
                        <div class="policy-card">
                            <div class="policy-card-icon" style="background: var(--accent-gradient);">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3 class="policy-card-title">Quick Contact Options</h3>
                            <div class="space-y-3">
                                <a href="mailto:hemsantechno@gmail.com" class="flex items-center p-4 rounded-xl hover:bg-white/10 transition duration-300 border border-white/20 backdrop-blur-sm">
                                    <i class="fas fa-envelope text-emerald-400 mr-4"></i>
                                    <span class="text-white/80">Send us an email</span>
                                </a>
                                <a href="{{ route('policy.terms') }}" class="flex items-center p-4 rounded-xl hover:bg-white/10 transition duration-300 border border-white/20 backdrop-blur-sm">
                                    <i class="fas fa-file-contract text-blue-400 mr-4"></i>
                                    <span class="text-white/80">Read our Terms & Conditions</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="policy-card" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%); border: 2px solid rgba(255, 255, 255, 0.2);">
                        <div class="policy-card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h3 class="policy-card-title">Send us a Message</h3>
                        <p class="text-white/70 mb-8 text-center">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>

                        <form id="contactForm" class="space-y-6">
                            @csrf
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="name" class="block text-sm font-semibold text-white mb-3 flex items-center">
                                        <i class="fas fa-user mr-2 text-blue-300"></i>
                                        Full Name *
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="name" name="name" required
                                               class="w-full px-4 py-4 bg-white/95 border-2 border-white/30 rounded-2xl focus:outline-none focus:ring-4 focus:ring-blue-500/50 focus:border-blue-400 transition-all duration-300 text-gray-800 placeholder-gray-500 backdrop-blur-sm text-lg font-medium"
                                               placeholder="Enter your full name">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div class="text-red-400 text-sm mt-2 hidden font-medium" id="name-error"></div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="block text-sm font-semibold text-white mb-3 flex items-center">
                                        <i class="fas fa-envelope mr-2 text-emerald-300"></i>
                                        Email Address *
                                    </label>
                                    <div class="relative">
                                        <input type="email" id="email" name="email" required
                                               class="w-full px-4 py-4 bg-white/95 border-2 border-white/30 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/50 focus:border-emerald-400 transition-all duration-300 text-gray-800 placeholder-gray-500 backdrop-blur-sm text-lg font-medium"
                                               placeholder="your@email.com">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                        </div>
                                    </div>
                                    <div class="text-red-400 text-sm mt-2 hidden font-medium" id="email-error"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="subject" class="block text-sm font-semibold text-white mb-3 flex items-center">
                                    <i class="fas fa-tag mr-2 text-purple-300"></i>
                                    Subject *
                                </label>
                                <div class="relative">
                                    <input type="text" id="subject" name="subject" required
                                           class="w-full px-4 py-4 bg-white/95 border-2 border-white/30 rounded-2xl focus:outline-none focus:ring-4 focus:ring-purple-500/50 focus:border-purple-400 transition-all duration-300 text-gray-800 placeholder-gray-500 backdrop-blur-sm text-lg font-medium"
                                           placeholder="What's this about?">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                        <i class="fas fa-tag text-gray-400"></i>
                                    </div>
                                </div>
                                <div class="text-red-400 text-sm mt-2 hidden font-medium" id="subject-error"></div>
                            </div>

                            <div class="form-group">
                                <label for="message" class="block text-sm font-semibold text-white mb-3 flex items-center">
                                    <i class="fas fa-comment-dots mr-2 text-orange-300"></i>
                                    Message *
                                </label>
                                <div class="relative">
                                    <textarea id="message" name="message" rows="6" required
                                              class="w-full px-4 py-4 bg-white/95 border-2 border-white/30 rounded-2xl focus:outline-none focus:ring-4 focus:ring-orange-500/50 focus:border-orange-400 transition-all duration-300 resize-none text-gray-800 placeholder-gray-500 backdrop-blur-sm text-lg font-medium"
                                              placeholder="Tell us how we can help you..."></textarea>
                                    <div class="absolute top-4 right-4">
                                        <i class="fas fa-comment-dots text-gray-400"></i>
                                    </div>
                                </div>
                                <div class="text-red-400 text-sm mt-2 hidden font-medium" id="message-error"></div>
                            </div>

                            <div class="text-center">
                                <button type="submit" id="submitBtn" class="group relative inline-flex items-center justify-center px-12 py-4 bg-gradient-to-r from-emerald-500 to-blue-500 text-white rounded-2xl hover:from-emerald-600 hover:to-blue-600 transition-all duration-300 shadow-2xl hover:shadow-3xl font-bold text-lg hover:-translate-y-1 hover:scale-105 min-w-[200px]">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-blue-400 rounded-2xl blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                                    <div class="relative flex items-center">
                                        <i class="fas fa-paper-plane mr-3 text-xl" id="submitIcon"></i>
                                        <span id="submitText">Send Message</span>
                                        <span id="loadingText" class="hidden">
                                            <i class="fas fa-spinner fa-spin mr-3 text-xl"></i>
                                            Sending...
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </form>

                        <!-- Success/Error Messages -->
                        <div id="successMessage" class="hidden mt-6 p-6 bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-2 border-green-400/40 rounded-2xl backdrop-blur-sm">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-check text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-green-300 font-bold text-lg">Success!</h4>
                                    <span class="text-green-200" id="successText"></span>
                                </div>
                            </div>
                        </div>

                        <div id="errorMessage" class="hidden mt-6 p-6 bg-gradient-to-r from-red-500/20 to-pink-500/20 border-2 border-red-400/40 rounded-2xl backdrop-blur-sm">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-red-300 font-bold text-lg">Error!</h4>
                                    <span class="text-red-200" id="errorText"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Disclaimer -->
                <div class="policy-card" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%); border: 1px solid rgba(255, 193, 7, 0.3);">
                    <div class="policy-card-icon" style="background: var(--warning-gradient);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="policy-card-title">Disclaimer</h3>
                    <p class="policy-card-text">
                        The above content is created at HEMSAN TECHNO's sole discretion. Razorpay shall not be liable for any content provided here and shall not be responsible for any claims and liability that may arise due to merchant's non-adherence to it.
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="policy-buttons">
            <a href="{{ url()->previous() }}" class="policy-btn policy-btn-primary">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </a>
            <a href="{{ route('policy.terms') }}" class="policy-btn policy-btn-secondary">
                <i class="fas fa-file-contract"></i>
                Terms & Conditions
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    const successText = document.getElementById('successText');
    const errorText = document.getElementById('errorText');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous messages
        hideMessages();
        clearErrors();

        // Show loading state
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        loadingText.classList.remove('hidden');

        // Add loading animation to button
        submitBtn.classList.add('animate-pulse');

        // Get form data
        const formData = new FormData(form);

        // Submit form via AJAX
        fetch('{{ route("contact.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess(data.message);
                form.reset();
                // Add success animation
                submitBtn.classList.add('animate-bounce');
                setTimeout(() => {
                    submitBtn.classList.remove('animate-bounce');
                }, 1000);
            } else {
                showError(data.message);
                if (data.errors) {
                    showFieldErrors(data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Something went wrong. Please try again later.');
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            loadingText.classList.add('hidden');
            submitBtn.classList.remove('animate-pulse');
        });
    });

    function showSuccess(message) {
        successText.textContent = message;
        successMessage.classList.remove('hidden');
        errorMessage.classList.add('hidden');

        // Auto hide after 5 seconds
        setTimeout(() => {
            successMessage.classList.add('hidden');
        }, 5000);
    }

    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('hidden');
        successMessage.classList.add('hidden');

        // Auto hide after 5 seconds
        setTimeout(() => {
            errorMessage.classList.add('hidden');
        }, 5000);
    }

    function hideMessages() {
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
    }

    function clearErrors() {
        const errorElements = document.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });
    }

    function showFieldErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(field + '-error');
            if (errorElement) {
                errorElement.textContent = errors[field][0];
                errorElement.classList.remove('hidden');
            }
        });
    }

    // Add focus effects to form fields
    const formFields = document.querySelectorAll('input, textarea');
    formFields.forEach(field => {
        field.addEventListener('focus', function() {
            this.parentElement.classList.add('scale-105');
            this.parentElement.classList.add('shadow-lg');
        });

        field.addEventListener('blur', function() {
            this.parentElement.classList.remove('scale-105');
            this.parentElement.classList.remove('shadow-lg');
        });

        // Add typing animation
        field.addEventListener('input', function() {
            this.classList.add('animate-pulse');
            setTimeout(() => {
                this.classList.remove('animate-pulse');
            }, 200);
        });
    });
});
</script>
@endsection
