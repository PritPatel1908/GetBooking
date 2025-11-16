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
                <i class="fas fa-file-contract"></i>
            </div>
            <h1 class="policy-title">Terms and Conditions</h1>
            <div class="policy-badge">
                <i class="fas fa-calendar-alt mr-2"></i>
                Last updated on Sep 27th 2025
            </div>
        </div>

        <!-- Main Content -->
        <div class="policy-content slide-up">
            <!-- Header Section -->
            <div class="policy-content-header">
                <h2 class="policy-content-title">Legal Agreement</h2>
                <p class="policy-content-subtitle">Please read these terms carefully before using our services</p>
            </div>

            <div class="p-8">
                <!-- Introduction -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="policy-card-title">Company Information</h3>
                    <p class="policy-card-text">
                        For the purpose of these Terms and Conditions, The term "we", "us", "our" used anywhere on this page shall mean <span class="policy-highlight">HEMSAN TECHNO</span>, whose registered/operational office is <span class="policy-highlight">B1-208, VISHWAS CITY 1, OPP SHAYONA CITY, GHATLODIA, AHMEDABAD AHMEDABAD GUJARAT 380061</span>. "you", "your", "user", "visitor" shall mean any natural or legal person who is visiting our website and/or agreed to purchase from us.
                    </p>
                </div>

                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--accent-gradient);">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="policy-card-title">Agreement</h3>
                    <p class="policy-card-text">
                        Your use of the website and/or purchase from us are governed by following Terms and Conditions:
                    </p>
                </div>

                <!-- Terms Grid -->
                <div class="policy-grid">
                    <!-- Website Content -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="policy-card-title">Website Content</h3>
                        <p class="policy-card-text">The content of the pages of this website is subject to change without notice.</p>
                    </div>

                    <!-- Accuracy of Information -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--warning-gradient);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="policy-card-title">Accuracy of Information</h3>
                        <p class="policy-card-text">Neither we nor any third parties provide any warranty or guarantee as to the accuracy, timeliness, performance, completeness or suitability of the information and materials found or offered on this website for any particular purpose.</p>
                    </div>

                    <!-- Use of Information -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--secondary-gradient);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="policy-card-title">Use of Information</h3>
                        <p class="policy-card-text">Your use of any information or materials on our website and/or product pages is entirely at your own risk, for which we shall not be liable.</p>
                    </div>

                    <!-- Copyright -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--dark-gradient);">
                            <i class="fas fa-copyright"></i>
                        </div>
                        <h3 class="policy-card-title">Copyright and Intellectual Property</h3>
                        <p class="policy-card-text">Our website contains material which is owned by or licensed to us. This material includes, but are not limited to, the design, layout, look, appearance and graphics.</p>
                    </div>

                    <!-- Trademarks -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--primary-gradient);">
                            <i class="fas fa-trademark"></i>
                        </div>
                        <h3 class="policy-card-title">Trademarks</h3>
                        <p class="policy-card-text">All trademarks reproduced in our website which are not the property of, or licensed to, the operator are acknowledged on the website.</p>
                    </div>

                    <!-- Unauthorized Use -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--accent-gradient);">
                            <i class="fas fa-ban"></i>
                        </div>
                        <h3 class="policy-card-title">Unauthorized Use</h3>
                        <p class="policy-card-text">Unauthorized use of information provided by us shall give rise to a claim for damages and/or be a criminal offense.</p>
                    </div>

                    <!-- External Links -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-link"></i>
                        </div>
                        <h3 class="policy-card-title">External Links</h3>
                        <p class="policy-card-text">From time to time our website may also include links to other websites. These links are provided for your convenience to provide further information.</p>
                    </div>

                    <!-- Linking to Our Website -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--warning-gradient);">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                        <h3 class="policy-card-title">Linking to Our Website</h3>
                        <p class="policy-card-text">You may not create a link to our website from another website or document without HEMSAN TECHNO's prior written consent.</p>
                    </div>

                    <!-- Governing Law -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--secondary-gradient);">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h3 class="policy-card-title">Governing Law</h3>
                        <p class="policy-card-text">Any dispute arising out of use of our website and/or purchase with us and/or any engagement with us is subject to the laws of India.</p>
                    </div>

                    <!-- Payment Authorization -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--dark-gradient);">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="policy-card-title">Payment Authorization</h3>
                        <p class="policy-card-text">We, shall be under no liability whatsoever in respect of any loss or damage arising directly or indirectly out of the decline of authorization for any Transaction.</p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="policy-card-title">Contact Information</h3>
                    <p class="policy-card-text">
                        If you have any questions about these Terms and Conditions, please contact us at:
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
