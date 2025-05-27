@extends('layouts.user')

@section('styles')
{{-- <link rel="stylesheet" href="{{ asset('assets/user/css/all-grounds.css') }}"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<style>
    /* View More Button Styling */
    #view-more-btn {
        transition: all 0.3s ease;
        padding: 10px 25px;
        border-radius: 30px;
        box-shadow: 0 4px 15px rgba(52, 144, 220, 0.15);
    }

    #view-more-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(52, 144, 220, 0.25);
    }

    #view-more-btn i {
        transition: transform 0.3s ease;
    }

    #view-more-btn:hover i {
        transform: translateY(3px);
    }

    /* Pulsating animation for View More button */
    @keyframes pulse-border {
        0% { box-shadow: 0 0 0 0 rgba(52, 144, 220, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(52, 144, 220, 0); }
        100% { box-shadow: 0 0 0 0 rgba(52, 144, 220, 0); }
    }

    .btn-animated {
        animation: pulse-border 2s infinite;
    }
    /* Enhanced Theme Colors */
    :root {
        --primary-color: #3490dc;
        --primary-dark: #2779bd;
        --secondary-color: #38c172;
        --accent-color: #f6993f;
        --danger-color: #e3342f;
        --text-color: #2d3748;
        --text-muted: #718096;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --input-bg: #f1f5f9;
        --input-text: #4a5568;
        --border-color: #e2e8f0;
        --input-bg-rgb: 241, 245, 249;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        --card-hover-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        --primary-color-rgb: 52, 144, 220;
    }

    /* Animated Buttons */
    .btn-animated {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        z-index: 1;
        padding: 10px 24px;
        border: none;
        box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3);
        transform: translateY(0);
        transition: all 0.3s ease;
    }

    .btn-animated:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.2), rgba(255,255,255,0));
        transform: skewX(-25deg);
        transition: all 0.5s ease;
        z-index: -1;
    }

    .btn-animated:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(52, 144, 220, 0.4);
    }

    .btn-animated:hover:before {
        left: 100%;
        transition: 0.7s ease;
    }

    /* Enhanced Alert Styling */
    .alert {
        border-radius: 12px;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        padding: 1rem 1.25rem;
        position: relative;
        overflow: hidden;
    }

    .alert:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: currentColor;
        opacity: 0.7;
    }

    .alert-info {
        background: rgba(52, 144, 220, 0.1);
        color: var(--primary-color);
    }

    .alert-warning {
        background: rgba(246, 173, 85, 0.1);
        color: #C05621;
    }

    .alert-danger {
        background: rgba(227, 52, 47, 0.1);
        color: var(--danger-color);
    }

    .alert-success {
        background: rgba(56, 193, 114, 0.1);
        color: var(--secondary-color);
    }

    .alert-link {
        color: inherit;
        text-decoration: underline;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .alert-link:hover {
        color: inherit;
        opacity: 0.8;
    }

    /* Enhanced Icons */
    .fa-star, .fa-check-circle, .fa-info-circle, .fa-exclamation-circle {
        filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
    }

    /* Add shimmer effect to buttons */
    @keyframes shimmer {
        0% {
            background-position: -100% 0;
        }
        100% {
            background-position: 100% 0;
        }
    }

    .book-now-btn, .btn-primary, .btn-warning {
        background-size: 200% 100%;
        animation: shimmer 2s infinite alternate;
    }

    .book-now-btn {
        background-image: linear-gradient(135deg, var(--primary-color), var(--primary-dark), var(--primary-color));
    }

    .btn-primary {
        background-image: linear-gradient(135deg, var(--primary-color), var(--primary-dark), var(--primary-color));
    }

    .btn-warning {
        background-image: linear-gradient(135deg, #F6AD55, #ED8936, #F6AD55);
    }

    /* Enhanced Transitions */
    .ground-info, .review-item, .date-box, .time-slot, .meta-item, .amenity-item {
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    /* Pulsating effect for CTA elements */
    @keyframes softPulse {
        0% {
            box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3);
        }
        50% {
            box-shadow: 0 5px 25px rgba(52, 144, 220, 0.5);
        }
        100% {
            box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3);
        }
    }

    #writeReviewBtn, .book-now-btn {
        animation: softPulse 3s infinite;
    }

    /* Highlighted containers */
    .ground-info, .review-item {
        position: relative;
        background: linear-gradient(to bottom, var(--card-bg), var(--card-bg));
        background-size: 100% 100%;
        z-index: 1;
    }

    .ground-info:after, .review-item:after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(var(--primary-color-rgb), 0.1), transparent);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: -1;
        border-radius: inherit;
        pointer-events: none;
    }

    .ground-info:hover:after, .review-item:hover:after {
        opacity: 1;
    }

    /* Enhanced ground name animation */
    .ground-name {
        background: linear-gradient(90deg, var(--text-color), var(--primary-color), var(--text-color));
        background-size: 200% auto;
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: textShine 5s linear infinite;
    }

    @keyframes textShine {
        to { background-position: 200% center; }
    }

    /* Enhance shadows for depth */
    .ground-image-slider, .ground-info, .review-item, .booking-summary {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05),
                    0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Additional button states */
    .btn:active {
        transform: translateY(0) !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1) !important;
    }

    .dark {
        --primary-color: #4299e1;
        --primary-dark: #3182ce;
        --secondary-color: #48bb78;
        --accent-color: #ed8936;
        --danger-color: #f56565;
        --text-color: #f7fafc;
        --text-muted: #cbd5e0;
        --bg-color: #1a202c;
        --card-bg: #2d3748;
        --input-bg: #4a5568;
        --input-text: #e2e8f0;
        --border-color: #4a5568;
        --input-bg-rgb: 74, 85, 104;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        --card-hover-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    /* Base styles */
    .ground-details-container {
        padding: 2rem 0;
        color: var(--text-color);
        background-color: var(--bg-color);
    }

    /* Enhanced image slider with smoother transitions */
    .ground-image-slider {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        margin-bottom: 2rem;
        height: 400px;
        box-shadow: var(--card-shadow);
    }

    .ground-image-slider img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease, opacity 0.5s ease;
    }

    .ground-image-slider .slider-images {
        position: relative;
        height: 100%;
    }

    .ground-image-slider img:not(.active-image) {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
    }

    .ground-image-slider img.active-image {
        opacity: 1;
        animation: zoomIn 8s ease-in-out infinite alternate;
    }

    @keyframes zoomIn {
        0% { transform: scale(1); }
        100% { transform: scale(1.05); }
    }

    /* Enhanced slider navigation */
    .slider-nav {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 10;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 30px;
        padding: 8px 16px;
    }

    .slider-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .slider-dot.active {
        background-color: #fff;
        transform: scale(1.2);
        box-shadow: 0 0 8px rgba(255, 255, 255, 0.8);
    }

    .slider-controls {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        pointer-events: none;
    }

    .slider-control {
        background: rgba(0, 0, 0, 0.3);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        pointer-events: auto;
        opacity: 0;
    }

    .ground-image-slider:hover .slider-control {
        opacity: 1;
    }

    .slider-control:hover {
        background: rgba(0, 0, 0, 0.6);
        transform: scale(1.1);
    }

    /* Enhanced ground info section */
    .ground-info {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        color: var(--text-color);
        display: flex;
        flex-direction: column;
        height: 100%;
        border-top: 5px solid var(--primary-color);
    }

    .ground-info:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .ground-name {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-color);
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
    }

    .ground-name:after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        transform-origin: left;
        border-radius: 3px;
        animation: underlineAnimation 3s infinite alternate;
    }

    @keyframes underlineAnimation {
        from { transform: scaleX(0.5); }
        to { transform: scaleX(1); }
    }

    .ground-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-color);
        transition: transform 0.3s ease;
        background: rgba(var(--input-bg-rgb), 0.5);
        padding: 8px 12px;
        border-radius: 8px;
    }

    .meta-item:hover {
        transform: translateY(-3px);
        background: rgba(var(--input-bg-rgb), 0.8);
    }

    .meta-item i {
        color: var(--primary-color);
        transition: transform 0.3s ease;
    }

    .meta-item:hover i {
        transform: scale(1.2);
    }

    .ground-description {
        line-height: 1.6;
        color: var(--text-color);
        margin-bottom: 1.5rem;
        padding: 15px;
        background: rgba(var(--input-bg-rgb), 0.3);
        border-radius: 8px;
        border-left: 3px solid var(--primary-color);
    }

    /* Enhanced amenities section */
    .amenity-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        padding: 8px;
        border-radius: 10px;
        transition: all 0.3s ease;
        color: var(--text-color);
        background: rgba(var(--input-bg-rgb), 0.3);
        flex-wrap: nowrap;
    }

    .amenity-item span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .amenity-item:hover {
        background: var(--input-bg);
        transform: translateX(5px);
    }

    .amenity-item i {
        color: var(--secondary-color);
        background: rgba(56, 193, 114, 0.2);
        padding: 8px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .dark .amenity-item i {
        background: rgba(72, 187, 120, 0.2);
    }

    .amenity-item:hover i {
        transform: rotate(360deg);
    }

    /* Enhanced date selector */
    .date-selector {
        display: flex;
        overflow-x: auto;
        gap: 10px;
        padding: 1rem 0;
        margin-bottom: 1.5rem;
        scrollbar-width: thin;
        scrollbar-color: var(--primary-color) var(--input-bg);
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        cursor: grab;
    }

    .date-selector:active {
        cursor: grabbing;
    }

    .date-selector::-webkit-scrollbar {
        height: 6px;
    }

    .date-selector::-webkit-scrollbar-track {
        background: var(--input-bg);
        border-radius: 10px;
    }

    .date-selector::-webkit-scrollbar-thumb {
        background-color: var(--primary-color);
        border-radius: 10px;
    }

    .date-box {
        min-width: 100px;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        color: var(--text-color);
        background: var(--card-bg);
        user-select: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }

    .date-box:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        transform: translateY(100%);
        transition: transform 0.3s ease;
        z-index: -1;
    }

    .date-box:hover {
        border-color: var(--primary-color);
        transform: translateY(-3px);
    }

    .date-box:hover:before {
        transform: translateY(70%);
    }

    .date-box.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3);
    }

    .dark .date-box.active {
        box-shadow: 0 5px 15px rgba(66, 153, 225, 0.4);
    }

    .date-box.active:before {
        transform: translateY(0);
    }

    .date-box .day {
        font-weight: 700;
        font-size: 1.1rem;
    }

    .date-box .date {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Enhanced time slots */
    .time-slots {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 2rem;
    }

    .time-slot-container {
        perspective: 1000px;
    }

    .time-slot {
        padding: 12px;
        border-radius: 8px;
        background: var(--input-bg);
        border: 1px solid var(--border-color);
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        transform-style: preserve-3d;
        height: 100%;
        color: var(--input-text);
    }

    .time-slot.available {
        color: var(--input-text);
    }

    .time-slot.available:hover {
        transform: translateY(-5px);
        border-color: var(--primary-color);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .dark .time-slot.available:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .time-slot.selected {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(52, 144, 220, 0.3);
        animation: pulse 1.5s infinite;
    }

    .dark .time-slot.selected {
        box-shadow: 0 8px 15px rgba(66, 153, 225, 0.4);
    }

    @keyframes pulse {
        0% { box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3); }
        50% { box-shadow: 0 5px 15px rgba(52, 144, 220, 0.6); }
        100% { box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3); }
    }

    .dark @keyframes pulse {
        0% { box-shadow: 0 5px 15px rgba(66, 153, 225, 0.3); }
        50% { box-shadow: 0 5px 15px rgba(66, 153, 225, 0.6); }
        100% { box-shadow: 0 5px 15px rgba(66, 153, 225, 0.3); }
    }

    .time-slot.booked {
        background: var(--input-bg);
        color: var(--border-color);
        cursor: not-allowed;
        border-color: var(--border-color);
        transform: none;
    }

    .time-slot.booked:after {
        content: '✕';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.5rem;
        color: rgba(220, 53, 69, 0.3);
    }

    /* Enhanced booking summary */
    .booking-summary {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        display: none;
        transform: translateY(20px);
        opacity: 0;
        transition: transform 0.5s ease, opacity 0.5s ease;
    }

    .booking-summary.active {
        display: block;
        transform: translateY(0);
        opacity: 1;
    }

    .summary-header {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-color);
        position: relative;
        display: inline-block;
    }

    .summary-header:after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--primary-color);
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.5s ease;
    }

    .booking-summary.active .summary-header:after {
        transform: scaleX(1);
        transform-origin: left;
    }

    .summary-details {
        margin-bottom: 1.5rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed var(--border-color);
        transition: transform 0.3s ease;
        color: var(--text-color);
    }

    .summary-row:hover {
        transform: translateX(5px);
    }

    .total-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .price-amount {
        animation: highlightPrice 2s infinite alternate;
    }

    @keyframes highlightPrice {
        from { color: var(--primary-color); }
        to { color: var(--primary-dark); }
    }

    .book-now-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .book-now-btn:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.2);
        transform: skewX(-30deg);
        transition: all 0.5s ease;
    }

    .book-now-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 144, 220, 0.4);
    }

    .dark .book-now-btn:hover {
        box-shadow: 0 5px 15px rgba(66, 153, 225, 0.5);
    }

    .book-now-btn:hover:before {
        left: 100%;
    }

    /* Pending Payments Button */
    .btn-warning {
        background: rgba(246, 173, 85, 0.1);
        color: #C05621;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
    }

    .btn-warning:hover {
        background: #C05621;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(192, 86, 33, 0.3);
        text-decoration: none;
    }

    .dark .btn-warning:hover {
        box-shadow: 0 5px 15px rgba(192, 86, 33, 0.4);
    }

    /* Enhanced reviews section */
    .reviews-section {
        margin-top: 5rem;
        position: relative;
        border-top: 1px solid #eee;
        padding-top: 3rem;
    }

    .reviews-section:before {
        content: '';
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 60px;
        background-color: #fff;
        border: 1px solid #eee;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }

    .reviews-section:after {
        content: '\f005';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: -18px;
        left: 50%;
        transform: translateX(-50%);
        color: #ffc107;
        font-size: 24px;
        z-index: 2;
    }

    .reviews-header {
        margin-bottom: 2rem;
    }

    .reviews-title {
        font-size: 1.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        margin: 0;
    }

    .reviews-count {
        color: var(--text-muted);
        font-weight: 500;
        margin-left: 5px;
    }

    .write-review-btn {
        padding: 10px 18px;
        font-weight: 600;
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .login-to-review {
        padding: 8px 16px;
        font-weight: 500;
        border-radius: 8px;
        border: 1px solid var(--primary-color);
    }

    .user-reviewed-alert, .login-alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
    }

    .user-reviewed-alert {
        background-color: rgba(52, 144, 220, 0.1);
        border-left: 4px solid var(--primary-color);
    }

    .login-alert {
        background-color: rgba(246, 173, 85, 0.1);
        border-left: 4px solid #ED8936;
    }

    /* Enhanced review item */
    .review-item {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        transform: translateY(0);
        border-left: 4px solid var(--primary-color);
        position: relative;
    }

    .review-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    /* Review header with avatar */
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.2rem;
    }

    .reviewer-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .reviewer-avatar {
        color: var(--primary-color);
        opacity: 0.8;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(52, 144, 220, 0.1);
        border-radius: 50%;
    }

    .reviewer-name {
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 2px;
    }

    .review-date {
        color: var(--text-muted);
        font-size: 0.85rem;
    }

    /* Enhanced review stars */
    .review-stars {
        display: flex;
        align-items: center;
        color: #FFC107;
        margin-bottom: 1rem;
        font-size: 1.1rem;
    }

    .rating-text {
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    .review-stars i {
        margin-right: 3px;
        transition: all 0.3s ease;
    }

    .review-item:hover .review-stars i {
        transform: rotate(360deg);
    }

    .review-text {
        color: var(--text-color);
        line-height: 1.6;
        font-size: 1.05rem;
        padding: 5px 0;
    }

    /* Review actions */
    .review-actions {
        display: flex;
        gap: 8px;
    }

    .review-actions .btn {
        padding: 5px 10px;
        font-size: 0.85rem;
    }

    /* Empty state styling */
    .no-reviews-container {
        text-align: center;
        padding: 3rem 1rem;
        background: rgba(var(--input-bg-rgb), 0.3);
        border-radius: 12px;
        margin-bottom: 2rem;
        border: 1px dashed var(--border-color);
    }

    .no-reviews-icon {
        margin-bottom: 1rem;
    }

    .no-reviews-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--text-color);
    }

    .no-reviews-text {
        color: var(--text-muted);
        margin-bottom: 1.5rem;
    }

    /* Reply styling */
    .review-replies {
        margin-top: 1.5rem;
        border-top: 1px dashed var(--border-color);
        padding-top: 1rem;
    }

    .replies-container {
        margin-left: 1.5rem;
    }

    .reply-item {
        background: rgba(var(--input-bg-rgb), 0.4);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.8rem;
        border-left: 3px solid var(--secondary-color);
        transition: all 0.3s ease;
    }

    .reply-item:hover {
        transform: translateX(5px);
        background: rgba(var(--input-bg-rgb), 0.6);
    }

    .reply-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .replier-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .replier-badge {
        color: var(--secondary-color);
        font-size: 0.8rem;
    }

    .replier-name {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .reply-date {
        color: var(--text-muted);
        font-size: 0.8rem;
    }

    .reply-text {
        font-size: 0.95rem;
        color: var(--text-color);
    }

    .reply-btn {
        margin-left: 1.5rem;
        padding: 5px 12px;
        font-size: 0.85rem;
    }

    /* Enhanced map container */
    .map-container {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .map-container:hover {
        box-shadow: var(--card-hover-shadow);
        transform: scale(1.02);
    }

    /* Enhanced contact info */
    .contact-item {
        transition: all 0.3s ease;
        padding: 10px;
        border-radius: 8px;
    }

    .contact-item:hover {
        background: var(--input-bg);
        transform: translateX(5px);
    }

    .contact-item i {
        background: rgba(52, 144, 220, 0.1);
        padding: 10px;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .dark .contact-item i {
        background: rgba(66, 153, 225, 0.2);
    }

    .contact-item:hover i {
        transform: rotate(360deg);
        background: rgba(52, 144, 220, 0.2);
    }

    .dark .contact-item:hover i {
        background: rgba(66, 153, 225, 0.3);
    }

    /* Stats styling */
    .stat-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(52, 144, 220, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        transition: all 0.3s ease;
    }

    .dark .stat-circle {
        background: rgba(66, 153, 225, 0.2);
    }

    .stat-circle i {
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    .stat-number {
        font-weight: bold;
        color: var(--text-color);
        margin-top: 10px;
    }

    .stat-label {
        color: var(--input-text);
        font-size: 0.9rem;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }

    .fade-in-delay-1 {
        opacity: 0;
        animation: fadeIn 0.5s ease forwards 0.1s;
    }

    .fade-in-delay-2 {
        opacity: 0;
        animation: fadeIn 0.5s ease forwards 0.2s;
    }

    .fade-in-delay-3 {
        opacity: 0;
        animation: fadeIn 0.5s ease forwards 0.3s;
    }

    @media (max-width: 768px) {
        .ground-image-slider {
            height: 250px;
        }

        .ground-meta {
            gap: 1rem;
            justify-content: flex-start;
        }

        .meta-item {
            width: calc(50% - 0.5rem);
            margin-bottom: 0.5rem;
        }

        .time-slots {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        }

        .ground-info {
            padding: 1.5rem;
        }

        .ground-name {
            font-size: 1.6rem;
        }
    }

    @media (max-width: 576px) {
        .meta-item {
            width: 100%;
        }

        .amenity-item {
            padding: 6px;
        }

        .amenity-item i {
            font-size: 0.9rem;
            padding: 6px;
        }

        .ground-info {
            padding: 1rem;
        }

        .ground-name {
            font-size: 1.4rem;
        }
    }

    .date-selector-container {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .date-navigation {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .date-selector {
        display: flex;
        overflow-x: auto;
        gap: 10px;
        padding: 1rem 0;
        scroll-behavior: smooth;
        -ms-overflow-style: none;
        scrollbar-width: none;
        flex: 1;
    }

    .date-selector::-webkit-scrollbar {
        display: none;
    }

    .date-nav-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .date-nav-btn:hover {
        background: var(--primary-dark);
        transform: scale(1.1);
    }

    .date-nav-btn:disabled {
        background: var(--border-color);
        cursor: not-allowed;
        transform: none;
    }

    .date-box {
        min-width: 100px;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        color: var(--text-color);
        background: var(--card-bg);
        flex-shrink: 0;
    }

    .date-box:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--primary-color);
        transform: translateY(100%);
        transition: transform 0.3s ease;
        z-index: -1;
    }

    .date-box:hover {
        border-color: var(--primary-color);
        transform: translateY(-3px);
    }

    .date-box:hover:before {
        transform: translateY(70%);
    }

    .date-box.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(52, 144, 220, 0.3);
    }

    .date-box.active:before {
        transform: translateY(0);
    }

    .date-box .day {
        font-weight: 700;
        font-size: 1.1rem;
    }

    .date-box .date {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Review Styling */
    .review-item {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }

    .review-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.8rem;
    }

    .reviewer-info {
        display: flex;
        flex-direction: column;
    }

    .reviewer-name {
        font-weight: 600;
        color: var(--text-color);
    }

    .review-date {
        color: var(--input-text);
        font-size: 0.9rem;
    }

    .review-stars {
        color: #ffc107;
        margin-bottom: 0.8rem;
    }

    .review-text {
        color: var(--text-color);
        line-height: 1.6;
    }

    .review-actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Reply Styling */
    .review-replies {
        margin-top: 1rem;
        border-top: 1px dashed var(--border-color);
        padding-top: 1rem;
    }

    .replies-container {
        margin-bottom: 1rem;
    }

    .reply-item {
        background: var(--input-bg);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        margin-left: 1.5rem;
        border-left: 3px solid var(--secondary-color);
        transition: all 0.3s ease;
    }

    .reply-item:hover {
        transform: translateX(5px);
        background: rgba(var(--input-bg-rgb), 0.7);
    }

    .reply-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .replier-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .replier-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-color);
    }

    .reply-date {
        color: var(--input-text);
        font-size: 0.8rem;
    }

    .reply-text {
        color: var(--text-color);
        font-size: 0.95rem;
    }

    .reply-actions {
        display: flex;
        gap: 0.25rem;
    }

    /* Rating Input Styling */
    .rating-input {
        display: flex;
        gap: 0.5rem;
        font-size: 1.5rem;
        color: #ffc107;
        margin-bottom: 1rem;
    }

    .rating-input i {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .rating-input i:hover {
        transform: scale(1.2);
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .review-header {
            flex-direction: column;
        }

        .review-actions {
            margin-top: 0.5rem;
        }

        .reply-item {
            margin-left: 0.5rem;
        }
    }

    /* Custom Modal Styles */
    .custom-modal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        overflow: hidden;
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.98);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, var(--danger-color), #dc3545);
    }

    .custom-modal .modal-header {
        border-bottom: none;
        padding: 20px 25px;
        position: relative;
        overflow: hidden;
    }

    .custom-modal .modal-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.2);
    }

    .custom-modal .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
    }

    .custom-modal .modal-header .close {
        color: white;
        opacity: 0.8;
        text-shadow: none;
        transition: all 0.3s ease;
        margin: -1rem 0 -1rem auto;
        padding: 0.75rem 1rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }

    .custom-modal .modal-header .close:hover {
        opacity: 1;
        transform: rotate(90deg);
        background: rgba(255, 255, 255, 0.2);
    }

    .custom-modal .modal-body {
        padding: 25px;
        position: relative;
    }

    .custom-modal .modal-footer {
        border-top: none;
        padding: 15px 25px 25px;
    }

    .custom-modal label {
        font-weight: 500;
        margin-bottom: 10px;
        color: var(--text-color);
    }

    .custom-modal .form-control {
        border-radius: 10px;
        padding: 15px;
        border: 1px solid var(--border-color);
        background-color: var(--input-bg);
        color: var(--text-color);
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        font-size: 1rem;
    }

    .custom-modal .form-control:focus {
        box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.25);
        border-color: var(--primary-color);
        background-color: #fff;
    }

    .custom-modal textarea.form-control {
        min-height: 100px;
        line-height: 1.6;
    }

    .custom-modal .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    }

    .custom-modal .btn-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border: none;
        box-shadow: 0 4px 10px rgba(52, 144, 220, 0.3);
    }

    .custom-modal .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(52, 144, 220, 0.4);
    }

    .custom-modal .btn-light {
        background: #f8f9fa;
        color: #5a6268;
        border: 1px solid #eaedf0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .custom-modal .btn-light:hover {
        background: #e2e6ea;
        color: #212529;
        transform: translateY(-2px);
    }

    .custom-modal .btn-danger {
        background: linear-gradient(135deg, var(--danger-color), #dc3545);
        border: none;
        box-shadow: 0 4px 10px rgba(227, 52, 47, 0.3);
    }

    .custom-modal .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(227, 52, 47, 0.4);
    }

    /* Enhanced Rating Input */
    .custom-modal .rating-input {
        display: flex;
        gap: 12px;
        font-size: 2.5rem;
        color: #ffc107;
        margin: 15px 0 20px;
        justify-content: center;
    }

    .custom-modal .rating-input i {
        cursor: pointer;
        transition: all 0.3s ease;
        filter: drop-shadow(0 2px 3px rgba(0,0,0,0.1));
    }

    .custom-modal .rating-input i:hover {
        transform: scale(1.3) rotate(5deg);
    }

    .custom-modal .rating-text {
        height: 20px;
        font-weight: 500;
    }

    /* Rating hover effects */
    .rating-input i.far.active-hover,
    .rating-input i.fas.active-hover {
        transform: scale(1.2);
        color: #ffce3d;
        filter: drop-shadow(0 0 3px rgba(255, 193, 7, 0.5));
    }

    /* Modal Animation */
    .modal.fade .modal-dialog {
        transform: translateY(-30px) scale(0.95);
        opacity: 0;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
    }

    .modal.show .modal-dialog {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Custom modal positioning to ensure centering */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }

    @media (min-width: 576px) {
        .modal-dialog-centered {
            min-height: calc(100% - 3.5rem);
        }
    }

    /* Rating stars animations */
    .pulse-animation .far,
    .pulse-animation .fas {
        animation: starsPulse 0.5s ease-in-out;
    }

    @keyframes starsPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.3); }
        100% { transform: scale(1); }
    }

    .rating-input.edit-mode {
        animation: editHighlight 1s ease;
    }

    @keyframes editHighlight {
        0% { filter: brightness(1); }
        30% { filter: brightness(1.2); }
        100% { filter: brightness(1); }
    }

    /* Form group animations */
    .custom-modal .form-group {
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .custom-modal.show .form-group {
        animation: slideIn 0.5s forwards;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Dark mode modal adjustments */
    .dark .custom-modal .modal-content {
        background-color: rgba(45, 55, 72, 0.98);
    }

    .dark .custom-modal .form-control {
        background-color: var(--card-bg);
        border-color: var(--border-color);
        color: var(--text-color);
    }

    .dark .custom-modal .form-control:focus {
        background-color: rgba(74, 85, 104, 0.8);
    }

    .dark .custom-modal .btn-light {
        background: #4a5568;
        color: #e2e8f0;
        border-color: #4a5568;
    }

    .dark .custom-modal .btn-light:hover {
        background: #2d3748;
    }

    /* Keep only the necessary styles for the review system */

    /* Basic styling for the review section */
    .review-section {
        margin-top: 40px;
        padding: 20px 0;
    }

    /* Simple rating stars with no background */
    .rating-stars {
        font-size: 1.8rem;
        color: #ffc107;
        cursor: pointer;
        display: flex;
        justify-content: center;
        margin-bottom: 10px;
    }

    .rating-stars i {
        margin: 0 5px;
        transition: transform 0.2s;
    }

    .rating-stars i:hover {
        transform: scale(1.2);
    }

    /* Rating meaning text */
    .rating-meaning {
        text-align: center;
        font-size: 1rem;
        margin-bottom: 20px;
        min-height: 24px;
        color: #555;
    }

    /* Simple review form */
    .review-form {
        width: 100%;
        margin: 0 auto;
    }

    .review-input {
        width: 100%;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 1rem;
        resize: vertical;
        min-height: 100px;
    }

    .review-input:focus {
        outline: none;
        border-color: #3490dc;
        box-shadow: 0 0 0 2px rgba(52, 144, 220, 0.25);
    }

    .submit-btn {
        background: #3490dc;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .submit-btn:hover {
        background: #2779bd;
        transform: translateY(-2px);
    }

        /* Enhanced review list */
    .reviews-list {
        margin-top: 40px;
    }

    .review-item {
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #3490dc;
        background-color: #f9f9f9;
        transition: transform 0.2s ease;
    }

    .review-item:hover {
        transform: translateY(-3px);
    }

    .review-item:last-child {
        margin-bottom: 0;
    }

    .reviewer-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .reviewer-name {
        font-weight: bold;
        font-size: 1.1rem;
        color: #333;
    }

    .review-date {
        color: #777;
        font-size: 0.9rem;
    }

    .review-stars-display {
        color: #ffc107;
        margin: 15px 0;
        font-size: 1.2rem;
    }

    .review-stars-display i {
        margin-right: 3px;
    }

    .review-stars-display span {
        color: #555;
        font-weight: 500;
        margin-left: 10px;
        vertical-align: middle;
    }

    .review-text {
        margin-top: 15px;
        line-height: 1.6;
        font-size: 1.05rem;
        color: #444;
        padding: 5px 0;
    }

    /* Simple div with 2 partitions */
    .review-container {
        border: 1px solid #eee;
        border-radius: 10px;
        overflow: hidden;
        margin: 20px 0;
    }

    .review-container-top {
        padding: 20px;
        border-bottom: 1px solid #eee;
        text-align: center;
    }

    .review-container-bottom {
        padding: 20px;
    }

    /* Login reminder */
    .login-reminder {
        text-align: center;
        margin: 30px 0;
    }

    .login-link {
        color: #3490dc;
        text-decoration: none;
        font-weight: bold;
    }

    .login-link:hover {
        text-decoration: underline;
    }

    /* Review Section Title */
    .review-section-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: #333;
        padding-bottom: 10px;
        border-bottom: 2px solid #eee;
        position: relative;
    }

    .review-section-title:after {
        content: '';
        position: absolute;
        width: 80px;
        height: 3px;
        background-color: #3490dc;
        bottom: -2px;
        left: 0;
    }

    /* Review Count */
    .reviews-count {
        display: flex;
        align-items: baseline;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .review-count-number {
        font-size: 2rem;
        font-weight: 700;
        color: #3490dc;
        margin-right: 10px;
    }

    .review-count-text {
        font-size: 1.2rem;
        color: #666;
    }

    /* For mobile responsiveness */
    @media (max-width: 768px) {
        .rating-stars {
            font-size: 1.5rem;
        }

        .review-section-title {
            font-size: 1.5rem;
        }

        .review-count-number {
            font-size: 1.6rem;
        }

        .review-count-text {
            font-size: 1rem;
        }

        .review-item {
            padding: 15px;
        }
    }
</style>
@endsection

@section('content')
<div class="container ground-details-container">
    <div class="row">
        <div class="col-lg-8">
            <!-- Ground Image Slider -->
            <div class="ground-image-slider" data-aos="fade-up">
                <div class="slider-images">
                    @if($ground->images->count() > 0)
                        @foreach($ground->images as $index => $image)
                            <img src="{{ asset($image->image_path) }}" alt="{{ $ground->name }}" class="{{ $index === 0 ? 'active-image' : '' }}">
                        @endforeach
                    @elseif($ground->ground_image)
                        <img src="{{ asset($ground->ground_image) }}" alt="{{ $ground->name }}" class="active-image">
                    @else
                        <img src="{{ asset('assets/images/ground-placeholder.jpg') }}" alt="{{ $ground->name }}" class="active-image">
                    @endif
                </div>
                <div class="slider-controls">
                    <div class="slider-control prev-slide">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="slider-control next-slide">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                <div class="slider-nav">
                    @if($ground->images->count() > 0)
                        @foreach($ground->images as $index => $image)
                            <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}"></div>
                        @endforeach
                    @elseif($ground->ground_image)
                        <div class="slider-dot active" data-index="0"></div>
                    @else
                        <div class="slider-dot active" data-index="0"></div>
                    @endif
                </div>
            </div>

            <!-- Ground Info -->
            <div class="ground-info" data-aos="fade-up" data-aos-delay="100">
                <h1 class="ground-name">{{ $ground->name }}</h1>

                <div class="ground-meta d-flex flex-wrap justify-content-between">
                    <div class="meta-item fade-in-delay-1">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $ground->location }}</span>
                    </div>
                    <div class="meta-item fade-in-delay-1">
                        <i class="fas fa-list-alt"></i>
                        <span>{{ ucfirst($ground->ground_category ?? 'All Grounds') }}</span>
                    </div>
                    <div class="meta-item fade-in-delay-2">
                        <i class="fas fa-star"></i>
                        <span>
                            @php
                                $reviewsCount = $ground->reviews->count();
                                $avgRating = $reviewsCount > 0 ? round($ground->reviews->avg('rating'), 1) : 0;
                            @endphp
                            {{ $avgRating }} ({{ $reviewsCount }} {{ Str::plural('review', $reviewsCount) }})
                        </span>
                    </div>
                    <div class="meta-item fade-in-delay-3">
                        <i class="fas fa-rupee-sign"></i>
                        <span>₹{{ $ground->price_per_hour }}/hour</span>
                    </div>
                    <div class="meta-item fade-in-delay-3">
                        <i class="fas fa-phone"></i>
                        <span>{{ $ground->phone }}</span>
                    </div>
                </div>

                <div class="ground-description">
                    {{ $ground->description }}
                </div>

                <div class="ground-features">
                    <h4>Amenities</h4>
                    <div class="row mt-3">
                        @if($ground->features->count() > 0)
                            @foreach($ground->features as $feature)
                                <div class="col-lg-3 col-md-4 col-6 mb-2">
                                    <div class="amenity-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>{{ $feature->feature_name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12">
                                <p>No amenities listed for this ground.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Booking Section -->
            <div class="booking-section" data-aos="fade-up" data-aos-delay="200">
                <h3 class="mb-4">Book a Slot</h3>

                <!-- Date Selection -->
                <div class="date-selector-container">
                    <div class="date-navigation">
                        <button class="date-nav-btn prev-dates" id="prevDates">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="date-selector" id="dateSelector">
                            @php
                            $startDate = \Carbon\Carbon::now();
                            $endDate = \Carbon\Carbon::now()->addMonths(3);
                            $currentDate = $startDate;
                            @endphp
                            @while($currentDate <= $endDate)
                                <div class="date-box {{ $currentDate->isToday() ? 'active' : '' }}"
                                     data-date="{{ $currentDate->format('Y-m-d') }}">
                                    <div class="day">{{ $currentDate->format('D') }}</div>
                                    <div class="date">{{ $currentDate->format('d M') }}</div>
                                </div>
                                @php
                                $currentDate->addDay();
                                @endphp
                            @endwhile
                        </div>
                        <button class="date-nav-btn next-dates" id="nextDates">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Time Slots -->
                <h5 class="mb-3">Available Time Slots</h5>
                <div class="time-slots">
                    @php
                    // Get opening and closing time
                    $openingTime = \Carbon\Carbon::parse($ground->opening_time ?? '08:00:00');
                    $closingTime = \Carbon\Carbon::parse($ground->closing_time ?? '22:00:00');
                    $interval = 2; // 2 hour slots
                    $selectedDate = \Carbon\Carbon::now()->format('Y-m-d');

                    // Get all slots for this ground
                    $groundSlots = $ground->slots;

                    // Get booked slots for the selected date through BookingDetail
                    $bookedSlotIds = \App\Models\BookingDetail::where('ground_id', $ground->id)
                        ->whereHas('booking', function($query) use ($selectedDate) {
                            $query->where('booking_date', $selectedDate)
                                  ->where('booking_status', '!=', 'cancelled');
                        })
                        ->pluck('slot_id')
                        ->toArray();

                    // If no slots defined in database, generate them based on opening/closing time
                    if($groundSlots->isEmpty()) {
                        $slots = [];
                        $currentTime = clone $openingTime;

                        while($currentTime < $closingTime) {
                            $slotStart = $currentTime->format('H:i');
                            $currentTime->addHours($interval);
                            $slotEnd = $currentTime->format('H:i');

                            if (\Carbon\Carbon::parse($slotEnd) <= $closingTime) {
                                $slots[] = [
                                    'id' => null, // No ID since these are generated
                                    'time' => "$slotStart-$slotEnd",
                                    'price' => round($ground->price_per_hour * $interval),
                                    'hours' => $interval,
                                    'available' => true // Default is available, we'll check bookings later
                                ];
                            }
                        }
                    } else {
                        // Use slots from database
                        $slots = [];
                        foreach($groundSlots as $slot) {
                            // Calculate hours from slot time range (assuming format "HH:MM-HH:MM")
                            $times = explode('-', $slot->slot_name);
                            if(count($times) == 2) {
                                $start = \Carbon\Carbon::parse($times[0]);
                                $end = \Carbon\Carbon::parse($times[1]);

                                // Handle slots that cross midnight
                                if($end < $start) {
                                    $end->addDay();
                                }

                                $hours = $end->diffInMinutes($start) / 60;
                                // Ensure we always have a positive duration
                                $hours = abs($hours);
                                // Round to nearest 0.5 for better display
                                $hours = round($hours * 2) / 2;
                            } else {
                                $hours = $interval; // Default if can't parse
                            }

                            $slots[] = [
                                'id' => $slot->id,
                                'time' => $slot->slot_name, // Assuming slot_name is in format "HH:MM-HH:MM"
                                'price' => round($ground->price_per_hour * $hours), // Calculate based on actual hours
                                'hours' => $hours,
                                'available' => !in_array($slot->id, $bookedSlotIds) && $slot->slot_status === 'active'
                            ];
                        }
                    }
                    @endphp

                    @foreach($slots as $index => $slot)
                        <div class="time-slot-container" data-aos="zoom-in" data-aos-delay="{{ 100 + ($index * 50) }}">
                            <div class="time-slot {{ $slot['available'] ? 'available' : 'booked' }}"
                                data-time="{{ $slot['time'] }}"
                                data-slot-id="{{ $slot['id'] }}"
                                data-price="{{ $slot['price'] }}"
                                data-hours="{{ $slot['hours'] }}"
                                data-available="{{ $slot['available'] ? 'true' : 'false' }}">
                                <div>{{ str_replace('-', ' - ', $slot['time']) }}</div>
                                <div>₹{{ $slot['price'] }} ({{ $slot['hours'] }} hours)</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Booking Summary -->
                <div class="booking-summary">
                    <div class="summary-header">Booking Summary</div>
                    <div class="summary-details">
                        <div class="summary-row">
                            <div>Date</div>
                            <div class="selected-date">{{ \Carbon\Carbon::now()->format('M d, Y') }}</div>
                        </div>
                        <div class="summary-row">
                            <div>Time</div>
                            <div class="selected-time">--:-- to --:--</div>
                        </div>
                        <div class="summary-row">
                            <div>Duration</div>
                            <div class="selected-duration">0 hours</div>
                        </div>
                        <div class="summary-row">
                            <div>Rate</div>
                            <div>₹{{ $ground->price_per_hour }}/hour</div>
                        </div>
                        <div class="total-price">
                            <span>Total Amount</span>
                            <span>₹<span class="price-amount">0</span></span>
                        </div>
                    </div>
                    <button class="book-now-btn">Book Now <i class="fas fa-arrow-right ml-2"></i></button>
                </div>
            </div>

            <!-- Enhanced Reviews Section -->
            <div class="reviews-section mt-5 pt-4" data-aos="fade-up">
                <h2 class="mb-4 review-section-title">
                    <i class="fas fa-star text-warning mr-2"></i>
                    Customer Reviews
                </h2>

                @auth
                    @php
                        $userHasReviewed = $ground->reviews->where('user_id', Auth::id())->first();
                    @endphp

                    @if(!$userHasReviewed)
                        <div class="review-container">
                            <div class="review-container-top">
                                <h4 class="mb-3">Rate this ground</h4>
                                <div class="rating-stars">
                                    <i class="far fa-star" data-rating="1"></i>
                                    <i class="far fa-star" data-rating="2"></i>
                                    <i class="far fa-star" data-rating="3"></i>
                                    <i class="far fa-star" data-rating="4"></i>
                                    <i class="far fa-star" data-rating="5"></i>
                                </div>
                                <div class="rating-meaning">Select stars to rate</div>
                            </div>

                            <div class="review-container-bottom">
                                <form id="reviewForm" class="review-form">
                                    <input type="hidden" name="ground_id" value="{{ $ground->id }}">
                                    <input type="hidden" name="rating" id="rating_input" value="">

                                    <textarea class="review-input" name="comment" placeholder="Share your experience with this ground..." required></textarea>

                                    <button type="button" id="submitReview" class="submit-btn">Submit Review</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="review-container">
                            <div class="review-container-top">
                                <h4 class="mb-3">Edit your rating</h4>
                                <div class="rating-stars edit-mode">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $userHasReviewed->rating ? 'fas' : 'far' }} fa-star" data-rating="{{ $i }}"></i>
                                    @endfor
                                </div>
                                <div class="rating-meaning">
                                    {{ ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][$userHasReviewed->rating] }}
                                </div>
                            </div>

                            <div class="review-container-bottom">
                                <form id="reviewForm" class="review-form">
                                    <input type="hidden" name="ground_id" value="{{ $ground->id }}">
                                    <input type="hidden" name="rating" id="rating_input" value="{{ $userHasReviewed->rating }}">
                                    <input type="hidden" id="review_id" value="{{ $userHasReviewed->id }}">

                                    <textarea class="review-input" name="comment" required>{{ $userHasReviewed->comment }}</textarea>

                                    <div class="d-flex justify-content-between">
                                        <button type="button" id="submitReview" class="submit-btn">Update Review</button>
                                        <button type="button" id="deleteReview" class="btn btn-outline-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="login-reminder">
                        <p>Please <a href="{{ route('login') }}?redirect={{ url()->current() }}" class="login-link">login</a> to leave a review</p>
                    </div>
                @endauth

                <!-- Existing Reviews -->
                <div class="reviews-list">
                    <div class="reviews-count mb-4">
                        <span class="review-count-number">{{ count($ground->reviews) }}</span>
                        <span class="review-count-text">{{ count($ground->reviews) == 1 ? 'Review' : 'Reviews' }}</span>
                    </div>

                    @if($ground->reviews->count() > 0)
                        <div id="reviews-container">
                            <!-- Reviews will be loaded here by JavaScript -->
                        </div>

                                <div style="height: 10px;"></div>
        <div class="text-center mb-4" id="view-more-container" style="display: none; padding-top: 20px;">
            <button id="view-more-btn" class="btn btn-outline-primary btn-animated">
                View More <i class="fas fa-chevron-down ml-1"></i>
            </button>
            <div class="mt-2 text-muted small">
                <span id="reviews-shown-count">3</span> of <span id="total-reviews-count">0</span> reviews shown
            </div>
        </div>
                    @else
                        <p class="text-center text-muted">No reviews yet. Be the first to review!</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- <div class="col-lg-4">
            <!-- Location Map -->
            <div class="ground-info mb-4" data-aos="fade-left">
                <h4 class="mb-3">Location</h4>
                <div class="map-container" style="height: 300px;">
                    <iframe width="100%" height="100%" frameborder="0" style="border:0"
                        src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q={{ urlencode($ground->location) }}"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="mt-3">
                    <i class="fas fa-map-marker-alt text-danger"></i>
                    {{ $ground->location }}
                </div>
            </div>

            <!-- Contact Info -->
            <div class="ground-info" data-aos="fade-left" data-aos-delay="100">
                <h4 class="mb-3">Contact Information</h4>
                <div class="contact-item d-flex align-items-center mb-3">
                    <i class="fas fa-user mr-3 text-primary"></i>
                    <div>
                        <div>{{ $ground->client->name ?? 'Ground Owner' }}</div>
                        <div class="text-muted">Ground Owner</div>
                    </div>
                </div>
                <div class="contact-item d-flex align-items-center mb-3">
                    <i class="fas fa-phone mr-3 text-primary"></i>
                    <div>
                        <div>{{ $ground->phone }}</div>
                        <div class="text-muted">Call for inquiries</div>
                    </div>
                </div>
                <div class="contact-item d-flex align-items-center">
                    <i class="fas fa-envelope mr-3 text-primary"></i>
                    <div>
                        <div>{{ $ground->email }}</div>
                        <div class="text-muted">Email for bookings</div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="ground-info mt-4" data-aos="fade-left" data-aos-delay="200">
                <h4 class="mb-3">Ground Stats</h4>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="stat-circle mb-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="stat-number">{{ \App\Models\BookingDetail::where('ground_id', $ground->id)->count() }}</h5>
                        <div class="stat-label">Bookings</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-circle mb-2">
                            <i class="fas fa-star"></i>
                        </div>
                        <h5 class="stat-number">{{ $avgRating }}</h5>
                        <div class="stat-label">Rating</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-circle mb-2">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5 class="stat-number">98%</h5>
                        <div class="stat-label">Availability</div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</div>

