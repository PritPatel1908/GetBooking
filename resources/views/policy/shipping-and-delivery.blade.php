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
                <i class="fas fa-shipping-fast"></i>
            </div>
            <h1 class="policy-title">Shipping and Delivery Policy</h1>
            <div class="policy-badge">
                <i class="fas fa-calendar-alt mr-2"></i>
                Last updated on Sep 27th 2025
            </div>
        </div>

        <!-- Main Content -->
        <div class="policy-content slide-up">
            <!-- Header Section -->
            <div class="policy-content-header">
                <h2 class="policy-content-title">Delivery Information</h2>
                <p class="policy-content-subtitle">Everything you need to know about our shipping and delivery process</p>
            </div>

            <div class="p-8">
                <!-- Policies Grid -->
                <div class="policy-grid">
                    <!-- International Shipping -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--primary-gradient);">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="policy-card-title">International Shipping</h3>
                        <p class="policy-card-text">For International buyers, orders are shipped and delivered through registered international courier companies and/or International speed post only.</p>
                        <div class="mt-4 p-4 rounded-xl bg-blue-500/20 border border-blue-400/30">
                            <h4 class="font-bold text-blue-300 mb-3">International Delivery Features:</h4>
                            <div class="policy-list">
                                <li>Registered Courier Service</li>
                                <li>International Speed Post</li>
                                <li>Tracking Available</li>
                                <li>Secure Packaging</li>
                            </div>
                        </div>
                    </div>

                    <!-- Domestic Shipping -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--success-gradient);">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3 class="policy-card-title">Domestic Shipping</h3>
                        <p class="policy-card-text">For domestic buyers, orders are shipped through registered domestic courier companies and /or speed post only.</p>
                        <div class="mt-4 p-4 rounded-xl bg-emerald-500/20 border border-emerald-400/30">
                            <h4 class="font-bold text-emerald-300 mb-3">Domestic Delivery Features:</h4>
                            <div class="policy-list">
                                <li>Fast Domestic Courier</li>
                                <li>Speed Post Service</li>
                                <li>Real-time Tracking</li>
                                <li>Cash on Delivery</li>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Timeline -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--warning-gradient);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="policy-card-title">Delivery Timeline</h3>
                        <p class="policy-card-text">Orders are shipped within Not Applicable or as per the delivery date agreed at the time of order confirmation and delivering of the shipment subject to Courier Company / post office norms.</p>
                        <div class="mt-4 p-4 rounded-xl bg-yellow-500/20 border border-yellow-400/30">
                            <h4 class="font-bold text-yellow-300 mb-3">Timeline Information:</h4>
                            <div class="policy-list">
                                <li>Delivery dates are confirmed at order placement</li>
                                <li>Subject to courier company norms and regulations</li>
                                <li>Delivery times may vary by location</li>
                            </div>
                        </div>
                    </div>

                    <!-- Liability Disclaimer -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--secondary-gradient);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 class="policy-card-title">Liability Disclaimer</h3>
                        <p class="policy-card-text">HEMSAN TECHNO is not liable for any delay in delivery by the courier company / postal authorities and only guarantees to hand over the consignment to the courier company or postal authorities within Not Applicable from the date of the order and payment or as per the delivery date agreed at the time of order confirmation.</p>
                        <div class="mt-4 p-4 rounded-xl bg-red-500/20 border border-red-400/30">
                            <h4 class="font-bold text-red-300 mb-3">Important Notes:</h4>
                            <div class="policy-list">
                                <li>We guarantee handover to courier within specified timeframe</li>
                                <li>Not responsible for courier company delays</li>
                                <li>Delivery dates confirmed at order placement</li>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--accent-gradient);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3 class="policy-card-title">Delivery Address</h3>
                        <p class="policy-card-text">Delivery of all orders will be to the address provided by the buyer. Delivery of our services will be confirmed on your mail ID as specified during registration.</p>
                        <div class="mt-4 p-4 rounded-xl bg-purple-500/20 border border-purple-400/30">
                            <h4 class="font-bold text-purple-300 mb-3">Address Requirements:</h4>
                            <div class="policy-list">
                                <li>Complete and accurate address required</li>
                                <li>Email confirmation sent to registered email</li>
                                <li>Delivery to address provided by buyer only</li>
                            </div>
                        </div>
                    </div>

                    <!-- Support Contact -->
                    <div class="policy-card">
                        <div class="policy-card-icon" style="background: var(--dark-gradient);">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="policy-card-title">Support Contact</h3>
                        <p class="policy-card-text">For any issues in utilizing our services you may contact our helpdesk on or <a href="mailto:hemsantechno@gmail.com" class="text-blue-300 hover:text-blue-200 font-semibold">hemsantechno@gmail.com</a></p>
                        <div class="mt-4 p-4 rounded-xl bg-indigo-500/20 border border-indigo-400/30">
                            <h4 class="font-bold text-indigo-300 mb-3">Support Services:</h4>
                            <div class="policy-list">
                                <li>Email Support</li>
                                <li>Phone Support</li>
                                <li>Live Chat</li>
                                <li>FAQ Support</li>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Information -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="policy-card-title">Important Information</h3>
                    <div class="policy-grid-2">
                        <div class="p-6 rounded-xl bg-white/10 border border-white/20">
                            <h4 class="font-bold text-white mb-4">Shipping Methods</h4>
                            <div class="policy-list">
                                <li>International: Courier & Speed Post</li>
                                <li>Domestic: Courier & Speed Post</li>
                                <li>Secure Packaging Guaranteed</li>
                            </div>
                        </div>
                        <div class="p-6 rounded-xl bg-white/10 border border-white/20">
                            <h4 class="font-bold text-white mb-4">Delivery Confirmation</h4>
                            <div class="policy-list">
                                <li>Email confirmation sent</li>
                                <li>Address verification required</li>
                                <li>Delivery status tracking</li>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--success-gradient);">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="policy-card-title">Need Help?</h3>
                    <p class="policy-card-text mb-6">If you have any questions about shipping or delivery, please don't hesitate to contact us:</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="mailto:hemsantechno@gmail.com" class="policy-btn policy-btn-success">
                            <i class="fas fa-envelope mr-2"></i>
                            Email Support
                        </a>
                        <a href="{{ route('policy.contact') }}" class="policy-btn policy-btn-primary">
                            <i class="fas fa-phone mr-2"></i>
                            Contact Us
                        </a>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="policy-card">
                    <div class="policy-card-icon" style="background: var(--primary-gradient);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="policy-card-title">Contact Information</h3>
                    <p class="policy-card-text">
                        If you have any questions about this Shipping and Delivery Policy, please contact us at:
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
