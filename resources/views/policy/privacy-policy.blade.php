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
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="policy-title">Privacy Policy</h1>
            <div class="policy-badge">
                <i class="fas fa-calendar-alt mr-2"></i>
                Last updated on Sep 27th 2025
            </div>
        </div>

        <!-- Main Content -->
        <div class="policy-content slide-up">
            <!-- Header Section -->
            <div class="policy-content-header">
                <h2 class="policy-content-title">Your Privacy Matters</h2>
                <p class="policy-content-subtitle">How we collect, use, and protect your personal information</p>
            </div>

            <div class="p-8">
                <!-- Introduction Cards -->
                <div class="policy-grid">
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--primary-gradient);">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3 class="policy-card-title">Our Commitment</h3>
                        <p class="policy-card-text">
                            This privacy policy sets out how <span class="policy-highlight">HEMSAN TECHNO</span> uses and protects any information that you give us when you visit our website.
                        </p>
                    </div>

                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-shield-check"></i>
                        </div>
                        <h3 class="policy-card-title">Privacy Protection</h3>
                        <p class="policy-card-text">
                            We are committed to ensuring that your privacy is protected. Your information will only be used in accordance with this privacy statement.
                        </p>
                    </div>

                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--secondary-gradient);">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3 class="policy-card-title">Policy Updates</h3>
                        <p class="policy-card-text">
                            We may change this policy from time to time by updating this page. Please check this page regularly to stay informed.
                        </p>
                    </div>
                </div>

                <!-- Information We Collect -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--accent-gradient);">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="policy-card-title">Information We Collect</h3>
                    <p class="policy-card-text mb-4">We may collect the following information:</p>
                    <div class="policy-grid-2">
                        <div class="policy-list">
                            <li>Name</li>
                            <li>Email Address</li>
                            <li>Demographic Information</li>
                            <li>Survey & Offer Data</li>
                        </div>
                    </div>
                </div>

                <!-- What We Do With Information -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--success-gradient);">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3 class="policy-card-title">What We Do With The Information We Gather</h3>
                    <p class="policy-card-text mb-4">We require this information to understand your needs and provide you with a better service, and in particular for the following reasons:</p>
                    <div class="policy-grid-2">
                        <div class="policy-list">
                            <li>Internal record keeping</li>
                            <li>We may use the information to improve our products and services</li>
                            <li>We may periodically send promotional emails about new products, special offers or other information</li>
                            <li>We may also use your information to contact you for market research purposes</li>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--warning-gradient);">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3 class="policy-card-title">Security</h3>
                    <p class="policy-card-text mb-4">We are committed to ensuring that your information is secure. In order to prevent unauthorised access or disclosure we have put in suitable measures.</p>
                    <div class="policy-grid-2">
                        <div class="policy-list">
                            <li>Data Encryption</li>
                            <li>Access Controls</li>
                            <li>Privacy Protection</li>
                            <li>Secure Authentication</li>
                        </div>
                    </div>
                </div>

                <!-- How We Use Cookies -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--secondary-gradient);">
                        <i class="fas fa-cookie-bite"></i>
                    </div>
                    <h3 class="policy-card-title">How We Use Cookies</h3>
                    <div class="policy-grid-2">
                        <div>
                            <h4 class="policy-card-title" style="font-size: 1.2rem; margin-bottom: 1rem;">What are Cookies?</h4>
                            <p class="policy-card-text">A cookie is a small file which asks permission to be placed on your computer's hard drive. Once you agree, the file is added and the cookie helps analyze web traffic or lets you know when you visit a particular site.</p>
                        </div>
                        <div>
                            <h4 class="policy-card-title" style="font-size: 1.2rem; margin-bottom: 1rem;">How We Use Them</h4>
                            <p class="policy-card-text">We use traffic log cookies to identify which pages are being used. This helps us analyze data about webpage traffic and improve our website in order to tailor it to customer needs.</p>
                        </div>
                        <div>
                            <h4 class="policy-card-title" style="font-size: 1.2rem; margin-bottom: 1rem;">Benefits</h4>
                            <p class="policy-card-text">Overall, cookies help us provide you with a better website, by enabling us to monitor which pages you find useful and which you do not.</p>
                        </div>
                        <div>
                            <h4 class="policy-card-title" style="font-size: 1.2rem; margin-bottom: 1rem;">Your Control</h4>
                            <p class="policy-card-text">You can choose to accept or decline cookies. Most web browsers automatically accept cookies, but you can usually modify your browser setting to decline cookies if you prefer.</p>
                        </div>
                    </div>
                </div>

                <!-- Controlling Personal Information -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--dark-gradient);">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h3 class="policy-card-title">Controlling Your Personal Information</h3>
                    <div class="policy-grid-2">
                        <div>
                            <h4 class="policy-card-title" style="font-size: 1.2rem; margin-bottom: 1rem;">You can restrict information collection by:</h4>
                            <div class="policy-list">
                                <li>Looking for opt-out boxes when filling forms on our website</li>
                                <li>Changing your marketing preferences at any time by contacting us</li>
                            </div>
                        </div>
                        <div>
                            <h4 class="policy-card-title" style="font-size: 1.2rem; margin-bottom: 1rem;">Data Sharing Policy</h4>
                            <p class="policy-card-text">We will not sell, distribute or lease your personal information to third parties unless we have your permission or are required by law to do so.</p>
                        </div>
                        <div>
                            <h4 class="policy-card-title" style="font-size: 1.2rem; margin-bottom: 1rem;">Data Correction</h4>
                            <p class="policy-card-text">If you believe that any information we are holding on you is incorrect or incomplete, please write to <span class="policy-highlight">B1-208, VISHWAS CITY 1, OPP SHAYONA CITY, GHATLODIA, AHMEDABAD AHMEDABAD GUJARAT 380061</span> or contact us as soon as possible.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="policy-card-title">Contact Information</h3>
                    <p class="policy-card-text">
                        If you have any questions about this Privacy Policy, please contact us at:
                        <br><br>
                        <strong>HEMSAN TECHNO</strong><br>
                        B1-208, VISHWAS CITY 1, OPP SHAYONA CITY, GHATLODIA, AHMEDABAD AHMEDABAD GUJARAT 380061
                    </p>
                </div>

                <!-- Razorpay Disclaimer -->
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
            <a href="{{ route('policy.contact') }}" class="policy-btn policy-btn-secondary">
                <i class="fas fa-envelope"></i>
                Contact Us
            </a>
        </div>
    </div>
</div>
@endsection