@endsection

@section('scripts')
<!-- Add jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- JavaScript for functionality -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    // Initialize AOS (Animate on Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Initialize selected date with today's date
    let selectedDate = "{{ \Carbon\Carbon::now()->format('Y-m-d') }}";

        // Image slider functionality
        const sliderDots = document.querySelectorAll('.slider-dot');
        const sliderImages = document.querySelectorAll('.ground-image-slider img');
        let currentSlide = 0;
        let totalSlides = sliderDots.length;
        console.log('Total slides: ' + totalSlides);
        console.log('Total images: ' + sliderImages.length);

        // Make sure first image is active
        if (sliderImages.length > 0) {
            sliderImages.forEach(img => {
                img.classList.remove('active-image');
                img.style.opacity = 0;
            });
            sliderImages[0].classList.add('active-image');
            sliderImages[0].style.opacity = 1;
        }

        function showSlide(index) {
            // Check if elements exist
            if (sliderDots.length === 0 || sliderImages.length === 0) return;

            // Update active dot
            const activeDot = document.querySelector('.slider-dot.active');
            if (activeDot) activeDot.classList.remove('active');
            if (sliderDots[index]) sliderDots[index].classList.add('active');

            // Hide all images
            sliderImages.forEach(img => {
                img.classList.remove('active-image');
                img.style.opacity = 0;
            });

            // Show active image with fade effect
            if (sliderImages[index]) {
                sliderImages[index].classList.add('active-image');
                sliderImages[index].style.opacity = 1;
            }

            currentSlide = index;
        }

        sliderDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
            });
        });

        // Previous and Next buttons
        const prevBtn = document.querySelector('.prev-slide');
        const nextBtn = document.querySelector('.next-slide');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                let newIndex = currentSlide - 1;
                if (newIndex < 0) newIndex = totalSlides - 1;
                showSlide(newIndex);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                let newIndex = currentSlide + 1;
                if (newIndex >= totalSlides) newIndex = 0;
                showSlide(newIndex);
            });
        }

        // Auto slide only if we have multiple images
        if (totalSlides > 1) {
            setInterval(() => {
                let newIndex = currentSlide + 1;
                if (newIndex >= totalSlides) newIndex = 0;
                showSlide(newIndex);
            }, 5000);
        }

        // Date selection functionality
        const dateSelector = document.getElementById('dateSelector');
        const prevDates = document.getElementById('prevDates');
        const nextDates = document.getElementById('nextDates');
        const scrollAmount = 300;
        let isDragging = false;
        let startX;
        let scrollLeft;
        let touchStartX = null;

        // Function to check if we can scroll further
        function updateNavigationButtons() {
            prevBtn.disabled = dateSelector.scrollLeft <= 0;
            nextBtn.disabled = dateSelector.scrollLeft + dateSelector.clientWidth >= dateSelector.scrollWidth;
        }

        // Initialize button states
    updateNavigationButtons();

    // Previous dates button click
    prevDates.addEventListener('click', () => {
        dateSelector.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
        setTimeout(updateNavigationButtons, 500);
    });

    // Set up click handlers for date boxes
    const dateBoxes = document.querySelectorAll('.date-box');
    dateBoxes.forEach(dateBox => {
        dateBox.addEventListener('click', function() {
            // Update selectedDate value when a date is clicked
            selectedDate = this.getAttribute('data-date');

            // Remove active class from all date boxes
            dateBoxes.forEach(box => box.classList.remove('active'));

            // Add active class to the clicked date box
            this.classList.add('active');

            // Update the displayed selected date in the booking summary
            const selectedDateElement = document.querySelector('.selected-date');
            if (selectedDateElement) {
                const formattedDate = new Date(selectedDate).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                selectedDateElement.textContent = formattedDate;
            }

            // Load time slots for the selected date
            loadTimeSlots(selectedDate);

            console.log('Selected date:', selectedDate);
        });
    });

    // Function to load time slots for selected date
    function loadTimeSlots(date) {
        const timeSlotsContainer = document.querySelector('.time-slots');
        if (!timeSlotsContainer) return;

        // Show loading indicator
        timeSlotsContainer.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading available slots...</div>';

        // Reset slot selection
        resetSlotSelection();

        // Fetch slots from server
        fetch(`/get-ground-slots/${date}/{{ $ground->id }}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear loading indicator
                    timeSlotsContainer.innerHTML = '';

                    if (data.slots && data.slots.length > 0) {
                        // Create slot elements
                        data.slots.forEach((slot, index) => {
                            const slotElement = document.createElement('div');
                            slotElement.className = 'time-slot-container';
                            slotElement.setAttribute('data-aos', 'zoom-in');
                            slotElement.setAttribute('data-aos-delay', `${100 + (index * 50)}`);

                            slotElement.innerHTML = `
                                <div class="time-slot ${slot.available ? 'available' : 'booked'}"
                                    data-time="${slot.time}"
                                    data-slot-id="${slot.id}"
                                    data-price="${slot.price}"
                                    data-hours="${slot.hours}"
                                    data-available="${slot.available ? 'true' : 'false'}">
                                    <div>${slot.time.replace('-', ' - ')}</div>
                                    <div>₹${slot.price} (${slot.hours} hours)</div>
                                </div>
                            `;

                            timeSlotsContainer.appendChild(slotElement);
                        });

                        // Initialize AOS for new elements
                        AOS.refresh();

                        // Attach event listeners to new slots
                        attachSlotEventListeners();
                    } else {
                        timeSlotsContainer.innerHTML = '<div class="alert alert-info">No slots available for this date.</div>';
                    }
                } else {
                    timeSlotsContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load slots'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                timeSlotsContainer.innerHTML = '<div class="alert alert-danger">Error loading available slots. Please try again.</div>';
            });
    }

            // Next dates button click
            nextDates.addEventListener('click', () => {
                dateSelector.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
                setTimeout(updateNavigationButtons, 500);
            });

            // Mouse wheel scroll functionality for horizontal scrolling
    dateSelector.addEventListener('wheel', (e) => {
        // Instead of preventDefault, use a more performant approach
        // Only handle horizontal scroll when the cursor is over the date selector
        if (e.deltaY !== 0) {
            // Use requestAnimationFrame for smoother performance
            requestAnimationFrame(() => {
                const scrollDirection = e.deltaY > 0 ? 1 : -1;
                dateSelector.scrollBy({
                    left: scrollAmount * scrollDirection,
                    behavior: 'smooth'
                });
                setTimeout(updateNavigationButtons, 500);
            });
        }
    }, { passive: true }); // Mark as passive for better performance

            // Mouse events for dragging
            dateSelector.addEventListener('mousedown', (e) => {
                // Only start dragging if clicking on the container, not on a date box
                if (e.target === dateSelector || e.target.parentElement === dateSelector) {
                    isDragging = true;
                    startX = e.pageX - dateSelector.offsetLeft;
                    scrollLeft = dateSelector.scrollLeft;
                    dateSelector.style.cursor = 'grabbing';
                    e.preventDefault();
                }
            });

            dateSelector.addEventListener('mouseleave', () => {
                isDragging = false;
                dateSelector.style.cursor = 'grab';
            });

            dateSelector.addEventListener('mouseup', () => {
                isDragging = false;
                dateSelector.style.cursor = 'grab';
            });

            dateSelector.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - dateSelector.offsetLeft;
                const walk = (x - startX) * 2;
                dateSelector.scrollLeft = scrollLeft - walk;
                updateNavigationButtons();
            });

            // Touch events - using passive event listener for better performance
    dateSelector.addEventListener('touchstart', (e) => {
        // Only start touch tracking if touching the container, not a date box
        if (e.target === dateSelector || e.target.parentElement === dateSelector) {
            touchStartX = e.touches[0].clientX;
            // Don't call preventDefault in a passive listener
        }
    }, { passive: true });

            dateSelector.addEventListener('touchmove', (e) => {
        if (!touchStartX) return;
        // Instead of preventing default, which would block scrolling,
        // we'll just handle our custom scrolling alongside the default behavior
        const touchX = e.touches[0].clientX;
        const diff = touchStartX - touchX;

        // Use requestAnimationFrame for smoother scrolling
        requestAnimationFrame(() => {
            dateSelector.scrollLeft += diff;
            updateNavigationButtons();
        });

        touchStartX = touchX;
    }, { passive: true });

            dateSelector.addEventListener('touchend', () => {
                touchStartX = null;
            });

            // Make selectedSlots accessible globally
            let selectedSlots = [];

            // Function to attach event listeners to time slots
            function attachSlotEventListeners() {
                const timeSlots = document.querySelectorAll('.time-slot');
                selectedSlots = []; // Reset selected slots array when attaching new listeners

                timeSlots.forEach(slot => {
                    if (slot.getAttribute('data-available') === 'true') {
                        slot.addEventListener('click', function() {
                            if (this.classList.contains('booked')) {
                                return; // Can't select booked slots
                            }

                            if (this.classList.contains('selected')) {
                                // Deselect this slot
                                this.classList.remove('selected');
                                this.style.animation = '';
                                // Remove from selected slots array
                                const index = selectedSlots.findIndex(s =>
                                    s.getAttribute('data-time') === this.getAttribute('data-time') &&
                                    s.getAttribute('data-slot-id') === this.getAttribute('data-slot-id')
                                );
                                if (index !== -1) {
                                    selectedSlots.splice(index, 1);
                                }
                            } else {
                                // Select this slot with animation
                                this.classList.add('selected');
                                this.style.animation = 'pulse 1.5s infinite';
                                selectedSlots.push(this);
                            }

                            // Update booking summary
                            updateBookingSummary();
                        });
                    }
                });
            }

            function resetSlotSelection() {
                // Clear the selected class from all slots
                document.querySelectorAll('.time-slot.selected').forEach(slot => {
                    slot.classList.remove('selected');
                    slot.style.animation = '';
                });
                // Empty the array
                selectedSlots = [];
                // Update booking summary
                updateBookingSummary();
            }

            function updateBookingSummary() {
                const bookingSummary = document.querySelector('.booking-summary');

                if (selectedSlots.length > 0) {
                    // Show booking summary with animation
                    bookingSummary.classList.add('active');

                    // Sort slots by start time
                    selectedSlots.sort((a, b) => {
                        const timeA = a.getAttribute('data-time').split('-')[0].trim();
                        const timeB = b.getAttribute('data-time').split('-')[0].trim();
                        return timeA.localeCompare(timeB);
                    });

                    // Calculate start and end times properly
                    let earliestStart = null;
                    let latestEnd = null;
                    let totalDuration = 0;

                    selectedSlots.forEach(slot => {
                        // Get hours directly from data attribute
                        const slotHours = parseFloat(slot.getAttribute('data-hours'));
                        totalDuration += slotHours;

                        // Split the time slot (e.g., "10:00-11:00" or "10:00 - 11:00")
                        const timeRange = slot.getAttribute('data-time');
                        const [startTime, endTime] = timeRange.split('-').map(t => t.trim());

                        // Parse times into Date objects for comparison
                        const today = new Date();
                        const startDate = new Date(today.toDateString() + ' ' + startTime);
                        const endDate = new Date(today.toDateString() + ' ' + endTime);

                        // Handle case where end time is on the next day (e.g., 23:00-01:00)
                        if (endDate < startDate) {
                            endDate.setDate(endDate.getDate() + 1);
                        }

                        // Update earliest start and latest end
                        if (!earliestStart || startDate < earliestStart) {
                            earliestStart = startDate;
                        }

                        if (!latestEnd || endDate > latestEnd) {
                            latestEnd = endDate;
                        }
                    });

                    // Format the times
                    const formatTimeStr = (date) => {
                        const hours = date.getHours().toString().padStart(2, '0');
                        const minutes = date.getMinutes().toString().padStart(2, '0');
                        return `${hours}:${minutes}`;
                    };

                    const startTimeStr = formatTimeStr(earliestStart);
                    const endTimeStr = formatTimeStr(latestEnd);

                    // Format duration as hours and minutes
                    const formatDuration = (hours) => {
                        const wholeHours = Math.floor(hours);
                        const minutes = Math.round((hours - wholeHours) * 60);

                        if (minutes === 0) {
                            return `${wholeHours} hour${wholeHours !== 1 ? 's' : ''}`;
                        } else if (minutes === 60) {
                            return `${wholeHours + 1} hour${wholeHours !== 0 ? 's' : ''}`;
                        } else {
                            return `${wholeHours} hour${wholeHours !== 1 ? 's' : ''} ${minutes} min`;
                        }
                    };

                    const durationStr = formatDuration(totalDuration);

                    // Calculate total price based on each slot's price
                    const totalPrice = selectedSlots.reduce((sum, slot) => {
                        return sum + parseInt(slot.getAttribute('data-price'));
                    }, 0);

                    // Update summary with animation
                    const selectedTimeElement = document.querySelector('.selected-time');
                    const selectedDurationElement = document.querySelector('.selected-duration');
                    const priceAmountElement = document.querySelector('.price-amount');

                    // Apply updates with fade effect
                    const elements = [selectedTimeElement, selectedDurationElement, priceAmountElement];
                    elements.forEach(el => el.style.transition = 'opacity 0.3s ease');
                    elements.forEach(el => el.style.opacity = '0');

                    setTimeout(() => {
                        selectedTimeElement.textContent = `${startTimeStr} to ${endTimeStr}`;
                        selectedDurationElement.textContent = durationStr;
                        priceAmountElement.textContent = totalPrice;

                        elements.forEach(el => el.style.opacity = '1');
                    }, 300);
                } else {
                    // Hide booking summary
                    bookingSummary.classList.remove('active');
                }
            }

            // Initial setup for the time slots
            attachSlotEventListeners();

            // Book now button
            document.querySelector('.book-now-btn').addEventListener('click', function() {
                if (selectedSlots.length === 0) {
                    alert('Please select at least one time slot.');
                    return;
                }

                // Get CSRF token from meta tag
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    alert('Security token missing. Please refresh the page and try again.');
                    return;
                }

                // Prepare booking data
                const bookingData = {
                    ground_id: {{ $ground->id }},
                    date: selectedDate,
                    slot_ids: selectedSlots.map(slot => slot.getAttribute('data-slot-id')),
                    time_slots: selectedSlots.map(slot => slot.getAttribute('data-time')),
                    total_price: document.querySelector('.price-amount').textContent
                };

                // Change button state
                const bookButton = this;
                const originalButtonText = bookButton.innerHTML;
                bookButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`;
                bookButton.disabled = true;
                bookButton.style.opacity = '0.7';

                // Send booking request to server
                fetch('/book-ground', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(bookingData)
                })
                .then(response => {
                    // Check for authentication issues
                    if (response.redirected) {
                        window.location.href = response.url;
                        return null;
                    }

                    if (response.status === 401) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.href);
                        return null;
                    }

                    return response.json();
                })
                .then(data => {
                    if (!data) return; // Handle redirected cases

                    if (data.success) {
                        // Initialize Razorpay payment
                        const options = {
                            key: '{{ Config::get('services.razorpay.key') }}',
                            amount: data.amount,
                            currency: data.currency,
                            name: 'GetBooking',
                            description: 'Ground Booking Payment',
                            order_id: data.order_id,
                            handler: function (response) {
                                // Send payment verification details to server
                                fetch('/payment-callback', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        razorpay_payment_id: response.razorpay_payment_id,
                                        razorpay_order_id: response.razorpay_order_id,
                                        razorpay_signature: response.razorpay_signature
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Show success message
                                        bookButton.innerHTML = `<i class="fas fa-check"></i> Booking Confirmed!`;
                                        bookButton.style.background = '#28a745';
                                        bookButton.style.opacity = '1';

                                        // Create a custom toast notification
                                        const toastDiv = document.createElement('div');
                                        toastDiv.style.position = 'fixed';
                                        toastDiv.style.top = '20px';
                                        toastDiv.style.right = '20px';
                                        toastDiv.style.zIndex = '9999';
                                        toastDiv.style.backgroundColor = '#28a745';
                                        toastDiv.style.color = 'white';
                                        toastDiv.style.padding = '15px 25px';
                                        toastDiv.style.borderRadius = '10px';
                                        toastDiv.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
                                        toastDiv.style.minWidth = '300px';
                                        toastDiv.style.transform = 'translateX(400px)';
                                        toastDiv.style.transition = 'transform 0.3s ease-in-out';

                                        toastDiv.innerHTML = `
                                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                                <div style="display: flex; align-items: center;">
                                                    <i class="fas fa-check-circle" style="margin-right: 10px; font-size: 20px;"></i>
                                                    <strong>Booking Successful</strong>
                                                </div>
                                                <span id="close-toast" style="cursor: pointer; font-size: 20px;">&times;</span>
                                            </div>
                                            <p style="margin: 0;">${data.message || 'Booking confirmed successfully!'}</p>
                                            <p style="margin: 5px 0 0 0;">Booking Reference: <strong>${data.booking_id || 'N/A'}</strong></p>
                                        `;

                                        document.body.appendChild(toastDiv);

                                        // Display the toast with animation
                                        setTimeout(() => {
                                            toastDiv.style.transform = 'translateX(0)';
                                        }, 10);

                                        // Close button functionality
                                        const closeToast = toastDiv.querySelector('#close-toast');
                                        closeToast.addEventListener('click', () => {
                                            toastDiv.style.transform = 'translateX(400px)';
                                            setTimeout(() => {
                                                toastDiv.remove();
                                            }, 300);
                                        });

                                        // Auto-dismiss toast after 5 seconds
                                        setTimeout(() => {
                                            toastDiv.style.transform = 'translateX(400px)';
                                            setTimeout(() => {
                                                toastDiv.remove();
                                            }, 300);
                                        }, 5000);

                                        // Reset booking form
                                        resetSlotSelection();
                                        document.querySelector('.booking-summary').classList.remove('active');

                                        // Fetch updated slot availability without page reload
                                        const activeDate = document.querySelector('.date-box.active');
                                        if (activeDate) {
                                            activeDate.click(); // This will refresh the slots for the current date
                                        }
                                    } else {
                                        // Show error message
                                        bookButton.innerHTML = originalButtonText;
                                        bookButton.disabled = false;
                                        bookButton.style.opacity = '1';
                                        alert(data.message || 'Payment verification failed');
                                    }
                                })
                                .catch(error => {
                                    console.error('Payment verification error:', error);
                                    bookButton.innerHTML = originalButtonText;
                                    bookButton.disabled = false;
                                    bookButton.style.opacity = '1';
                                    alert('Payment verification failed. Please contact support.');
                                });
                            },
                            prefill: {
                                name: '{{ Auth::check() ? Auth::user()->name : "" }}',
                                email: '{{ Auth::check() ? Auth::user()->email : "" }}',
                                contact: '{{ Auth::check() ? Auth::user()->phone : "" }}'
                            },
                            theme: {
                                color: '#3490dc'
                            },
                            modal: {
                                ondismiss: function() {
                                    // Reset button state if payment modal is dismissed
                                    bookButton.innerHTML = originalButtonText;
                                    bookButton.disabled = false;
                                    bookButton.style.opacity = '1';
                                }
                            }
                        };

                        // Open Razorpay payment modal
                        const razorpayPayment = new Razorpay(options);
                        razorpayPayment.open();
                    } else {
                        // Show error message
                        bookButton.innerHTML = originalButtonText;
                        bookButton.disabled = false;
                        bookButton.style.opacity = '1';

                        // Show inline error message
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'alert alert-danger mt-3';
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message || 'Something went wrong. Please try again.'}`;
                        bookButton.parentNode.appendChild(errorDiv);

                        // Remove error message after 5 seconds
                        setTimeout(() => {
                            errorDiv.remove();
                        }, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error creating booking:', error);
                    bookButton.innerHTML = originalButtonText;
                    bookButton.disabled = false;
                    bookButton.style.opacity = '1';

                    // Show error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> Something went wrong. Please try again later.`;
                    bookButton.parentNode.appendChild(errorDiv);

                    // Remove error message after 5 seconds
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);
                });
            });

            // Add additional animations and effects
            const statItems = document.querySelectorAll('.stat-number');

            // Add stat circles styling
            document.querySelectorAll('.stat-circle').forEach(circle => {
                circle.style.width = '60px';
                circle.style.height = '60px';
                circle.style.borderRadius = '50%';
                circle.style.background = 'rgba(52, 144, 220, 0.1)';
                circle.style.display = 'flex';
                circle.style.alignItems = 'center';
                circle.style.justifyContent = 'center';
                circle.style.margin = '0 auto';
                circle.style.transition = 'all 0.3s ease';
                circle.querySelector('i').style.color = '#3490dc';
                circle.querySelector('i').style.fontSize = '1.5rem';

                circle.addEventListener('mouseenter', () => {
                    circle.style.transform = 'scale(1.1)';
                    circle.style.background = 'rgba(52, 144, 220, 0.2)';
                });

                circle.addEventListener('mouseleave', () => {
                    circle.style.transform = 'scale(1)';
                    circle.style.background = 'rgba(52, 144, 220, 0.1)';
                });
            });
        });
    </script>

