import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import {
    HomeIcon,
    MapPinIcon,
    CalendarIcon,
    ClockIcon,
    PhoneIcon,
    EnvelopeIcon,
    StarIcon,
    UserIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    BoltIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    Bars3Icon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/react/24/solid';
import axios from 'axios';
import CityFilter from '../components/CityFilter';

function HomePage() {
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [featuredGrounds, setFeaturedGrounds] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentSlide, setCurrentSlide] = useState(0);
    const [hasMoreGrounds, setHasMoreGrounds] = useState(false);
    const [groundStates, setGroundStates] = useState({}); // Track current image and slot for each ground
    const [selectedCity, setSelectedCity] = useState('');
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [statistics, setStatistics] = useState({
        total_grounds: 0,
        total_bookings: 0,
        completed_bookings: 0,
        total_users: 0,
    });
    const [displayStats, setDisplayStats] = useState({
        total_grounds: 0,
        total_bookings: 0,
        completed_bookings: 0,
        total_users: 0,
    });

    useEffect(() => {
        // Clear all booking-related localStorage and sessionStorage when landing on home page
        // This ensures a fresh start when user returns to home
        const keysToRemove = [];
        
        // Remove all ground booking selections from localStorage
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('ground_') && key.endsWith('_booking_selection')) {
                keysToRemove.push(key);
            }
        }
        
        keysToRemove.forEach(key => localStorage.removeItem(key));
        
        // Remove all scroll flags from sessionStorage
        const sessionKeysToRemove = [];
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key && key.startsWith('scroll_to_booking_')) {
                sessionKeysToRemove.push(key);
            }
        }
        
        sessionKeysToRemove.forEach(key => sessionStorage.removeItem(key));
        
        // Load city from localStorage
        const savedCity = localStorage.getItem('selectedCity') || '';
        setSelectedCity(savedCity);
        
        fetchUserData();
        fetchFeaturedGrounds(savedCity);
        fetchStatistics();
    }, []);

    const fetchUserData = async () => {
        try {
            const response = await axios.get('/api/user');
            setUser(response.data);
        } catch (error) {
            console.log('User not authenticated or error fetching user data');
        }
    };

    const fetchFeaturedGrounds = async (city = null) => {
        try {
            const params = {};
            const cityToUse = city !== null ? city : selectedCity;
            if (cityToUse && cityToUse !== '' && cityToUse !== 'all') {
                params.city = cityToUse;
            }
            
            const response = await axios.get('/api/grounds/featured', { params });
            const grounds = response.data.grounds || [];
            setFeaturedGrounds(grounds);
            setHasMoreGrounds(response.data.has_more || false);
            
            // Initialize random image and slot indices for each ground
            const initialState = {};
            grounds.forEach((ground) => {
                const randomImageIndex = ground.images && ground.images.length > 0 
                    ? Math.floor(Math.random() * ground.images.length) 
                    : 0;
                const randomSlotIndex = ground.slots && ground.slots.length > 0 
                    ? Math.floor(Math.random() * ground.slots.length) 
                    : 0;
                initialState[ground.id] = {
                    imageIndex: randomImageIndex,
                    slotIndex: randomSlotIndex,
                };
            });
            setGroundStates(initialState);
        } catch (error) {
            console.error('Error fetching featured grounds:', error);
            setFeaturedGrounds([]);
            setHasMoreGrounds(false);
        } finally {
            setLoading(false);
        }
    };

    // Auto-rotate images and slots for each ground
    useEffect(() => {
        if (featuredGrounds.length === 0) return;

        const intervals = featuredGrounds.map((ground) => {
            // Rotate image every 4 seconds
            const imageInterval = setInterval(() => {
                if (ground.images && ground.images.length > 0) {
                    setGroundStates((prev) => {
                        const currentIndex = prev[ground.id]?.imageIndex || 0;
                        const nextIndex = (currentIndex + 1) % ground.images.length;
                        return {
                            ...prev,
                            [ground.id]: {
                                ...prev[ground.id],
                                imageIndex: nextIndex,
                            },
                        };
                    });
                }
            }, 4000);

            // Rotate slot/price every 5 seconds
            const slotInterval = setInterval(() => {
                if (ground.slots && ground.slots.length > 0) {
                    setGroundStates((prev) => {
                        const currentIndex = prev[ground.id]?.slotIndex || 0;
                        const nextIndex = (currentIndex + 1) % ground.slots.length;
                        return {
                            ...prev,
                            [ground.id]: {
                                ...prev[ground.id],
                                slotIndex: nextIndex,
                            },
                        };
                    });
                }
            }, 5000);

            return { imageInterval, slotInterval };
        });

        // Cleanup intervals on unmount
        return () => {
            intervals.forEach(({ imageInterval, slotInterval }) => {
                clearInterval(imageInterval);
                clearInterval(slotInterval);
            });
        };
    }, [featuredGrounds]);

    const fetchStatistics = async () => {
        try {
            const response = await axios.get('/api/statistics');
            if (response.data.success && response.data.statistics) {
                setStatistics(response.data.statistics);
            }
        } catch (error) {
            console.error('Error fetching statistics:', error);
        }
    };

    // Animate counter from 0 to target value
    useEffect(() => {
        const duration = 2000; // 2 seconds
        const steps = 60; // 60 steps
        const stepDuration = duration / steps;

        const animateCounter = (key) => {
            const target = statistics[key] || 0;
            const increment = target / steps;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                setDisplayStats((prev) => ({
                    ...prev,
                    [key]: Math.floor(current),
                }));
            }, stepDuration);
        };

        // Animate each statistic
        Object.keys(statistics).forEach((key) => {
            if (statistics[key] > 0) {
                animateCounter(key);
            }
        });
    }, [statistics]);

    const handleViewGrounds = () => {
        window.location.href = '/grounds';
    };

    const handleViewGround = (id) => {
        window.location.href = `/grounds/${id}`;
    };

    // Hero slider data
    const heroSlides = [
        {
            id: 1,
            title: 'Your Sports Ground, Just a Click Away',
            subtitle: 'Premium Ground Booking Platform',
            description: 'Book cricket, football, basketball, and more at premium sports grounds across the city. Secure your slot in seconds with instant confirmation. Join thousands of players who trust GetBooking for their sports adventures.',
            image: 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=1600',
            buttonText: 'Explore Grounds',
            buttonAction: handleViewGrounds,
        },
        {
            id: 2,
            title: 'World-Class Facilities at Your Fingertips',
            subtitle: 'Top-Quality Sports Infrastructure',
            description: 'Experience professional-grade sports facilities with modern amenities. From well-maintained turfs to floodlit courts, find the perfect venue for your match. Competitive pricing, flexible timings, and seamless booking experience.',
            image: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1600',
            buttonText: 'View All Grounds',
            buttonAction: handleViewGrounds,
        },
        {
            id: 3,
            title: 'Play. Book. Repeat.',
            subtitle: 'Join 50,000+ Active Players',
            description: 'Be part of India\'s fastest-growing sports community. Whether it\'s a friendly match with friends or a competitive tournament, GetBooking connects you to the best sports grounds in your city. Start your booking journey today!',
            image: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=1600',
            buttonText: 'Get Started',
            buttonAction: () => {
                if (!user) {
                    navigate('/register');
                } else {
                    handleViewGrounds();
                }
            },
        },
    ];

    // Auto-slide functionality
    useEffect(() => {
        const slideInterval = setInterval(() => {
            setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
        }, 5000); // Change slide every 5 seconds

        return () => clearInterval(slideInterval);
    }, [heroSlides.length]);

    const goToSlide = (index) => {
        setCurrentSlide(index);
    };

    const nextSlide = () => {
        setCurrentSlide((prev) => (prev + 1) % heroSlides.length);
    };

    const prevSlide = () => {
        setCurrentSlide((prev) => (prev - 1 + heroSlides.length) % heroSlides.length);
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50">
            {/* Navigation Bar */}
            <nav className="bg-white/80 backdrop-blur-md shadow-md sticky top-0 z-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-16">
                        <div className="flex items-center space-x-2 sm:space-x-4">
                            {/* Mobile Menu Button */}
                            <button
                                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                                className="md:hidden p-2 text-gray-700 hover:text-green-600 transition-colors"
                                aria-label="Toggle menu"
                            >
                                {mobileMenuOpen ? (
                                    <XMarkIcon className="h-6 w-6" />
                                ) : (
                                    <Bars3Icon className="h-6 w-6" />
                                )}
                            </button>
                            
                            <Link to="/home" className="flex items-center space-x-2">
                                <div className="bg-gradient-to-r from-green-500 to-blue-500 p-1.5 sm:p-2 rounded-lg">
                                    <HomeIcon className="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                                </div>
                                <span className="text-lg sm:text-2xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                                    GetBooking
                                </span>
                            </Link>
                            <nav className="hidden md:flex items-center space-x-4 ml-4 lg:ml-8">
                                <Link
                                    to="/grounds"
                                    className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-green-600 transition-colors"
                                >
                                    Grounds
                                </Link>
                                {user && (
                                    <Link
                                        to="/my_bookings"
                                        className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-green-600 transition-colors"
                                    >
                                        Bookings
                                    </Link>
                                )}
                            </nav>
                        </div>
                        <div className="flex items-center space-x-2 sm:space-x-4">
                            <div className="hidden sm:block">
                                <CityFilter 
                                    onCityChange={(city) => {
                                        setSelectedCity(city);
                                        fetchFeaturedGrounds(city);
                                    }}
                                    selectedCity={selectedCity}
                                />
                            </div>
                            {user ? (
                                <div className="hidden sm:flex items-center space-x-3">
                                    <Link
                                        to="/profile"
                                        className="flex items-center space-x-2 bg-green-50 px-3 sm:px-4 py-2 rounded-full hover:bg-green-100 transition-colors cursor-pointer"
                                    >
                                        <UserIcon className="h-5 w-5 text-green-600" />
                                        <span className="text-xs sm:text-sm font-medium text-gray-700 hidden lg:inline">
                                            {user.name}
                                        </span>
                                    </Link>
                                    <a
                                        href="/logout"
                                        className="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:text-green-600 transition-colors"
                                    >
                                        Logout
                                    </a>
                                </div>
                            ) : (
                                <div className="hidden sm:flex items-center space-x-2 sm:space-x-3">
                                    <Link
                                        to="/login"
                                        className="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:text-green-600 transition-colors"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        to="/register"
                                        className="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg hover:from-green-600 hover:to-blue-600 transition-all shadow-md"
                                    >
                                        Register
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                    
                    {/* Mobile Menu */}
                    {mobileMenuOpen && (
                        <div className="md:hidden pb-4 border-t border-gray-200 mt-2 pt-4">
                            <div className="space-y-3">
                                <div className="px-2">
                                    <CityFilter 
                                        onCityChange={(city) => {
                                            setSelectedCity(city);
                                            fetchFeaturedGrounds(city);
                                            setMobileMenuOpen(false);
                                        }}
                                        selectedCity={selectedCity}
                                    />
                                </div>
                                <Link
                                    to="/grounds"
                                    onClick={() => setMobileMenuOpen(false)}
                                    className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors"
                                >
                                    Grounds
                                </Link>
                                {user ? (
                                    <>
                                        <Link
                                            to="/my_bookings"
                                            onClick={() => setMobileMenuOpen(false)}
                                            className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors"
                                        >
                                            My Bookings
                                        </Link>
                                        <Link
                                            to="/profile"
                                            onClick={() => setMobileMenuOpen(false)}
                                            className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors flex items-center space-x-2"
                                        >
                                            <UserIcon className="h-5 w-5" />
                                            <span>Profile ({user.name})</span>
                                        </Link>
                                        <a
                                            href="/logout"
                                            className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors"
                                        >
                                            Logout
                                        </a>
                                    </>
                                ) : (
                                    <>
                                        <Link
                                            to="/login"
                                            onClick={() => setMobileMenuOpen(false)}
                                            className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors"
                                        >
                                            Login
                                        </Link>
                                        <Link
                                            to="/register"
                                            onClick={() => setMobileMenuOpen(false)}
                                            className="block px-3 py-2 text-base font-medium bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg hover:from-green-600 hover:to-blue-600 transition-all text-center"
                                        >
                                            Register
                                        </Link>
                                    </>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </nav>

            {/* Hero Slider */}
            <div className="relative overflow-hidden h-[320px] md:h-[380px]">
                {/* Slides Container */}
                <div className="relative w-full h-full">
                    {heroSlides.map((slide, index) => (
                        <div
                            key={slide.id}
                            className={`absolute inset-0 transition-opacity duration-1000 ${
                                index === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0'
                            }`}
                        >
                            {/* Background Image */}
                            <div
                                className="absolute inset-0 bg-cover bg-center"
                                style={{
                                    backgroundImage: `url(${slide.image})`,
                                }}
                            >
                                <div className="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-black/70"></div>
                            </div>

                            {/* Content Overlay */}
                            <div className="relative z-10 h-full flex items-center justify-center">
                                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                                    <div className="inline-flex items-center space-x-2 bg-green-500/90 text-white px-3 py-1.5 rounded-full mb-4 backdrop-blur-sm">
                                        <BoltIcon className="h-5 w-5" />
                                        <span className="text-sm font-medium">{slide.subtitle}</span>
                                    </div>
                                    <h1 className="text-3xl md:text-5xl lg:text-6xl font-extrabold text-white mb-4 animate-fade-in-up">
                                        {slide.title}
                                    </h1>
                                    <p className="text-lg md:text-xl text-gray-200 mb-6 max-w-2xl mx-auto">
                                        {slide.description}
                                    </p>
                                    <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                                        <button
                                            onClick={slide.buttonAction}
                                            className="px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white text-base md:text-lg font-semibold rounded-lg hover:from-green-600 hover:to-blue-600 transition-all shadow-lg hover:shadow-xl flex items-center space-x-2 transform hover:scale-105"
                                        >
                                            <span>{slide.buttonText}</span>
                                            <ArrowRightIcon className="h-5 w-5" />
                                        </button>
                                        {!user && index === 0 && (
                                            <Link
                                                to="/register"
                                                className="px-6 py-3 bg-white/90 text-green-600 text-base md:text-lg font-semibold rounded-lg hover:bg-white transition-all shadow-md backdrop-blur-sm transform hover:scale-105"
                                            >
                                                Get Started
                                            </Link>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Navigation Arrows */}
                <button
                    onClick={prevSlide}
                    className="absolute left-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all backdrop-blur-sm"
                    aria-label="Previous slide"
                >
                    <ChevronLeftIcon className="h-6 w-6" />
                </button>
                <button
                    onClick={nextSlide}
                    className="absolute right-4 top-1/2 transform -translate-y-1/2 z-20 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all backdrop-blur-sm"
                    aria-label="Next slide"
                >
                    <ChevronRightIcon className="h-6 w-6" />
                </button>

                {/* Dots Indicator */}
                <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-20 flex space-x-3">
                    {heroSlides.map((_, index) => (
                        <button
                            key={index}
                            onClick={() => goToSlide(index)}
                            className={`transition-all rounded-full ${
                                index === currentSlide
                                    ? 'bg-white w-10 h-3'
                                    : 'bg-white/50 w-3 h-3 hover:bg-white/75'
                            }`}
                            aria-label={`Go to slide ${index + 1}`}
                        />
                    ))}
                </div>
            </div>

            {/* Featured Grounds Section */}
            <div className="bg-gray-50 py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-12">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Explore Premium Sports Grounds
                        </h2>
                        <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                            Browse through our curated collection of premium sports grounds. Each venue is verified, well-maintained, and equipped with modern facilities to enhance your sports experience.
                        </p>
                    </div>

                    {loading ? (
                        <div className="text-center py-12">
                            <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
                            <p className="mt-4 text-gray-600">Loading grounds...</p>
                        </div>
                    ) : featuredGrounds.length > 0 ? (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                                {featuredGrounds.map((ground) => {
                                    const currentState = groundStates[ground.id] || { imageIndex: 0, slotIndex: 0 };
                                    const currentImage = ground.images && ground.images.length > 0 
                                        ? ground.images[currentState.imageIndex] 
                                        : null;
                                    const currentSlot = ground.slots && ground.slots.length > 0 
                                        ? ground.slots[currentState.slotIndex] 
                                        : null;

                                    return (
                                        <div
                                            key={ground.id}
                                            className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-all cursor-pointer group"
                                            onClick={() => handleViewGround(ground.id)}
                                        >
                                            <div className="relative h-36 md:h-40 bg-gradient-to-r from-green-400 to-blue-400 overflow-hidden">
                                                {currentImage && currentImage.image_url ? (
                                                    <img
                                                        key={`${ground.id}-${currentState.imageIndex}`}
                                                        src={currentImage.image_url}
                                                        alt={ground.name}
                                                        className="w-full h-full object-cover group-hover:scale-110 transition-all duration-500"
                                                    />
                                                ) : (
                                                    <div className="w-full h-full flex items-center justify-center">
                                                        <HomeIcon className="h-20 w-20 text-white opacity-50" />
                                                    </div>
                                                )}
                                                {ground.is_featured && (
                                                    <div className="absolute top-4 right-4 bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-xs font-bold flex items-center space-x-1">
                                                        <StarIconSolid className="h-4 w-4" />
                                                        <span>Featured</span>
                                                    </div>
                                                )}
                                            </div>
                                            <div className="p-4">
                                                <h3 className="text-lg font-bold text-gray-900 mb-2 line-clamp-1">
                                                    {ground.name}
                                                </h3>
                                                <div className="flex items-center text-gray-600 mb-2">
                                                    <MapPinIcon className="h-4 w-4 mr-2 text-green-600" />
                                                    <span className="text-xs md:text-sm line-clamp-1">{ground.location}</span>
                                                </div>
                                                <div className="flex items-center justify-between mb-3">
                                                    <div className="flex items-center text-gray-600">
                                                        <span className="text-xs md:text-sm font-medium">
                                                            {ground.ground_type || 'Standard'}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center space-x-3">
                                                        {/* Rating Display */}
                                                        {ground.average_rating > 0 && (
                                                            <div className="flex items-center space-x-1">
                                                                <StarIconSolid className="h-4 w-4 text-yellow-400" />
                                                                <span className="text-xs md:text-sm font-semibold text-gray-700">
                                                                    {ground.average_rating}
                                                                </span>
                                                                {ground.reviews_count > 0 && (
                                                                    <span className="text-[10px] md:text-xs text-gray-500">
                                                                        ({ground.reviews_count})
                                                                    </span>
                                                                )}
                                                            </div>
                                                        )}
                                                        {ground.capacity && (
                                                            <div className="flex items-center text-gray-600">
                                                                <UserIcon className="h-4 w-4 mr-1" />
                                                                <span className="text-xs md:text-sm">{ground.capacity} players</span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                                {currentSlot && (
                                                    <div className="mb-3 p-2 bg-green-50 rounded-lg">
                                                        <div className="flex items-center justify-between">
                                                            <span className="text-[11px] md:text-xs text-gray-600 font-medium">
                                                                {(() => {
                                                                    // Check if slot_name contains time format (e.g., "11:00 - 12:00")
                                                                    const timePattern = /\d{1,2}:\d{2}\s*-\s*\d{1,2}:\d{2}/;
                                                                    if (currentSlot.slot_name && timePattern.test(currentSlot.slot_name)) {
                                                                        // If slot_name is just a time format, show "Slot" or use a generic name
                                                                        return 'Slot';
                                                                    }
                                                                    return currentSlot.slot_name || 'Slot';
                                                                })()}
                                                            </span>
                                                            <span className="text-xs md:text-sm font-bold text-green-600">
                                                                ₹{parseFloat(currentSlot.price_per_slot).toFixed(2)}
                                                            </span>
                                                        </div>
                                                        {currentSlot.start_time && currentSlot.end_time && (
                                                            <div className="text-[11px] md:text-xs text-gray-500 mt-1">
                                                                {currentSlot.start_time} - {currentSlot.end_time}
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                                {ground.description && (
                                                    <p className="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2">
                                                        {ground.description}
                                                    </p>
                                                )}
                                                <button className="w-full bg-gradient-to-r from-green-500 to-blue-500 text-white py-2 rounded-md text-sm md:text-base font-semibold hover:from-green-600 hover:to-blue-600 transition-all flex items-center justify-center space-x-2">
                                                    <span>View Details</span>
                                                    <ArrowRightIcon className="h-5 w-5" />
                                                </button>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                            {hasMoreGrounds && (
                                <div className="text-center mt-8">
                                    <button
                                        onClick={handleViewGrounds}
                                        className="px-8 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 mx-auto"
                                    >
                                        <span>View All</span>
                                        <ArrowRightIcon className="h-5 w-5" />
                                    </button>
                                </div>
                            )}
                        </>
                    ) : (
                        <div className="text-center py-12 bg-white rounded-xl">
                            <HomeIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                            <p className="text-gray-600 text-lg">No featured grounds available at the moment</p>
                            <button
                                onClick={handleViewGrounds}
                                className="mt-4 px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all"
                            >
                                View All Grounds
                            </button>
                        </div>
                    )}
                </div>
            </div>

            {/* Features Section */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div className="text-center mb-12">
                    <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Why Choose GetBooking?
                    </h2>
                    <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                        India's most trusted platform for sports ground bookings. Join thousands of satisfied players who choose us for seamless booking experience.
                    </p>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div className="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow border-t-4 border-green-500">
                        <div className="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <CalendarIcon className="h-6 w-6 text-green-600" />
                        </div>
                        <h3 className="text-xl font-bold text-gray-900 mb-2">Instant Booking</h3>
                        <p className="text-gray-600">
                            Book your favorite sports ground in less than 60 seconds. Our intuitive platform makes finding and reserving the perfect venue effortless. Real-time availability ensures you always get the slot you want.
                        </p>
                    </div>
                    <div className="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow border-t-4 border-blue-500">
                        <div className="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <ClockIcon className="h-6 w-6 text-blue-600" />
                        </div>
                        <h3 className="text-xl font-bold text-gray-900 mb-2">24/7 Support</h3>
                        <p className="text-gray-600">
                            Round-the-clock customer support to assist you with any queries. From booking assistance to rescheduling, our dedicated team ensures your experience is smooth and hassle-free.
                        </p>
                    </div>
                    <div className="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow border-t-4 border-yellow-500">
                        <div className="bg-yellow-100 w-12 h-12 rounded-lg flex items-center justify-center mb-4">
                            <CheckCircleIcon className="h-6 w-6 text-yellow-600" />
                        </div>
                        <h3 className="text-xl font-bold text-gray-900 mb-2">Verified & Safe</h3>
                        <p className="text-gray-600">
                            Every ground on our platform undergoes thorough verification. We ensure quality facilities, proper maintenance, and safety standards. Play with complete peace of mind and confidence.
                        </p>
                    </div>
                </div>
            </div>

            {/* Statistics Counter Section */}
            <div className="bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                        <div className="text-center">
                            <div className="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4 backdrop-blur-sm">
                                <HomeIcon className="h-8 w-8 text-white" />
                            </div>
                            <div className="text-4xl md:text-5xl font-bold text-white mb-2">
                                {displayStats.total_grounds.toLocaleString()}+
                            </div>
                            <div className="text-green-100 font-medium text-sm md:text-base">
                                Active Grounds
                            </div>
                        </div>
                        <div className="text-center">
                            <div className="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4 backdrop-blur-sm">
                                <CalendarIcon className="h-8 w-8 text-white" />
                            </div>
                            <div className="text-4xl md:text-5xl font-bold text-white mb-2">
                                {displayStats.total_bookings.toLocaleString()}+
                            </div>
                            <div className="text-green-100 font-medium text-sm md:text-base">
                                Total Bookings
                            </div>
                        </div>
                        <div className="text-center">
                            <div className="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4 backdrop-blur-sm">
                                <CheckCircleIcon className="h-8 w-8 text-white" />
                            </div>
                            <div className="text-4xl md:text-5xl font-bold text-white mb-2">
                                {displayStats.completed_bookings.toLocaleString()}+
                            </div>
                            <div className="text-green-100 font-medium text-sm md:text-base">
                                Completed
                            </div>
                        </div>
                        <div className="text-center">
                            <div className="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4 backdrop-blur-sm">
                                <UserIcon className="h-8 w-8 text-white" />
                            </div>
                            <div className="text-4xl md:text-5xl font-bold text-white mb-2">
                                {displayStats.total_users.toLocaleString()}+
                            </div>
                            <div className="text-green-100 font-medium text-sm md:text-base">
                                Happy Users
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Tournaments and Events Section - Coming Soon */}
            <div className="bg-gradient-to-br from-purple-50 via-blue-50 to-green-50 py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Tournaments & Events
                        </h2>
                        <p className="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
                            Compete in exciting tournaments, join community events, and showcase your skills on the biggest stage. From local championships to city-wide competitions, we're building something amazing for you.
                        </p>
                    </div>
                    <div className="bg-white rounded-2xl shadow-2xl p-12 text-center relative overflow-hidden">
                        {/* Decorative elements */}
                        <div className="absolute top-0 left-0 w-32 h-32 bg-gradient-to-br from-purple-200 to-transparent rounded-full blur-3xl opacity-50"></div>
                        <div className="absolute bottom-0 right-0 w-32 h-32 bg-gradient-to-tl from-green-200 to-transparent rounded-full blur-3xl opacity-50"></div>
                        
                        <div className="relative z-10">
                            <div className="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full mb-6 shadow-lg">
                                <CalendarIcon className="h-10 w-10 text-white" />
                            </div>
                            <h3 className="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                                Coming Soon
                            </h3>
                            <p className="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                                We're crafting an incredible platform for tournaments and events. Imagine weekend leagues, corporate tournaments, inter-city championships, and so much more. Get ready to compete, connect, and celebrate sports like never before!
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center items-center">
                                <div className="flex items-center space-x-2 text-gray-700 bg-green-50 px-4 py-2 rounded-lg">
                                    <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span className="text-sm font-medium">Cricket Tournaments</span>
                                </div>
                                <div className="flex items-center space-x-2 text-gray-700 bg-blue-50 px-4 py-2 rounded-lg">
                                    <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                                    <span className="text-sm font-medium">Football Leagues</span>
                                </div>
                                <div className="flex items-center space-x-2 text-gray-700 bg-purple-50 px-4 py-2 rounded-lg">
                                    <div className="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></div>
                                    <span className="text-sm font-medium">Multi-Sport Events</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* CTA Section */}
            <div className="bg-gradient-to-r from-green-600 to-blue-600 py-16">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">
                        Ready to Book Your Perfect Sports Ground?
                    </h2>
                    <p className="text-xl text-green-50 mb-8 max-w-2xl mx-auto">
                        Join over 50,000 active players who choose GetBooking for seamless ground bookings. From weekend matches to competitive tournaments, we've got the perfect venue for you. Start booking now and experience the difference!
                    </p>
                    <button
                        onClick={handleViewGrounds}
                        className="px-8 py-4 bg-white text-green-600 text-lg font-semibold rounded-lg hover:bg-green-50 transition-all shadow-lg hover:shadow-xl"
                    >
                        Start Booking Now
                    </button>
                </div>
            </div>

            {/* Footer */}
            <footer className="bg-gray-900 text-white py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                        <div>
                            <div className="flex items-center space-x-2 mb-4">
                                <div className="bg-gradient-to-r from-green-500 to-blue-500 p-2 rounded-lg">
                                    <HomeIcon className="h-6 w-6 text-white" />
                                </div>
                                <span className="text-xl font-bold">GetBooking</span>
                            </div>
                            <p className="text-gray-400">
                                GetBooking is India's leading platform for sports ground bookings. We connect passionate players with premium sports facilities across major cities. Trusted by thousands, powered by technology, dedicated to your sports journey.
                            </p>
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Quick Links</h3>
                            <ul className="space-y-2 text-gray-400">
                                <li>
                                    <Link to="/home" className="hover:text-white transition-colors">
                                        Home
                                    </Link>
                                </li>
                                <li>
                                    <Link to="/grounds" className="hover:text-white transition-colors">
                                        All Grounds
                                    </Link>
                                </li>
                                {user && (
                                    <li>
                                        <Link
                                            to="/my_bookings"
                                            className="hover:text-white transition-colors"
                                        >
                                            My Bookings
                                        </Link>
                                    </li>
                                )}
                            </ul>
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Contact Us</h3>
                            <ul className="space-y-2 text-gray-400">
                                <li className="flex items-center space-x-2">
                                    <PhoneIcon className="h-5 w-5" />
                                    <span>+91 1800-123-4567</span>
                                </li>
                                <li className="flex items-center space-x-2">
                                    <EnvelopeIcon className="h-5 w-5" />
                                    <span>support@getbooking.in</span>
                                </li>
                                <li className="text-sm mt-3">
                                    Available 24/7 for your convenience
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div className="border-t border-gray-800 pt-8 text-center text-gray-400">
                        <p>&copy; {new Date().getFullYear()} GetBooking. All rights reserved.</p>
                    </div>
                </div>
            </footer>

            {/* Add animation styles */}
            <style>{`
                @keyframes fade-in-up {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                .animate-fade-in-up {
                    animation: fade-in-up 1s ease-out;
                }
            `}</style>
        </div>
    );
}

export default HomePage;

