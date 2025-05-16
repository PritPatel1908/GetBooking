@extends('layouts.user')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/user/css/all-grounds.css') }}">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }

        .container {
            position: relative;
        }

        .no-grounds-message, .no-results {
            display: none;
        }

        /* Filter Button Styles */
        .filter-button-container {
            margin-bottom: 25px;
            text-align: left;
        }

        .show-filters-btn {
            background: #222;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .show-filters-btn .filter-icon {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .show-filters-btn .filter-icon i {
            font-size: 14px;
        }

        .show-filters-btn .filter-text {
            letter-spacing: 0.5px;
        }

        .show-filters-btn:hover {
            background: #333;
            transform: translateY(-2px);
        }

        .show-filters-btn:hover .filter-icon {
            transform: rotate(90deg);
        }

        .show-filters-btn:active {
            transform: translateY(1px);
        }

        /* Dropdown Filter Styles */
        .filter-dropdown {
            position: absolute;
            z-index: 1000;
            left: 0;
            top: 60px;
            width: 100%;
            max-width: 400px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .filter-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .filter-dropdown-content {
            background: #222;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            overflow: hidden;
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #1c1c1c;
            border-bottom: 1px solid #333;
        }

        .filter-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 500;
            color: #e0e0e0;
        }

        .close-filters-btn {
            background: transparent;
            border: none;
            color: #999;
            font-size: 16px;
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s;
        }

        .close-filters-btn:hover {
            color: #fff;
        }

        .filter-body {
            padding: 20px;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group:last-child {
            margin-bottom: 0;
        }

        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #aaa;
        }

        .search-filter, .category-filter {
            position: relative;
        }

        .search-filter input {
            width: 100%;
            padding: 12px 40px 12px 40px;
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 10px;
            color: #e0e0e0;
            font-size: 15px;
            transition: all 0.3s;
        }

        .search-filter input:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .search-filter .search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #7c3aed;
            font-size: 15px;
        }

        .search-filter .search-clear {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            cursor: pointer;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            display: none;
        }

        .search-filter .search-clear:hover {
            color: #fff;
        }

        .category-filter select {
            width: 100%;
            padding: 12px 40px 12px 15px;
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 10px;
            color: #e0e0e0;
            font-size: 15px;
            transition: all 0.3s;
            appearance: none;
            cursor: pointer;
        }

        .category-filter select:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .category-filter .select-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7c3aed;
            font-size: 12px;
            pointer-events: none;
        }

        .filter-footer {
            display: flex;
            gap: 10px;
            padding: 15px 20px;
            background: #1c1c1c;
            border-top: 1px solid #333;
        }

        .apply-filters-btn {
            flex: 1;
            padding: 10px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .apply-filters-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            transform: translateY(-1px);
        }

        .reset-all-btn {
            padding: 10px 15px;
            background: transparent;
            color: #999;
            border: 1px solid #444;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .reset-all-btn:hover {
            background: #333;
            color: #fff;
        }

        /* Search Animation */
        .search-active {
            animation: pulse 0.5s ease-in-out;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.4); }
            50% { box-shadow: 0 0 0 5px rgba(124, 58, 237, 0.2); }
            100% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0); }
        }

        /* Applied filters */
        .applied-filters {
            margin: 20px 0 25px;
            padding: 15px 20px;
            background: #1c1c1c;
            border-radius: 12px;
            border: 1px solid #333;
        }

        .applied-filters-title {
            font-weight: 500;
            font-size: 14px;
            color: #aaa;
            margin-bottom: 12px;
        }

        .filter-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #2d2d2d;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            color: #e0e0e0;
            border: 1px solid #444;
        }

        .filter-tag i.fa-times {
            margin-left: 5px;
            cursor: pointer;
            color: #999;
            transition: color 0.2s;
            font-size: 12px;
        }

        .filter-tag i.fa-times:hover {
            color: #fff;
        }

        .clear-all-btn {
            background: #2d2d2d;
            color: #e0e0e0;
            border: 1px solid #444;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .clear-all-btn:hover {
            background: #444;
        }

        /* No grounds message */
        .no-grounds-found {
            text-align: center;
            padding: 50px 30px;
            background: #1c1c1c;
            border-radius: 16px;
            margin: 40px auto;
            max-width: 650px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .no-grounds-found .no-grounds-icon {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 15px rgba(124, 58, 237, 0.25);
        }

        .no-grounds-found .no-grounds-icon i {
            font-size: 30px;
            color: white;
        }

        .no-grounds-found h3 {
            font-size: 24px;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 12px;
        }

        .no-grounds-found p {
            font-size: 16px;
            color: #999;
            margin-bottom: 25px;
            line-height: 1.5;
            max-width: 450px;
            margin-left: auto;
            margin-right: auto;
        }

        .reset-filters-btn {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .reset-filters-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            transform: translateY(-2px);
        }

        /* Card styling */
        .card {
            background: #1c1c1c;
            border: 1px solid #333;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            color: #e0e0e0;
            font-size: 18px;
            font-weight: 600;
        }

        .card-text {
            color: #999;
            font-size: 14px;
            line-height: 1.5;
        }

        .card-meta {
            color: #aaa;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
        }

        .card-btn {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .card-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #6366f1);
            transform: translateY(-2px);
        }

        /* Pagination styles */
        .pagination-container {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        .pagination {
            display: flex;
            gap: 5px;
        }

        .pagination .page-item .page-link {
            background: #222;
            color: #e0e0e0;
            border: 1px solid #333;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s;
        }

        .pagination .page-item .page-link:hover {
            background: #333;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            border-color: transparent;
        }

        /* Page header */
        .page-header {
            background: #1c1c1c;
            padding: 40px 0;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #e0e0e0;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 16px;
            color: #999;
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
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
        <div class="filter-button-container">
            <button id="show-filters-btn" class="show-filters-btn">
                <span class="filter-icon"><i class="fas fa-sliders-h"></i></span>
                <span class="filter-text">Filter Grounds</span>
            </button>
        </div>

        <div class="filter-dropdown" id="filter-section">
            <div class="filter-dropdown-content">
                <div class="filter-header">
                    <h3>Filter Options</h3>
                    <button class="close-filters-btn" id="close-filters-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="filter-body">
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <div class="search-filter">
                            <input type="text" placeholder="Search grounds..." class="form-control" id="search-input" value="{{ $search ?? '' }}">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Sport Category</label>
                        <div class="category-filter">
                            <select class="form-control" id="category-filter">
                                <option value="allgrounds">All Sports</option>
                                <option value="football">Football</option>
                                <option value="cricket">Cricket</option>
                                <option value="basketball">Basketball</option>
                                <option value="tennis">Tennis</option>
                                <option value="volleyball">Volleyball</option>
                                <option value="badminton">Badminton</option>
                            </select>
                            <i class="fas fa-chevron-down select-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="filter-footer">
                    <button class="apply-filters-btn" id="apply-filters-btn">Apply Filters</button>
                    <button class="reset-all-btn" id="reset-all-btn">Reset All</button>
                </div>
            </div>
        </div>

        <div class="cards-container">
            @if(isset($grounds) && $grounds->count() > 0)
                @foreach($grounds as $ground)
                    <div class="card" data-category="{{ $ground->ground_category }}">
                        @if($ground->is_new)
                            <div class="card-badge">New</div>
                        @elseif($ground->is_featured)
                            <div class="card-badge">Featured</div>
                        @endif
                        <div class="card-image">
                            <img src="{{ $ground->getImageUrl() }}" alt="{{ $ground->name }}">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title">{{ $ground->name }}</h3>
                            <p class="card-text">{{ $ground->description ?? 'Professional sports ground with modern amenities for the perfect game experience.' }}</p>
                            <a href="{{ route('user.view_ground', $ground->id) }}" class="btn card-btn"><i class="fas fa-bookmark"></i> Book Now</a>
                            <div class="card-meta">
                                <span><i class="fas fa-star"></i> 4.8 (120 reviews)</span>
                                <span><i class="fas fa-rupee-sign"></i> {{ $ground->price_per_hour }}/hr</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- No grounds message with better UI -->
                <div class="no-grounds-found">
                    <div class="no-grounds-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Grounds Found</h3>
                    <p>We couldn't find any sports grounds matching your filters. Please try different criteria or reset the filters to see all available grounds.</p>
                    <button type="button" class="reset-filters-btn" id="dynamic-reset-btn">Reset Filters</button>
                </div>
            @endif

            @if((isset($search) && !empty($search)) || (isset($price) && !empty($price)) || (isset($category) && $category !== 'allgrounds'))
                <!-- Show the currently applied filters -->
                <div class="applied-filters">
                    <div class="applied-filters-title">Applied filters:</div>
                    <div class="filter-tags">
                        @if(isset($category) && $category !== 'allgrounds')
                            <span class="filter-tag">
                                Category: {{ ucfirst($category) }} <i class="fas fa-times" data-filter="category"></i>
                            </span>
                        @endif

                        @if(isset($search) && !empty($search))
                            <span class="filter-tag">
                                Search: "{{ $search }}" <i class="fas fa-times" data-filter="search"></i>
                            </span>
                        @endif

                        @if(isset($price) && !empty($price))
                            <span class="filter-tag">
                                Price: {{ $price }} <i class="fas fa-times" data-filter="price"></i>
                            </span>
                        @endif

                        <button class="clear-all-btn" id="clear-all-filters">Clear All</button>
                    </div>
                </div>
            @endif
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the filter elements
        const categoryFilter = document.getElementById('category-filter');
        const searchFilter = document.getElementById('search-input');
        const priceFilter = document.getElementById('price-filter');
        const resetBtn = document.getElementById('dynamic-reset-btn');
        const cards = document.querySelectorAll('.card');

        // Filter toggle elements
        const showFiltersBtn = document.getElementById('show-filters-btn');
        const closeFiltersBtn = document.getElementById('close-filters-btn');
        const filterSection = document.getElementById('filter-section');
        const applyFiltersBtn = document.getElementById('apply-filters-btn');
        const resetAllBtn = document.getElementById('reset-all-btn');

        // Toggle filters visibility
        if (showFiltersBtn && filterSection && closeFiltersBtn) {
            // Show filters when the filter button is clicked
            showFiltersBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                filterSection.classList.add('active');
                // Focus on search input for better UX
                setTimeout(() => {
                    if (searchFilter) searchFilter.focus();
                }, 300);
            });

            // Hide filters when the close button is clicked
            closeFiltersBtn.addEventListener('click', function() {
                filterSection.classList.remove('active');
            });

            // Also hide filters when clicking outside
            document.addEventListener('click', function(e) {
                if (filterSection.classList.contains('active') &&
                    !filterSection.contains(e.target) &&
                    e.target !== showFiltersBtn) {
                    filterSection.classList.remove('active');
                }
            });

            // Prevent closing when clicking inside the dropdown
            filterSection.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Apply filters button
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', function() {
                    filterGroundsClientSide();
                    filterSection.classList.remove('active');
                });
            }

            // Reset all filters button
            if (resetAllBtn) {
                resetAllBtn.addEventListener('click', function() {
                    resetFiltersInPlace();
                });
            }
        }

        // Add search clear button
        const searchContainer = document.querySelector('.search-filter');
        if (searchContainer && searchFilter) {
            const clearBtn = document.createElement('span');
            clearBtn.className = 'search-clear';
            clearBtn.innerHTML = '×';
            searchContainer.appendChild(clearBtn);

            clearBtn.addEventListener('click', function() {
                searchFilter.value = '';
                searchFilter.focus();
                this.style.display = 'none';
            });

            // Show/hide clear button based on search input
            searchFilter.addEventListener('input', function() {
                clearBtn.style.display = this.value ? 'block' : 'none';
            });

            // Check initial value
            if (searchFilter.value) {
                clearBtn.style.display = 'block';
            }
        }

        // Extract and store all card prices on page load to avoid repeated DOM queries
        const cardPrices = [];
        cards.forEach(card => {
            try {
                const priceSpans = card.querySelectorAll('.card-meta span');
                let price = 0;

                for (const span of priceSpans) {
                    if (span.innerHTML.includes('₹')) {
                        const priceText = span.textContent.trim();
                        const priceMatch = priceText.match(/₹\s*(\d+)/);

                        if (priceMatch && priceMatch[1]) {
                            price = parseInt(priceMatch[1], 10);
                            break;
                        }
                    }
                }

                // Store the price for this card
                cardPrices.push({
                    card: card,
                    price: price
                });

                console.log('Card price extracted:', price);
            } catch (error) {
                console.error('Error extracting price:', error);
                cardPrices.push({
                    card: card,
                    price: 0
                });
            }
        });

        // Check if there are actually any cards
        const cardsContainer = document.querySelector('.cards-container');
        const hasCards = cards.length > 0;

        // Debounce function to limit how often a function can fire
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func.apply(context, args);
                }, wait);
            };
        }

        // Reset filters without page reload
        function resetFiltersInPlace() {
            // Reset filter values
            if (categoryFilter) categoryFilter.value = 'allgrounds';
            if (searchFilter) searchFilter.value = '';

            // Update URL without refreshing
            const newUrl = window.location.pathname;
            window.history.pushState({}, '', newUrl);

            // Show all cards
            cards.forEach(card => {
                card.style.display = 'block';
            });

            // Hide no results message
            const noResults = document.querySelector('.no-grounds-found');
            if (noResults) {
                noResults.style.display = 'none';
            }

            // Hide applied filters section
            const appliedFilters = document.querySelector('.applied-filters');
            if (appliedFilters) {
                appliedFilters.style.display = 'none';
            }

            // Hide clear button
            const clearBtn = document.querySelector('.search-clear');
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }

            // Reset filter button text
            if (showFiltersBtn) {
                showFiltersBtn.querySelector('.filter-text').textContent = 'Filter Grounds';
            }

            // Close filter panel if needed
            if (filterSection) {
                filterSection.classList.remove('active');
            }
        }

        // Function to remove a specific filter without page reload
        function removeFilterInPlace(filterName) {
            // Reset the specific filter
            if (filterName === 'category' && categoryFilter) categoryFilter.value = 'allgrounds';
            if (filterName === 'search' && searchFilter) {
                searchFilter.value = '';
                // Hide clear button
                const clearBtn = document.querySelector('.search-clear');
                if (clearBtn) {
                    clearBtn.style.display = 'none';
                }
            }

            // Update URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.delete(filterName);

            // Update URL without refreshing
            const newUrl = urlParams.toString()
                ? `${window.location.pathname}?${urlParams.toString()}`
                : window.location.pathname;
            window.history.pushState({}, '', newUrl);

            // Re-apply remaining filters
            filterGroundsClientSide();

            // Handle applied filters display
            updateAppliedFiltersDisplay();
        }

        // Update the applied filters display
        function updateAppliedFiltersDisplay() {
            const appliedFilters = document.querySelector('.applied-filters');
            if (!appliedFilters) {
                console.log('Applied filters container not found');
                return;
            }

            const hasCategory = categoryFilter && categoryFilter.value !== 'allgrounds';
            const hasSearch = searchFilter && searchFilter.value !== '';

            console.log('Filter status:', { hasCategory, hasSearch });

            // Show/hide applied filters section
            if (!hasCategory && !hasSearch) {
                appliedFilters.style.display = 'none';
                // Show normal filter button if no filters applied
                if (showFiltersBtn) {
                    showFiltersBtn.querySelector('.filter-text').textContent = 'Filter Grounds';
                }
            } else {
                appliedFilters.style.display = 'block';
                // Update filter button to show active filters count
                if (showFiltersBtn) {
                    const activeCount = (hasCategory ? 1 : 0) + (hasSearch ? 1 : 0);
                    showFiltersBtn.querySelector('.filter-text').textContent = `Filters (${activeCount})`;
                }

                // Update filter tags
                const filterTags = appliedFilters.querySelector('.filter-tags');
                if (filterTags) {
                    filterTags.innerHTML = '';

                    if (hasCategory) {
                        const tag = document.createElement('span');
                        tag.className = 'filter-tag';

                        // Add sport icon to tag
                        let iconClass = 'fas fa-globe';
                        switch(categoryFilter.value) {
                            case 'football': iconClass = 'fas fa-futbol'; break;
                            case 'cricket': iconClass = 'fas fa-baseball-ball'; break;
                            case 'basketball': iconClass = 'fas fa-basketball-ball'; break;
                            case 'tennis': iconClass = 'fas fa-table-tennis'; break;
                            case 'volleyball': iconClass = 'fas fa-volleyball-ball'; break;
                            case 'badminton': iconClass = 'fas fa-running'; break;
                        }

                        tag.innerHTML = `<i class="${iconClass}"></i> ${categoryFilter.value} <i class="fas fa-times" data-filter="category"></i>`;
                        filterTags.appendChild(tag);
                    }

                    if (hasSearch) {
                        const tag = document.createElement('span');
                        tag.className = 'filter-tag';
                        tag.innerHTML = `<i class="fas fa-search"></i> "${searchFilter.value}" <i class="fas fa-times" data-filter="search"></i>`;
                        filterTags.appendChild(tag);
                    }

                    // Add clear all button
                    const clearAllBtn = document.createElement('button');
                    clearAllBtn.className = 'clear-all-btn';
                    clearAllBtn.id = 'clear-all-filters';
                    clearAllBtn.innerText = 'Clear All';
                    clearAllBtn.addEventListener('click', resetFiltersInPlace);
                    filterTags.appendChild(clearAllBtn);

                    // Add event listeners to the new filter tag close icons
                    filterTags.querySelectorAll('.filter-tag i.fa-times').forEach(icon => {
                        icon.addEventListener('click', function() {
                            const filterType = this.getAttribute('data-filter');
                            console.log('Removing filter:', filterType);
                            removeFilterInPlace(filterType);
                        });
                    });
                }
            }
        }

        // Client-side filtering function
        function filterGroundsClientSide() {
            if (!hasCards) return; // Skip if no cards

            const categoryValue = categoryFilter ? categoryFilter.value : 'allgrounds';
            const searchValue = searchFilter ? searchFilter.value.toLowerCase() : '';

            console.log('Filtering with values:', { categoryValue, searchValue });

            let visibleCount = 0;

            // Loop through all cards
            cardPrices.forEach(({ card }) => {
                // Get card data for other filters
                const cardCategory = card.getAttribute('data-category');
                const cardName = card.querySelector('.card-title').textContent.toLowerCase();
                const cardDesc = card.querySelector('.card-text').textContent.toLowerCase();

                // Initial visibility
                let isVisible = true;

                // Filter by category
                if (categoryValue !== 'allgrounds' && cardCategory !== categoryValue) {
                    isVisible = false;
                }

                // Filter by search term
                if (searchValue && !cardName.includes(searchValue) && !cardDesc.includes(searchValue)) {
                    isVisible = false;
                }

                // Set visibility
                card.style.display = isVisible ? 'block' : 'none';

                if (isVisible) {
                    visibleCount++;
                }
            });

            // Show no results message if all cards are hidden
            toggleNoResultsMessage(visibleCount);

            // Update applied filters display
            updateAppliedFiltersDisplay();

            // Update URL with current filters (without page reload)
            updateUrlWithCurrentFilters();
        }

        // Update URL with current filters
        function updateUrlWithCurrentFilters() {
            const categoryValue = categoryFilter && categoryFilter.value !== 'allgrounds' ? categoryFilter.value : null;
            const searchValue = searchFilter && searchFilter.value ? searchFilter.value : null;

            const urlParams = new URLSearchParams();
            if (categoryValue) urlParams.set('category', categoryValue);
            if (searchValue) urlParams.set('search', searchValue);

            const newUrl = urlParams.toString()
                ? `${window.location.pathname}?${urlParams.toString()}`
                : window.location.pathname;

            window.history.pushState({}, '', newUrl);
        }

        // Toggle the no results message
        function toggleNoResultsMessage(visibleCount) {
            // Create or show no results message if all cards are hidden
            if (visibleCount === 0 && hasCards) {
                let noResultsElement = document.querySelector('.no-grounds-found');

                if (!noResultsElement) {
                    // Create the no results element if it doesn't exist
                    noResultsElement = document.createElement('div');
                    noResultsElement.className = 'no-grounds-found';
                    noResultsElement.innerHTML = `
                        <div class="no-grounds-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>No Grounds Found</h3>
                        <p>We couldn't find any sports grounds matching your filters. Please try different criteria or reset the filters to see all available grounds.</p>
                        <button type="button" class="reset-filters-btn" id="dynamic-reset-btn">Reset Filters</button>
                    `;
                    cardsContainer.appendChild(noResultsElement);

                    // Add event listener to the dynamic reset button
                    document.getElementById('dynamic-reset-btn').addEventListener('click', resetFiltersInPlace);
                } else {
                    noResultsElement.style.display = 'block';
                }
            } else {
                // Hide the no results message if we have visible cards
                const noResults = document.querySelector('.no-grounds-found');
                if (noResults) {
                    noResults.style.display = 'none';
                }
            }
        }

        // Initialize event listeners
        function initEventListeners() {
            // Add event listener to reset button in "no grounds found" message
            if (resetBtn) {
                resetBtn.addEventListener('click', resetFiltersInPlace);
            }

            // Don't filter immediately on change for better UX with the apply button
            if (categoryFilter) {
                categoryFilter.addEventListener('change', function() {
                    console.log('Category changed to:', this.value);

                    // Add animation to the select
                    this.classList.add('category-changed');
                    setTimeout(() => {
                        this.classList.remove('category-changed');
                    }, 500);
                });
            }

            // Handle search input (but don't filter immediately)
            if (searchFilter) {
                searchFilter.addEventListener('input', function() {
                    console.log('Search input changed to:', this.value);

                    // Add animation to the search input
                    this.classList.add('search-active');
                    setTimeout(() => {
                        this.classList.remove('search-active');
                    }, 500);

                    // Update clear button visibility
                    const clearBtn = document.querySelector('.search-clear');
                    if (clearBtn) {
                        clearBtn.style.display = this.value ? 'block' : 'none';
                    }
                });

                // Filter if Enter is pressed
                searchFilter.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        filterGroundsClientSide();
                        if (filterSection) {
                            filterSection.classList.remove('active');
                        }
                    }
                });
            }

            // Filter when price filter changes
            if (priceFilter) {
                priceFilter.addEventListener('change', function() {
                    console.log('Price filter changed to:', this.value);
                    // Run filtering immediately without any delay
                    filterGroundsClientSide();
                });
            }

            // Add event listeners to filter tag close icons
            document.querySelectorAll('.filter-tag i.fa-times').forEach(icon => {
                icon.addEventListener('click', function() {
                    const filterType = this.getAttribute('data-filter');
                    removeFilterInPlace(filterType);
                });
            });

            // Add event listener to clear all button
            const clearAllBtn = document.querySelector('.clear-all-btn');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', resetFiltersInPlace);
            }
        }

        // Set filter values from URL params on page load
        function setFiltersFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);

            // Set category filter
            if (urlParams.has('category') && categoryFilter) {
                categoryFilter.value = urlParams.get('category');
            }

            // Set search filter
            if (urlParams.has('search') && searchFilter) {
                searchFilter.value = urlParams.get('search');
            }

            // Set price filter
            if (urlParams.has('price') && priceFilter) {
                const priceValue = urlParams.get('price');
                // Verify price value is valid
                const validPrices = ['0-25', '25-50', '50-100', '100+'];
                if (validPrices.includes(priceValue)) {
                    priceFilter.value = priceValue;
                }
            }

            // Update applied filters display
            updateAppliedFiltersDisplay();
        }

        // Initialize the page
        function init() {
            console.log('Initializing ground filters');

            // First extract all prices from cards (should already be done above)
            console.log(`Extracted prices from ${cardPrices.length} cards`);
            cardPrices.forEach((item, index) => {
                console.log(`Card ${index}: Price = ${item.price}`);
            });

            // Then set filter values from URL
            setFiltersFromUrl();

            // Setup event listeners
            initEventListeners();

            // Apply filters with a slight delay to ensure DOM is fully ready
            setTimeout(() => {
                console.log('Applying initial filters');
                filterGroundsClientSide();
            }, 100);

            // Add debug info for URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            console.log('URL parameters:', Object.fromEntries(urlParams.entries()));
        }

        // Start initialization
        init();
    });
</script>
@endsection