<!-- Add simple JavaScript for the review functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Check if user is authenticated
        const isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};

        // Track current state of reviews
        let allReviews = [];
        let currentPage = 1;
        const reviewsPerPage = 3;
        const moreReviewsPerClick = 2;

        // Helper function to fetch and update reviews without page reload
        function fetchUpdatedReviews() {
            // Show loading state in reviews list
            const reviewsContainer = document.getElementById('reviews-container');
            const viewMoreContainer = document.getElementById('view-more-container');
            const reviewsList = document.querySelector('.reviews-list');

            if (reviewsContainer) {
                reviewsContainer.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading reviews...</div>';
            }

            if (viewMoreContainer) {
                viewMoreContainer.style.display = 'none';
            }

            // Reset current page
            currentPage = 1;

            // Fetch updated reviews
            fetch(`/get-ground-reviews/{{ $ground->id }}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && reviewsList) {
                    // Store all reviews
                    allReviews = data.reviews;

                                        // Update reviews count
                    const reviewCount = allReviews.length;
                    const reviewsCountElement = document.querySelector('.reviews-count');

                    if (reviewsCountElement) {
                        reviewsCountElement.innerHTML = `
                            <span class="review-count-number">${reviewCount}</span>
                            <span class="review-count-text">${reviewCount == 1 ? 'Review' : 'Reviews'}</span>
                        `;
                    }

                    // Update total reviews count in the view more section
                    document.getElementById('total-reviews-count').textContent = reviewCount;

                    // Add reviews or no reviews message
                    if (reviewCount > 0) {
                        // Clear container
                        reviewsContainer.innerHTML = '';

                                                // Show initial batch of reviews
                        displayReviews(allReviews.slice(0, reviewsPerPage));

                        // Update reviews shown count
                        document.getElementById('reviews-shown-count').textContent = Math.min(reviewsPerPage, reviewCount);

                        // Show view more button if there are more reviews
                        if (reviewCount > reviewsPerPage) {
                            viewMoreContainer.style.display = 'block';
                        } else {
                            viewMoreContainer.style.display = 'none';
                        }

                        // Calculate average rating
                        const totalRating = allReviews.reduce((sum, review) => sum + review.rating, 0);
                        const avgRating = reviewCount > 0 ? (totalRating / reviewCount).toFixed(1) : 0;

                        // Update the rating in the ground meta section
                        const ratingMetaItem = document.querySelector('.meta-item.fade-in-delay-2 span');
                        if (ratingMetaItem) {
                            ratingMetaItem.textContent = `${avgRating} (${reviewCount} ${reviewCount === 1 ? 'review' : 'reviews'})`;
                        }
                    } else {
                        reviewsContainer.innerHTML = '<p class="text-center text-muted">No reviews yet. Be the first to review!</p>';
                        viewMoreContainer.style.display = 'none';

                        // Update the rating in the ground meta section to show 0
                        const ratingMetaItem = document.querySelector('.meta-item.fade-in-delay-2 span');
                        if (ratingMetaItem) {
                            ratingMetaItem.textContent = `0 (0 reviews)`;
                        }
                    }

                    // If there's a review form, update it based on current user's review status
                    if (data.userReview) {
                        updateReviewForm(data.userReview);
                    } else {
                        resetReviewForm();
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching reviews:', error);
                if (reviewsContainer) {
                    reviewsContainer.innerHTML = '<div class="alert alert-danger">Error loading reviews. Please refresh the page.</div>';
                }
            });
        }

                // Function to display a batch of reviews
        function displayReviews(reviews) {
            const reviewsContainer = document.getElementById('reviews-container');

            if (!reviewsContainer) return;

            reviews.forEach((review, index) => {
                const reviewElement = createReviewElement(review);

                // Add fade-in animation class with staggered delay
                reviewElement.style.opacity = '0';
                reviewElement.style.transform = 'translateY(20px)';
                reviewElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

                reviewsContainer.appendChild(reviewElement);

                // Trigger animation after a slight delay (staggered effect)
                setTimeout(() => {
                    reviewElement.style.opacity = '1';
                    reviewElement.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        }

                // Handle view more button click
        document.getElementById('view-more-btn')?.addEventListener('click', function() {
            // Calculate which reviews to show next
            const startIndex = currentPage * reviewsPerPage;
            const endIndex = startIndex + moreReviewsPerClick;
            const nextReviews = allReviews.slice(startIndex, endIndex);

            // Display the next batch of reviews
            displayReviews(nextReviews);

            // Update current page
            currentPage++;

            // Update reviews shown count
            const reviewsShownCount = Math.min(currentPage * reviewsPerPage + (currentPage - 1) * (moreReviewsPerClick - reviewsPerPage), allReviews.length);
            document.getElementById('reviews-shown-count').textContent = reviewsShownCount;

            // Hide view more button if no more reviews
            if (endIndex >= allReviews.length) {
                document.getElementById('view-more-container').style.display = 'none';
            }

            // Show loading animation on button
            const viewMoreBtn = document.getElementById('view-more-btn');
            const originalText = viewMoreBtn.innerHTML;

            viewMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

            // Restore button text after a short delay (for better UX)
            setTimeout(() => {
                viewMoreBtn.innerHTML = originalText;
            }, 500);

            // Scroll to the newly loaded reviews
            const lastReviewBeforeLoad = document.querySelectorAll('.review-item')[startIndex - 1];
            if (lastReviewBeforeLoad) {
                setTimeout(() => {
                    lastReviewBeforeLoad.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            }
        });

        // Helper function to update the review form for editing
        function updateReviewForm(userReview) {
            const form = document.getElementById('reviewForm');
            if (!form) return;

            // Set the review ID for editing
            let reviewIdInput = document.getElementById('review_id');
            if (!reviewIdInput) {
                reviewIdInput = document.createElement('input');
                reviewIdInput.type = 'hidden';
                reviewIdInput.id = 'review_id';
                form.appendChild(reviewIdInput);
            }
            reviewIdInput.value = userReview.id;

            // Update rating display
            document.getElementById('rating_input').value = userReview.rating;
            const ratingStars = document.querySelectorAll('.rating-stars i');
            ratingStars.forEach((star, index) => {
                if (index < userReview.rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });

            // Update rating meaning
            const ratingMeaning = document.querySelector('.rating-meaning');
            if (ratingMeaning) {
                const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
                ratingMeaning.textContent = ratingTexts[userReview.rating];
            }

            // Update comment textarea
            const commentTextarea = form.querySelector('textarea[name="comment"]');
            if (commentTextarea) {
                commentTextarea.value = userReview.comment;
            }

            // Update submit button text
            const submitButton = document.getElementById('submitReview');
            if (submitButton) {
                submitButton.textContent = 'Update Review';
            }

            // Show delete button if it exists
            const deleteButton = document.getElementById('deleteReview');
            if (deleteButton) {
                deleteButton.style.display = 'inline-block';
            }
        }

        // Helper function to reset the review form for new review
        function resetReviewForm() {
            const form = document.getElementById('reviewForm');
            if (!form) return;

            // Remove review ID input
            const reviewIdInput = document.getElementById('review_id');
            if (reviewIdInput) {
                reviewIdInput.remove();
            }

            // Reset rating display
            document.getElementById('rating_input').value = '';
            const ratingStars = document.querySelectorAll('.rating-stars i');
            ratingStars.forEach(star => {
                star.classList.remove('fas');
                star.classList.add('far');
            });

            // Reset rating meaning
            const ratingMeaning = document.querySelector('.rating-meaning');
            if (ratingMeaning) {
                ratingMeaning.textContent = 'Select stars to rate';
            }

            // Clear comment textarea
            const commentTextarea = form.querySelector('textarea[name="comment"]');
            if (commentTextarea) {
                commentTextarea.value = '';
            }

            // Update submit button text
            const submitButton = document.getElementById('submitReview');
            if (submitButton) {
                submitButton.textContent = 'Submit Review';
            }

            // Hide delete button if it exists
            const deleteButton = document.getElementById('deleteReview');
            if (deleteButton) {
                deleteButton.style.display = 'none';
            }
        }

        // Helper function to create review element
        function createReviewElement(review) {
            const reviewItem = document.createElement('div');
            reviewItem.className = 'review-item';

            // Generate star HTML
            let starsHtml = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= review.rating) {
                    starsHtml += '<i class="fas fa-star"></i>';
                } else {
                    starsHtml += '<i class="far fa-star"></i>';
                }
            }

            // Format date
            const reviewDate = new Date(review.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            // Determine rating text
            const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
            const ratingText = ratingTexts[review.rating] || '';

            reviewItem.innerHTML = `
                <div class="reviewer-info">
                    <div class="reviewer-name">
                        <i class="fas fa-user-circle mr-2"></i>
                        ${review.user ? review.user.name : 'Anonymous'}
                    </div>
                    <div class="review-date">
                        <i class="far fa-calendar-alt mr-1"></i>
                        ${reviewDate}
                    </div>
                </div>
                <div class="review-stars-display">
                    ${starsHtml}
                    <span>${ratingText}</span>
                </div>
                <div class="review-text">
                    <i class="fas fa-quote-left text-muted mr-2" style="opacity: 0.3; font-size: 0.9rem;"></i>
                    ${review.comment}
                    <i class="fas fa-quote-right text-muted ml-2" style="opacity: 0.3; font-size: 0.9rem;"></i>
                </div>
                ${review.replies && review.replies.length > 0 ? `
                    <div class="review-replies mt-3">
                        <h6 class="mb-2"><i class="fas fa-reply mr-1"></i> Replies</h6>
                        <div class="replies-container">
                            ${review.replies.map(reply => `
                                <div class="reply-item">
                                    <div class="reply-header">
                                        <div class="replier-info">
                                            <i class="fas fa-user-circle mr-1"></i>
                                            <span class="replier-name">${reply.user ? reply.user.name : 'Anonymous'}</span>
                                            ${reply.user && reply.user.is_admin ? '<span class="replier-badge">Admin</span>' :
                                              reply.user && reply.user.is_ground_owner ? '<span class="replier-badge">Ground Owner</span>' : ''}
                                        </div>
                                        <div class="reply-date">
                                            ${new Date(reply.created_at).toLocaleDateString('en-US', {
                                                year: 'numeric',
                                                month: 'short',
                                                day: 'numeric'
                                            })}
                                        </div>
                                    </div>
                                    <div class="reply-text">
                                        ${reply.comment}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                ${isAuthenticated ? `
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary reply-btn" data-review-id="${review.id}">
                            <i class="fas fa-reply mr-1"></i> Reply
                        </button>
                    </div>
                ` : ''}
            `;

            // Add event listener for reply button if authenticated
            if (isAuthenticated) {
                const replyBtn = reviewItem.querySelector('.reply-btn');
                if (replyBtn) {
                    replyBtn.addEventListener('click', function() {
                        showReplyModal(review.id);
                    });
                }
            }

            return reviewItem;
        }

        // Helper function to show toast messages
        function showToast(type, message) {
            // Remove any existing toasts
            const existingToasts = document.querySelectorAll('.custom-toast');
            existingToasts.forEach(toast => toast.remove());

            // Create toast element
            const toast = document.createElement('div');
            toast.className = `custom-toast ${type}`;
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.padding = '15px 25px';
            toast.style.borderRadius = '8px';
            toast.style.minWidth = '300px';
            toast.style.zIndex = '9999';
            toast.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
            toast.style.transform = 'translateX(400px)';
            toast.style.transition = 'transform 0.3s ease';

            // Set toast color based on type
            if (type === 'success') {
                toast.style.backgroundColor = '#28a745';
                toast.style.color = 'white';
            } else if (type === 'error') {
                toast.style.backgroundColor = '#dc3545';
                toast.style.color = 'white';
            } else {
                toast.style.backgroundColor = '#17a2b8';
                toast.style.color = 'white';
            }

            // Create toast content
            toast.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center;">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="margin-right: 10px;"></i>
                        <span>${message}</span>
                    </div>
                    <span style="cursor: pointer; margin-left: 15px;" onclick="this.parentNode.parentNode.remove();">×</span>
                </div>
            `;

            // Add toast to the document
            document.body.appendChild(toast);

            // Show toast with animation
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);

            // Auto-hide toast after 5 seconds
            setTimeout(() => {
                toast.style.transform = 'translateX(400px)';
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }

        // Setup star rating functionality
        const ratingStars = document.querySelectorAll('.rating-stars i');
        const ratingInput = document.getElementById('rating_input');
        const ratingMeaning = document.querySelector('.rating-meaning');
        const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

        // Initialize selected rating
        let selectedRating = ratingInput ? parseInt(ratingInput.value) || 0 : 0;

        // Add event listeners to stars
        ratingStars.forEach(star => {
            // Handle mouseover
            star.addEventListener('mouseover', function() {
                const rating = parseInt(this.getAttribute('data-rating'));

                // Update stars visual
                ratingStars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });

                // Update rating meaning
                if (ratingMeaning) {
                    ratingMeaning.textContent = ratingTexts[rating];
                }
            });

            // Handle mouseout
            star.addEventListener('mouseout', function() {
                // Restore selected rating
                ratingStars.forEach((s, index) => {
                    if (index < selectedRating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });

                // Restore rating meaning
                if (ratingMeaning) {
                    ratingMeaning.textContent = selectedRating > 0 ? ratingTexts[selectedRating] : 'Select stars to rate';
                }
            });

            // Handle click
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.getAttribute('data-rating'));

                // Update hidden input
                if (ratingInput) {
                    ratingInput.value = selectedRating;
                }

                // Update stars visual
                ratingStars.forEach((s, index) => {
                    if (index < selectedRating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });

                // Update rating meaning
                if (ratingMeaning) {
                    ratingMeaning.textContent = ratingTexts[selectedRating];
                }
            });
        });

        // Handle review submit
        const submitReviewButton = document.getElementById('submitReview');
        if (submitReviewButton) {
            submitReviewButton.addEventListener('click', function() {
                const form = document.getElementById('reviewForm');
                const formData = new FormData(form);
                const reviewId = document.getElementById('review_id')?.value;

                // Validate form
                if (!formData.get('rating')) {
                    showToast('error', 'Please select a rating');
                    return;
                }

                if (!formData.get('comment').trim()) {
                    showToast('error', 'Please enter your review');
                    return;
                }

                // Determine endpoint (create or update)
                const endpoint = reviewId ? `/update-review/${reviewId}` : '/store-review';
                const method = reviewId ? 'PUT' : 'POST';

                // Show loading state
                submitReviewButton.textContent = 'Submitting...';
                submitReviewButton.disabled = true;

                // Submit review
                fetch(endpoint, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Success! Fetch updated reviews without page reload
                        fetchUpdatedReviews();

                        // Show success toast
                        showToast('success', reviewId ? 'Review updated successfully!' : 'Review submitted successfully!');

                        if (!reviewId) {
                            // For new review: Reset form
                            form.querySelector('textarea').value = '';
                            selectedRating = 0;

                            // Reset stars display
                            ratingStars.forEach((s) => {
                                s.classList.remove('fas');
                                s.classList.add('far');
                            });

                            if (ratingMeaning) {
                                ratingMeaning.textContent = 'Select stars to rate';
                            }

                            if (data.review && data.review.id) {
                                // Add the review ID to the form for future edits
                                let reviewIdInput = document.getElementById('review_id');
                                if (!reviewIdInput) {
                                    reviewIdInput = document.createElement('input');
                                    reviewIdInput.type = 'hidden';
                                    reviewIdInput.id = 'review_id';
                                    form.appendChild(reviewIdInput);
                                }
                                reviewIdInput.value = data.review.id;

                                // Change the submit button text
                                submitReviewButton.textContent = 'Update Review';

                                // Show delete button if not already visible
                                let deleteButton = document.getElementById('deleteReview');
                                if (deleteButton) {
                                    deleteButton.style.display = 'inline-block';
                                }
                            }
                        }

                        // Reset button state
                        submitReviewButton.disabled = false;
                        submitReviewButton.textContent = reviewId ? 'Update Review' : 'Submit Review';
                    } else {
                        showToast('error', data.message || 'Error submitting review');
                        submitReviewButton.textContent = reviewId ? 'Update Review' : 'Submit Review';
                        submitReviewButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('error', 'An error occurred. Please try again.');
                    submitReviewButton.textContent = reviewId ? 'Update Review' : 'Submit Review';
                    submitReviewButton.disabled = false;
                });
            });
        }

        // Handle review delete
        const deleteReviewButton = document.getElementById('deleteReview');
        if (deleteReviewButton) {
            deleteReviewButton.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete your review?')) {
                    const reviewId = document.getElementById('review_id').value;
                    const form = document.getElementById('reviewForm');

                    // Show loading state
                    deleteReviewButton.textContent = 'Deleting...';
                    deleteReviewButton.disabled = true;

                    // Delete review
                    fetch(`/delete-review/${reviewId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Success! Fetch updated reviews without page reload
                            fetchUpdatedReviews();

                            // Show success toast
                            showToast('success', 'Review deleted successfully!');

                            // Reset the form to create new review mode
                            form.querySelector('textarea').value = '';
                            selectedRating = 0;

                            // Reset stars display
                            ratingStars.forEach((s) => {
                                s.classList.remove('fas');
                                s.classList.add('far');
                            });

                            if (ratingMeaning) {
                                ratingMeaning.textContent = 'Select stars to rate';
                            }

                            // Remove review_id input
                            const reviewIdInput = document.getElementById('review_id');
                            if (reviewIdInput) {
                                reviewIdInput.remove();
                            }

                            // Change submit button back to Submit Review
                            const submitReviewButton = document.getElementById('submitReview');
                            if (submitReviewButton) {
                                submitReviewButton.textContent = 'Submit Review';
                            }

                            deleteReviewButton.textContent = 'Delete';
                            deleteReviewButton.disabled = false;

                            // Hide delete button
                            deleteReviewButton.style.display = 'none';
                        } else {
                            showToast('error', data.message || 'Error deleting review');
                            deleteReviewButton.textContent = 'Delete';
                            deleteReviewButton.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('error', 'An error occurred. Please try again.');
                        deleteReviewButton.textContent = 'Delete';
                        deleteReviewButton.disabled = false;
                    });
                }
            });
        }

        // Function to show reply modal
        function showReplyModal(reviewId) {
            // Check if modal already exists
            let replyModal = document.getElementById('replyModal');
            if (!replyModal) {
                // Create modal
                replyModal = document.createElement('div');
                replyModal.className = 'modal fade custom-modal';
                replyModal.id = 'replyModal';
                replyModal.setAttribute('tabindex', '-1');
                replyModal.setAttribute('role', 'dialog');
                replyModal.setAttribute('aria-labelledby', 'replyModalLabel');
                replyModal.setAttribute('aria-hidden', 'true');

                replyModal.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-gradient-primary text-white">
                                <h5 class="modal-title" id="replyModalLabel">
                                    <i class="fas fa-reply mr-2"></i> Reply to Review
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="replyForm">
                                    <input type="hidden" name="review_id" id="reply_review_id">
                                    <div class="form-group">
                                        <label for="replyComment">Your Reply</label>
                                        <textarea class="form-control" id="replyComment" name="comment" rows="4" placeholder="Write your reply here..."></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="submitReply">
                                    <i class="fas fa-paper-plane mr-1"></i> Submit Reply
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                document.body.appendChild(replyModal);

                // Initialize Bootstrap modal
                $(replyModal).modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: false
                });

                // Add event listener for submit button
                document.getElementById('submitReply').addEventListener('click', function() {
                    submitReply();
                });
            }

            // Set review ID
            document.getElementById('reply_review_id').value = reviewId;

            // Show modal
            $(replyModal).modal('show');
        }

        // Function to submit reply
        function submitReply() {
            const replyForm = document.getElementById('replyForm');
            const formData = new FormData(replyForm);
            const submitButton = document.getElementById('submitReply');

            // Validate form
            if (!formData.get('comment').trim()) {
                showToast('error', 'Please enter your reply');
                return;
            }

            // Show loading state
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...';
            submitButton.disabled = true;

            // Submit reply
            fetch('/store-reply', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Success! Fetch updated reviews without page reload
                    fetchUpdatedReviews();

                    // Show success toast
                    showToast('success', 'Reply submitted successfully!');

                    // Close modal
                    $('#replyModal').modal('hide');

                    // Reset form
                    replyForm.reset();
                } else {
                    showToast('error', data.message || 'Error submitting reply');
                }

                // Reset button state
                submitButton.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Submit Reply';
                submitButton.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'An error occurred. Please try again.');

                // Reset button state
                submitButton.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Submit Reply';
                submitButton.disabled = false;
            });
        }

        // Initialize by fetching reviews when page loads
        fetchUpdatedReviews();
    });
</script>
@endsection

