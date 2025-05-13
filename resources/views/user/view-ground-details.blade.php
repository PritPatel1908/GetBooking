@extends('layouts.user')

@section('styles')
{{-- <link rel="stylesheet" href="{{ asset('assets/user/css/all-grounds.css') }}"> --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
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
    }

    .ground-name:after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 3px;
        background: var(--primary-color);
        transform-origin: left;
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
    }

    .meta-item:hover {
        transform: translateY(-3px);
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
    }

    /* Enhanced amenities section */
    .amenity-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        padding: 8px;
        border-radius: 6px;
        transition: all 0.3s ease;
        color: var(--text-color);
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
        background: rgba(56, 193, 114, 0.1);
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
        border-radius: 8px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        color: var(--text-color);
        background: var(--card-bg);
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

    /* Enhanced reviews section */
    .reviews-section {
        margin-top: 3rem;
    }

    .review-item {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        transform: translateY(0);
    }

    .review-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-hover-shadow);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
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

    .review-stars i {
        transition: all 0.3s ease;
    }

    .review-item:hover .review-stars i {
        transform: rotate(360deg);
    }

    .review-text {
        color: var(--text-color);
        line-height: 1.6;
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
                    <div class="meta-item fade-in-delay-2">
                        <i class="fas fa-star"></i>
                        <span>4.5 (150 reviews)</span>
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
                <div class="date-selector">
                    @for($i = 0; $i < 7; $i++)
                        @php
                        $date = \Carbon\Carbon::now()->addDays($i);
                        $isActive = $i === 0;
                        @endphp
                        <div class="date-box {{ $isActive ? 'active' : '' }}" data-date="{{ $date->format('Y-m-d') }}">
                            <div class="day">{{ $date->format('D') }}</div>
                            <div class="date">{{ $date->format('d M') }}</div>
                        </div>
                    @endfor
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

                    // Get booked slots for the selected date
                    $bookedSlotIds = \App\Models\Booking::where('ground_id', $ground->id)
                        ->where('booking_date', $selectedDate)
                        ->where('booking_status', '!=', 'cancelled')
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

            <!-- Reviews Section -->
            <div class="reviews-section" data-aos="fade-up">
                <h3 class="mb-4">Reviews (150)</h3>

                <!-- For demo purposes, show sample reviews -->
                <div class="review-item" data-aos="fade-up">
                    <div class="review-header">
                        <div class="reviewer-name">Prit Patel</div>
                        <div class="review-date">{{ \Carbon\Carbon::parse('2025-05-03')->format('M d, Y') }}</div>
                    </div>
                    <div class="review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= 4.5)
                                <i class="fas fa-star"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                    <div class="review-text">Very flexible ground with excellent facilities and staff support.</div>
                </div>
                <div class="review-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="review-header">
                        <div class="reviewer-name">Raj Sharma</div>
                        <div class="review-date">{{ \Carbon\Carbon::parse('2025-04-28')->format('M d, Y') }}</div>
                    </div>
                    <div class="review-stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= 5)
                                <i class="fas fa-star"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                    <div class="review-text">Best ground in the city! Always clean and well-maintained.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
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
                        <h5 class="stat-number">{{ $ground->bookings->count() }}</h5>
                        <div class="stat-label">Bookings</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-circle mb-2">
                            <i class="fas fa-star"></i>
                        </div>
                        <h5 class="stat-number">4.5</h5>
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
        </div>
    </div>
</div>

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
        const dateBoxes = document.querySelectorAll('.date-box');
        let selectedDate = dateBoxes[0].getAttribute('data-date');
        let selectedSlots = []; // Initialize the array for selected slots

        dateBoxes.forEach(dateBox => {
            dateBox.addEventListener('click', () => {
                document.querySelector('.date-box.active').classList.remove('active');
                dateBox.classList.add('active');
                selectedDate = dateBox.getAttribute('data-date');

                // Update selected date in summary
                const formattedDate = new Date(selectedDate).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                document.querySelector('.selected-date').textContent = formattedDate;

                // Reset slot selection
                resetSlotSelection();

                // Show loading state for slots
                const timeSlotsContainer = document.querySelector('.time-slots');
                timeSlotsContainer.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading available slots...</div>';

                // Fetch slots for the selected date via AJAX
                fetch(`/get-ground-slots/${selectedDate}/{{ $ground->id }}`)
                .then(response => {
                    // Check for authentication issues
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    if (response.status === 401) {
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.href);
                        return;
                    }

                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Network response was not ok: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Clear the container
                        timeSlotsContainer.innerHTML = '';

                        if (data.slots.length === 0) {
                            timeSlotsContainer.innerHTML = '<div class="text-center p-4">No slots available for this date</div>';
                            return;
                        }

                        // Add slots to the container
                        data.slots.forEach((slot, index) => {
                            const slotHtml = `
                                <div class="time-slot-container" data-aos="zoom-in" data-aos-delay="${100 + (index * 50)}">
                                    <div class="time-slot ${slot.available ? 'available' : 'booked'}"
                                        data-time="${slot.time}"
                                        data-slot-id="${slot.id}"
                                        data-price="${slot.price}"
                                        data-hours="${slot.hours}"
                                        data-available="${slot.available ? 'true' : 'false'}">
                                        <div>${slot.time.replace('-', ' - ')}</div>
                                        <div>₹${slot.price} (${slot.hours} hours)</div>
                                    </div>
                                </div>
                            `;
                            timeSlotsContainer.innerHTML += slotHtml;
                        });

                        // Re-attach event listeners to the new slots
                        attachSlotEventListeners();
                    } else {
                        timeSlotsContainer.innerHTML = `<div class="text-center p-4">Error: ${data.message || 'Could not load slots'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching slots:', error);
                    timeSlotsContainer.innerHTML = '<div class="text-center p-4 text-danger">Error loading slots. Please try again.<br><small>Check browser console for details.</small></div>';
                });
            });
        });

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

        // Initial setup for the time slots
        attachSlotEventListeners();

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
                    // Show success message
                    bookButton.innerHTML = `<i class="fas fa-check"></i> Booking Confirmed!`;
                    bookButton.style.background = '#28a745';
                    bookButton.style.opacity = '1';

                    // Show a success message
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'alert alert-success mt-3';
                    messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message || 'Booking confirmed successfully!'}`;
                    bookButton.parentNode.appendChild(messageDiv);

                    // Redirect to booking confirmation or payment page
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1500);
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
@endsection
