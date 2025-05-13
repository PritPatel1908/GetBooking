@extends('layouts.user')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/user/css/all-grounds.css') }}">
@endsection

@section('content')
<!-- All Sports Grounds Section -->
<section class="page-header">
    <div class="container">
        <h1>All Sports Grounds</h1>
        <p>Browse all available sports grounds and facilities for your next game or event</p>
    </div>
</section>

<section id="sports-grounds" class="all-grounds">
    <div class="container">
        <div class="filter-section">
            <div class="search-filter">
                <input type="text" placeholder="Search grounds..." class="form-control">
            </div>
            <div class="category-filter">
                <select class="form-control">
                    <option value="">All Sports</option>
                    <option value="football">Football</option>
                    <option value="basketball">Basketball</option>
                    <option value="tennis">Tennis</option>
                    <option value="cricket">Cricket</option>
                    <option value="volleyball">Volleyball</option>
                </select>
            </div>
            <div class="price-filter">
                <select class="form-control">
                    <option value="">Price Range</option>
                    <option value="0-25">$0 - $25/hr</option>
                    <option value="25-50">$25 - $50/hr</option>
                    <option value="50-100">$50 - $100/hr</option>
                    <option value="100+">$100+/hr</option>
                </select>
            </div>
            <button class="btn filter-btn">Filter</button>
        </div>

        <div class="cards-container">
            <!-- Ground Card 1 -->
            <div class="card">
                <div class="card-badge">Featured</div>
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/football-arena.jpg') }}" alt="City Football Arena">
                </div>
                <div class="card-content">
                    <h3 class="card-title">City Football Arena</h3>
                    <p class="card-text">Professional football pitch with floodlights, spectator seating and modern
                        amenities for the perfect game experience.</p>
                    <a href="{{ route('user.view_ground', 1) }}" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.8 (120 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $50/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 2 -->
            <div class="card">
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/basketball-court.jpg') }}"
                        alt="Downtown Basketball Court">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Downtown Basketball Court</h3>
                    <p class="card-text">Indoor basketball court with professional flooring, equipment and full climate
                        control for year-round play.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.6 (85 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $40/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 3 -->
            <div class="card">
                <div class="card-badge">New</div>
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/tennis-club.jpg') }}" alt="Greenview Tennis Club">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Greenview Tennis Club</h3>
                    <p class="card-text">Professional tennis courts with clay and hard court options, club house
                        amenities and coaching available.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.9 (200 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $35/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 4 -->
            <div class="card">
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/cricket-ground.jpg') }}"
                        alt="Central Cricket Ground">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Central Cricket Ground</h3>
                    <p class="card-text">Well-maintained cricket pitch with practice nets, changing rooms and a pavilion
                        for spectators.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.5 (95 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $45/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 5 -->
            <div class="card">
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/volleyball-court.jpg') }}"
                        alt="Beach Volleyball Court">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Beach Volleyball Court</h3>
                    <p class="card-text">Outdoor sand volleyball courts with professional nets, boundaries and seating
                        areas.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.7 (110 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $30/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 6 -->
            <div class="card">
                <div class="card-badge">Popular</div>
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/indoor-football.jpg') }}"
                        alt="Indoor 5-a-side Football">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Indoor 5-a-side Football</h3>
                    <p class="card-text">State-of-the-art indoor football facilities with artificial turf and full
                        amenities.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.9 (180 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $55/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 7 -->
            <div class="card">
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/multi-purpose.jpg') }}"
                        alt="Multi-purpose Sports Hall">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Multi-purpose Sports Hall</h3>
                    <p class="card-text">Versatile indoor facility suitable for basketball, volleyball, badminton and
                        other indoor sports.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.4 (75 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $60/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 8 -->
            <div class="card">
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/badminton-courts.jpg') }}"
                        alt="Elite Badminton Courts">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Elite Badminton Courts</h3>
                    <p class="card-text">Professional badminton courts with proper lighting, flooring and equipment
                        available for hire.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.7 (130 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $25/hr</span>
                    </div>
                </div>
            </div>

            <!-- Ground Card 9 -->
            <div class="card">
                <div class="card-badge">New</div>
                <div class="card-image">
                    <img src="{{ asset('assets/user/images/grounds/hockey-pitch.jpg') }}" alt="Community Hockey Pitch">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Community Hockey Pitch</h3>
                    <p class="card-text">Newly renovated artificial hockey pitch with floodlights and changing
                        facilities.</p>
                    <a href="" class="btn card-btn"><i class="fas fa-bookmark"></i> Book
                        Now</a>
                    <div class="card-meta">
                        <span><i class="fas fa-star"></i> 4.5 (45 reviews)</span>
                        <span><i class="fas fa-dollar-sign"></i> $65/hr</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pagination-container">
            <ul class="pagination">
                <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
            </ul>
        </div>
    </div>
</section>
@endsection
