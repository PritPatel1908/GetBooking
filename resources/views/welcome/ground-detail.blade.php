@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 ground-detail">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Ground Images -->
        <div class="relative w-full">
            <div class="swiper-container h-full w-full">
                <div class="swiper-wrapper">
                    @foreach($ground->images as $image)
                    <div class="swiper-slide">
                        <img src="{{ $image->image_url }}" alt="{{ $ground->name }}" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>

        <div class="p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $ground->name }}</h1>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <span>{{ $ground->location }}</span>
                    </div>
                </div>
                <div class="text-2xl font-bold text-emerald-600">₹{{ number_format($ground->price_per_hour, 2) }}/hr</div>
            </div>

            <!-- Features -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Features</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($ground->features as $feature)
                    <div class="flex items-center">
                        <i class="fas fa-check text-emerald-500 mr-2"></i>
                        <span>{{ $feature->feature_name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Description -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Description</h2>
                <p class="text-gray-600">{{ $ground->description }}</p>
            </div>

            <!-- Rules -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Rules</h2>
                <p class="text-gray-600">{{ $ground->rules }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Swiper
    new Swiper('.swiper-container', {
        loop: true,
        pagination: {
            el: '.swiper-pagination',
            clickable: true
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        }
    });
});
</script>
@endpush
@endsection
