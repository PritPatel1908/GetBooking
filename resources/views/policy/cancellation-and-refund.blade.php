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
                <i class="fas fa-undo"></i>
            </div>
            <h1 class="policy-title">Cancellation and Refund Policy</h1>
            <div class="policy-badge">
                <i class="fas fa-calendar-alt mr-2"></i>
                Last updated on Sep 27th 2025
            </div>
        </div>

        <!-- Main Content -->
        <div class="policy-content slide-up">
            <!-- Header Section -->
            <div class="policy-content-header">
                <h2 class="policy-content-title">Refund & Cancellation Policy</h2>
                <p class="policy-content-subtitle">Our commitment to customer satisfaction and fair refund practices</p>
            </div>

            <div class="p-8">
                <!-- Introduction -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="policy-card-title">Our Commitment</h3>
                    <p class="policy-card-text text-center text-xl">
                        <span class="policy-highlight">HEMSAN TECHNO</span> believes in helping its customers as far as possible, and has therefore a liberal cancellation policy. Under this policy:
                    </p>
                </div>

                <!-- Policies Grid -->
                <div class="policy-grid">
                    <!-- Cancellation Timeframe -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--accent-gradient);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="policy-card-title">Cancellation Timeframe</h3>
                        <p class="policy-card-text">Cancellations will be considered only if the request is made within <span class="policy-highlight">1-2 days</span> of placing the order. However, the cancellation request may not be entertained if the orders have been communicated to the vendors/merchants and they have initiated the process of shipping them.</p>
                        <div class="mt-4 p-4 rounded-xl bg-blue-500/20 border border-blue-400/30">
                            <p class="text-blue-300 font-medium"><i class="fas fa-info-circle mr-2"></i>Important: Cancellation window is 1-2 days from order placement</p>
                        </div>
                    </div>

                    <!-- Perishable Items Policy -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3 class="policy-card-title">Perishable Items Policy</h3>
                        <p class="policy-card-text">HEMSAN TECHNO does not accept cancellation requests for perishable items like flowers, eatables etc. However, refund/replacement can be made if the customer establishes that the quality of product delivered is not good.</p>
                        <div class="mt-4 p-4 rounded-xl bg-emerald-500/20 border border-emerald-400/30">
                            <p class="text-emerald-300 font-medium"><i class="fas fa-check-circle mr-2"></i>Quality issues with perishable items are eligible for refund/replacement</p>
                        </div>
                    </div>

                    <!-- Damaged or Defective Items -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--warning-gradient);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="policy-card-title">Damaged or Defective Items</h3>
                        <p class="policy-card-text">In case of receipt of damaged or defective items please report the same to our Customer Service team. The request will, however, be entertained once the merchant has checked and determined the same at his own end. This should be reported within <span class="policy-highlight">1-2 days</span> of receipt of the products.</p>
                        <div class="mt-4 p-4 rounded-xl bg-yellow-500/20 border border-yellow-400/30">
                            <p class="text-yellow-300 font-medium"><i class="fas fa-warning mr-2"></i>Report damage within 1-2 days of product receipt</p>
                        </div>
                    </div>

                    <!-- Product Quality Issues -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--secondary-gradient);">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="policy-card-title">Product Quality Issues</h3>
                        <p class="policy-card-text">In case you feel that the product received is not as shown on the site or as per your expectations, you must bring it to the notice of our customer service within <span class="policy-highlight">1-2 days</span> of receiving the product. The Customer Service Team after looking into your complaint will take an appropriate decision.</p>
                        <div class="mt-4 p-4 rounded-xl bg-purple-500/20 border border-purple-400/30">
                            <p class="text-purple-300 font-medium"><i class="fas fa-gavel mr-2"></i>Customer service will review and decide on quality complaints</p>
                        </div>
                    </div>

                    <!-- Warranty Items -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--dark-gradient);">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="policy-card-title">Warranty Items</h3>
                        <p class="policy-card-text">In case of complaints regarding products that come with a warranty from manufacturers, please refer the issue to them.</p>
                        <div class="mt-4 p-4 rounded-xl bg-gray-500/20 border border-gray-400/30">
                            <p class="text-gray-300 font-medium"><i class="fas fa-tools mr-2"></i>Warranty issues should be directed to the manufacturer</p>
                        </div>
                    </div>

                    <!-- Refund Processing Time -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--primary-gradient);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3 class="policy-card-title">Refund Processing Time</h3>
                        <p class="policy-card-text">In case of any Refunds approved by the HEMSAN TECHNO, it'll take <span class="policy-highlight">3-5 days</span> for the refund to be processed to the end customer.</p>
                        <div class="mt-4 p-4 rounded-xl bg-blue-500/20 border border-blue-400/30">
                            <p class="text-blue-300 font-medium"><i class="fas fa-hourglass-half mr-2"></i>Refunds are processed within 3-5 days after approval</p>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--accent-gradient);">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="policy-card-title">Important Notice</h3>
                    <div class="policy-grid-2">
                        <div class="text-center p-6 rounded-xl bg-white/10 border border-white/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                            <h4 class="font-bold text-white mb-2">Cancellation Window</h4>
                            <p class="text-white/80">1-2 days from order placement</p>
                        </div>
                        <div class="text-center p-6 rounded-xl bg-white/10 border border-white/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                            </div>
                            <h4 class="font-bold text-white mb-2">Quality Issues</h4>
                            <p class="text-white/80">Report within 1-2 days of receipt</p>
                        </div>
                        <div class="text-center p-6 rounded-xl bg-white/10 border border-white/20">
                            <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-money-bill-wave text-white text-xl"></i>
                            </div>
                            <h4 class="font-bold text-white mb-2">Refund Processing</h4>
                            <p class="text-white/80">3-5 days after approval</p>
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
                        If you have any questions about this Cancellation and Refund Policy, please contact us at:
                        <br><br>
                        <strong>HEMSAN TECHNO</strong><br>
                        B1-208, VISHWAS CITY 1, OPP SHAYONA CITY, GHATLODIA, AHMEDABAD AHMEDABAD GUJARAT 380061<br>
                        Email: <a href="mailto:hemsantechno@gmail.com" class="text-blue-300 hover:text-blue-200">hemsantechno@gmail.com</a>
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
            <a href="{{ route('policy.contact') }}" class="policy-btn policy-btn-warning">
                <i class="fas fa-envelope"></i>
                Contact Support
            </a>
        </div>
    </div>
</div>
@endsection
