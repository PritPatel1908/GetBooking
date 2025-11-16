import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import {
    HomeIcon,
    MapPinIcon,
    CalendarIcon,
    ClockIcon,
    UserIcon,
    StarIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    Bars3Icon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/react/24/solid';
import axios from 'axios';
import CityFilter from '../components/CityFilter';

function AllGroundsPage() {
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [grounds, setGrounds] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [search, setSearch] = useState('');
    const [category, setCategory] = useState('all');
    const [selectedCity, setSelectedCity] = useState('');
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [groundStates, setGroundStates] = useState({});

    useEffect(() => {
        // Load city from localStorage
        const savedCity = localStorage.getItem('selectedCity') || '';
        setSelectedCity(savedCity);
        
        fetchUserData();
        fetchGrounds(savedCity);
    }, []);

    useEffect(() => {
        fetchGrounds();
    }, [category, search, selectedCity]);

    useEffect(() => {
        if (grounds.length > 0) {
            const intervals = grounds.map((ground) => {
                if (ground.images && ground.images.length > 0) {
                    const imageInterval = setInterval(() => {
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
                    }, 4000);

                    return imageInterval;
                }
                return null;
            });

            return () => {
                intervals.forEach((interval) => {
                    if (interval) clearInterval(interval);
                });
            };
        }
    }, [grounds]);

    const fetchUserData = async () => {
        try {
            const response = await axios.get('/api/user');
            setUser(response.data);
        } catch (error) {
            console.log('User not authenticated');
        }
    };

    const fetchGrounds = async (city = null) => {
        try {
            setLoading(true);
            setError(null);
            const params = {};
            if (category && category !== 'all') {
                params.category = category;
            }
            if (search) {
                params.search = search;
            }
            const cityToUse = city !== null ? city : selectedCity;
            if (cityToUse && cityToUse !== '' && cityToUse !== 'all') {
                params.city = cityToUse;
            }

            const response = await axios.get('/api/grounds', { params });
            if (response.data.success && response.data.grounds) {
                setGrounds(response.data.grounds);
                
                // Initialize image indices
                const initialState = {};
                response.data.grounds.forEach((ground) => {
                    initialState[ground.id] = {
                        imageIndex: 0,
                    };
                });
                setGroundStates(initialState);
            } else {
                setGrounds([]);
            }
        } catch (error) {
            console.error('Error fetching grounds:', error);
            setError('Failed to load grounds. Please try again later.');
            setGrounds([]);
        } finally {
            setLoading(false);
        }
    };

    const handleViewGround = (id) => {
        navigate(`/grounds/${id}`);
    };

    const handleSearch = (e) => {
        e.preventDefault();
        fetchGrounds();
    };

    const formatTime = (timeString) => {
        if (!timeString) return '';
        try {
            const date = new Date(`2000-01-01T${timeString}`);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        } catch {
            return timeString;
        }
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
                            
                            <button
                                onClick={() => navigate('/home')}
                                className="flex items-center space-x-2 text-gray-700 hover:text-green-600 transition-colors"
                            >
                                <ArrowLeftIcon className="h-5 w-5" />
                                <span className="hidden sm:inline">Back</span>
                            </button>
                            <Link to="/home" className="flex items-center space-x-2">
                                <div className="bg-gradient-to-r from-green-500 to-blue-500 p-1.5 sm:p-2 rounded-lg">
                                    <HomeIcon className="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                                </div>
                                <span className="text-lg sm:text-2xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                                    GetBooking
                                </span>
                            </Link>
                            <nav className="hidden md:flex items-center space-x-4 ml-4">
                                <Link
                                    to="/grounds"
                                    className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-green-600 transition-colors border-b-2 border-green-600"
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
                                        fetchGrounds(city);
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
                                            fetchGrounds(city);
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

            {/* Page Content */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="mb-6 sm:mb-8">
                    <h1 className="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-2">All Sports Grounds</h1>
                    <p className="text-sm sm:text-base text-gray-600">Browse and book from our collection of premium sports facilities</p>
                </div>

                {/* Search and Filter */}
                <div className="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <form onSubmit={handleSearch} className="flex flex-col md:flex-row gap-4">
                        <div className="flex-1 relative">
                            <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search by name, location, or description..."
                                className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </div>
                        <div className="flex gap-4">
                            <select
                                value={category}
                                onChange={(e) => setCategory(e.target.value)}
                                className="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option value="all">All Categories</option>
                                <option value="cricket">Cricket</option>
                                <option value="football">Football</option>
                                <option value="basketball">Basketball</option>
                                <option value="tennis">Tennis</option>
                                <option value="badminton">Badminton</option>
                                <option value="volleyball">Volleyball</option>
                                <option value="other">Other</option>
                            </select>
                            <button
                                type="submit"
                                className="px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all flex items-center space-x-2"
                            >
                                <FunnelIcon className="h-5 w-5" />
                                <span>Filter</span>
                            </button>
                        </div>
                    </form>
                </div>

                {/* Grounds List */}
                {loading ? (
                    <div className="text-center py-12">
                        <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
                        <p className="mt-4 text-gray-600">Loading grounds...</p>
                    </div>
                ) : error ? (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                        <p className="text-red-800">{error}</p>
                        <button
                            onClick={fetchGrounds}
                            className="mt-4 px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-all"
                        >
                            Try Again
                        </button>
                    </div>
                ) : grounds.length === 0 ? (
                    <div className="bg-white rounded-xl shadow-lg p-12 text-center">
                        <HomeIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">No Grounds Found</h3>
                        <p className="text-gray-600 mb-6">
                            {search || category !== 'all'
                                ? 'Try adjusting your search or filter criteria.'
                                : "No grounds available at the moment."}
                        </p>
                        {(search || category !== 'all') && (
                            <button
                                onClick={() => {
                                    setSearch('');
                                    setCategory('all');
                                }}
                                className="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-all"
                            >
                                Clear Filters
                            </button>
                        )}
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                        {grounds.map((ground) => {
                            const currentState = groundStates[ground.id] || { imageIndex: 0 };
                            const currentImage = ground.images && ground.images.length > 0
                                ? ground.images[currentState.imageIndex]
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
                                            {ground.ground_type && (
                                                <span className="inline-block px-2.5 py-1 bg-green-100 text-green-700 text-[11px] md:text-xs font-semibold rounded-full">
                                                    {ground.ground_type}
                                                </span>
                                            )}
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
                                        </div>
                                        {ground.display_price && (
                                            <div className="mb-3 p-2 bg-green-50 rounded-lg">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-[11px] md:text-xs text-gray-600 font-medium">
                                                        Starting from
                                                    </span>
                                                    <span className="text-base md:text-lg font-bold text-green-600">
                                                        ₹{parseFloat(ground.display_price).toFixed(2)}
                                                    </span>
                                                </div>
                                            </div>
                                        )}
                                        {ground.description && (
                                            <p className="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2">
                                                {ground.description}
                                            </p>
                                        )}
                                        {ground.features && ground.features.length > 0 && (
                                            <div className="mb-3 flex flex-wrap gap-1.5">
                                                {ground.features.slice(0, 3).map((feature, idx) => (
                                                    <span
                                                        key={idx}
                                                        className="px-2 py-0.5 bg-blue-50 text-blue-700 text-[11px] md:text-xs rounded"
                                                    >
                                                        {feature}
                                                    </span>
                                                ))}
                                                {ground.features.length > 3 && (
                                                    <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-[11px] md:text-xs rounded">
                                                        +{ground.features.length - 3} more
                                                    </span>
                                                )}
                                            </div>
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
                )}
            </div>

            {/* Footer */}
            <footer className="bg-gray-900 text-white py-12 mt-16">
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
                                GetBooking is India's leading platform for sports ground bookings.
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
                                        <Link to="/my_bookings" className="hover:text-white transition-colors">
                                            My Bookings
                                        </Link>
                                    </li>
                                )}
                            </ul>
                        </div>
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Contact Us</h3>
                            <ul className="space-y-2 text-gray-400">
                                <li>+91 1800-123-4567</li>
                                <li>support@getbooking.in</li>
                            </ul>
                        </div>
                    </div>
                    <div className="border-t border-gray-800 pt-8 text-center text-gray-400">
                        <p>&copy; {new Date().getFullYear()} GetBooking. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}

export default AllGroundsPage;

