import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import {
    HomeIcon,
    CalendarIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
    ArrowLeftIcon,
    CreditCardIcon,
    BanknotesIcon,
    ExclamationTriangleIcon,
    MapPinIcon,
    UserIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    PrinterIcon,
    Bars3Icon,
} from '@heroicons/react/24/outline';
import axios from 'axios';

function BookingDetailsPage() {
    const { bookingSku } = useParams();
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [booking, setBooking] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedImageIndex, setSelectedImageIndex] = useState(0);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    useEffect(() => {
        // Fetch user data first, then booking details
        const initializePage = async () => {
            try {
                const userResponse = await axios.get('/api/user');
                if (userResponse.data && userResponse.data.id) {
                    setUser(userResponse.data);
                    // User is authenticated, fetch booking details
                    await fetchBookingDetails();
                } else {
                    // User not authenticated, redirect to login
                    navigate('/login');
                    return;
                }
            } catch (error) {
                console.error('User not authenticated:', error);
                navigate('/login');
                return;
            }
        };

        if (bookingSku) {
            initializePage();
        } else {
            setError('Booking ID not found');
            setLoading(false);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [bookingSku]);

    const fetchBookingDetails = async () => {
        try {
            setLoading(true);
            setError(null);
            console.log('Fetching booking details for SKU:', bookingSku);
            const response = await axios.get(`/api/bookings/sku/${bookingSku}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            console.log('Booking API response:', response);
            
            if (response.data && response.data.success) {
                setBooking(response.data.booking);
            } else {
                setError('Booking not found');
            }
        } catch (error) {
            console.error('Error fetching booking details:', error);
            if (error.response && error.response.status === 404) {
                setError('Booking not found. Please check the booking ID.');
            } else if (error.response && error.response.status === 401) {
                navigate('/login');
            } else {
                const errorMessage = error.response?.data?.message || error.message || 'Failed to load booking details. Please try again later.';
                setError(errorMessage);
            }
        } finally {
            setLoading(false);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        try {
            return new Date(dateString).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        } catch {
            return dateString;
        }
    };

    const formatTime = (timeString) => {
        if (!timeString) return 'N/A';
        try {
            const date = new Date(`2000-01-01T${timeString}`);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        } catch {
            return timeString;
        }
    };

    const getStatusColor = (status) => {
        switch (status?.toLowerCase()) {
            case 'confirmed':
                return 'bg-green-100 text-green-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            case 'completed':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const getPaymentStatusColor = (paymentStatus) => {
        switch (paymentStatus?.toLowerCase()) {
            case 'completed':
                return 'bg-green-100 text-green-800';
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'failed':
                return 'bg-red-100 text-red-800';
            case 'cancelled':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const downloadInvoice = () => {
        if (booking && booking.booking_sku) {
            window.open(`/user/bookings/${booking.booking_sku}/invoice`, '_blank');
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex items-center justify-center">
                <div className="text-center">
                    <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
                    <p className="text-gray-600">Loading booking details...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50">
                <nav className="bg-white/80 backdrop-blur-md shadow-md sticky top-0 z-50">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center h-16">
                            <div className="flex items-center space-x-4">
                                <button
                                    onClick={() => navigate('/my_bookings')}
                                    className="flex items-center space-x-2 text-gray-700 hover:text-green-600 transition-colors"
                                >
                                    <ArrowLeftIcon className="h-5 w-5" />
                                    <span className="hidden sm:inline">Back to Bookings</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div className="bg-white rounded-xl shadow-lg p-12 text-center">
                        <ExclamationTriangleIcon className="h-16 w-16 text-red-400 mx-auto mb-4" />
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">Error Loading Booking</h3>
                        <p className="text-gray-600 mb-6">{error}</p>
                        <button
                            onClick={() => navigate('/my_bookings')}
                            className="inline-block px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all"
                        >
                            Back to My Bookings
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    if (!booking) {
        return null;
    }

    const ground = booking.ground;
    const images = ground?.images || [];
    const hasImages = images.length > 0;

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
                                onClick={() => navigate('/my_bookings')}
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
                        </div>
                        <div className="flex items-center space-x-2 sm:space-x-4">
                            {user ? (
                                <div className="hidden sm:flex items-center space-x-3">
                                    <Link
                                        to="/profile"
                                        className="flex items-center space-x-2 bg-green-50 px-3 sm:px-4 py-2 rounded-full hover:bg-green-100 transition-colors cursor-pointer"
                                    >
                                        <UserIcon className="h-5 w-5 text-green-600" />
                                        <span className="text-xs sm:text-sm font-medium text-gray-700 hidden lg:inline">{user.name}</span>
                                    </Link>
                                    <a
                                        href="/logout"
                                        className="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 hover:text-green-600 transition-colors"
                                    >
                                        Logout
                                    </a>
                                </div>
                            ) : null}
                        </div>
                    </div>
                    
                    {/* Mobile Menu */}
                    {mobileMenuOpen && (
                        <div className="md:hidden pb-4 border-t border-gray-200 mt-2 pt-4">
                            <div className="space-y-3">
                                <Link
                                    to="/grounds"
                                    onClick={() => setMobileMenuOpen(false)}
                                    className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors"
                                >
                                    Grounds
                                </Link>
                                {user && (
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
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </nav>

            {/* Booking Details Content */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Booking Info Card */}
                        <div className="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                            <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
                                <div className="flex-1">
                                    <h1 className="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                                        {ground?.name || 'Ground Booking'}
                                    </h1>
                                    <p className="text-sm text-gray-600">
                                        <MapPinIcon className="h-4 w-4 inline mr-1" />
                                        {ground?.location || 'Location not specified'}
                                    </p>
                                </div>
                                <div className="flex flex-row sm:flex-col sm:items-end gap-2 flex-shrink-0">
                                    <span className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold ${getStatusColor(booking.booking_status)}`}>
                                        {booking.booking_status?.charAt(0).toUpperCase() + booking.booking_status?.slice(1) || 'Unknown'}
                                    </span>
                                    {booking.payment && (
                                        <span className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs sm:text-sm font-semibold ${getPaymentStatusColor(booking.payment.payment_status)}`}>
                                            Payment: {booking.payment.payment_status?.charAt(0).toUpperCase() + booking.payment.payment_status?.slice(1) || 'Unknown'}
                                        </span>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                                <div className="flex items-center space-x-3">
                                    <div className="bg-green-100 p-3 rounded-lg">
                                        <CalendarIcon className="h-6 w-6 text-green-600" />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-600">Booking Date</p>
                                        <p className="text-lg font-semibold text-gray-900">
                                            {formatDate(booking.booking_date)}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex items-center space-x-3">
                                    <div className="bg-blue-100 p-3 rounded-lg">
                                        <ClockIcon className="h-6 w-6 text-blue-600" />
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-xs text-gray-600 mb-1">Time Slot{booking.details && booking.details.length > 1 ? 's' : ''}</p>
                                        {booking.details && booking.details.length > 0 ? (
                                            <div className="flex flex-wrap gap-2">
                                                {booking.details.map((detail, index) => (
                                                    <span 
                                                        key={detail.id || index}
                                                        className="inline-block px-3 py-1 bg-blue-50 text-blue-700 rounded-md text-sm font-semibold border border-blue-200"
                                                    >
                                                        {detail.time_slot || detail.booking_time || 'N/A'}
                                                    </span>
                                                ))}
                                            </div>
                                        ) : (
                                            <p className="text-lg font-semibold text-gray-900">N/A</p>
                                        )}
                                    </div>
                                </div>
                                <div className="flex items-center space-x-3">
                                    <div className="bg-purple-100 p-3 rounded-lg">
                                        <DocumentTextIcon className="h-6 w-6 text-purple-600" />
                                    </div>
                                    <div>
                                        <p className="text-xs text-gray-600">Booking ID</p>
                                        <p className="text-lg font-semibold text-gray-900">{booking.booking_sku}</p>
                                    </div>
                                </div>
                                {booking.details && booking.details.length > 0 && (
                                    <div className="flex items-center space-x-3">
                                        <div className="bg-orange-100 p-3 rounded-lg">
                                            <ClockIcon className="h-6 w-6 text-orange-600" />
                                        </div>
                                        <div>
                                            <p className="text-xs text-gray-600">Total Duration</p>
                                            <p className="text-lg font-semibold text-gray-900">
                                                {booking.details.reduce((sum, detail) => sum + (detail.duration || 0), 0)} hours
                                            </p>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {ground?.description && (
                                <div className="mb-6">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                                    <p className="text-gray-600">{ground.description}</p>
                                </div>
                            )}

                            {ground?.features && ground.features.length > 0 && (
                                <div className="mb-6">
                                    <h3 className="text-lg font-semibold text-gray-900 mb-2">Features</h3>
                                    <div className="flex flex-wrap gap-2">
                                        {ground.features.map((feature, index) => (
                                            <span
                                                key={index}
                                                className="px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-medium"
                                            >
                                                {feature}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* Booking Details Table */}
                            {booking.details && booking.details.length > 0 && (
                                <div>
                                    <h3 className="text-lg font-semibold text-gray-900 mb-4">Time Slots Details</h3>
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Time Slot
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Duration
                                                    </th>
                                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Price
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {booking.details.map((detail, index) => (
                                                    <tr key={detail.id || index}>
                                                        <td className="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            {detail.time_slot || detail.booking_time || 'N/A'}
                                                        </td>
                                                        <td className="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                                            {detail.duration || 0} hours
                                                        </td>
                                                        <td className="px-4 py-4 whitespace-nowrap text-sm text-gray-600">
                                                            ₹{parseFloat(detail.slot?.price_per_slot || detail.price || 0).toFixed(2)}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Payment Summary Card */}
                        <div className="bg-white rounded-xl shadow-lg p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">Payment Summary</h3>
                            <div className="space-y-3 mb-4">
                                <div className="flex justify-between">
                                    <span className="text-gray-600">Total Amount</span>
                                    <span className="text-2xl font-bold text-green-600">
                                        ₹{parseFloat(booking.amount || 0).toFixed(2)}
                                    </span>
                                </div>
                                {booking.payment && (
                                    <>
                                        <div className="pt-3 border-t border-gray-200">
                                            <div className="flex justify-between text-sm mb-2">
                                                <span className="text-gray-600">Payment Method</span>
                                                <span className="font-medium text-gray-900">
                                                    {booking.payment.payment_method === 'online' ? 'Online' : 'Offline'}
                                                </span>
                                            </div>
                                            <div className="flex justify-between text-sm mb-2">
                                                <span className="text-gray-600">Payment Status</span>
                                                <span className={`font-medium px-2 py-1 rounded ${getPaymentStatusColor(booking.payment.payment_status)}`}>
                                                    {booking.payment.payment_status?.charAt(0).toUpperCase() + booking.payment.payment_status?.slice(1) || 'Unknown'}
                                                </span>
                                            </div>
                                            {booking.payment.transaction_id && (
                                                <div className="flex justify-between text-sm">
                                                    <span className="text-gray-600">Transaction ID</span>
                                                    <span className="font-medium text-gray-900">
                                                        {booking.payment.transaction_id}
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </>
                                )}
                            </div>
                            <button
                                onClick={downloadInvoice}
                                className="w-full px-4 py-3 rounded-lg font-semibold border-2 border-gray-300 text-gray-700 hover:border-green-500 hover:text-green-600 transition-all text-center flex items-center justify-center"
                            >
                                <PrinterIcon className="h-5 w-5 mr-2" />
                                Download Invoice
                            </button>
                        </div>

                        {/* Ground Images Card */}
                        {hasImages && (
                            <div className="bg-white rounded-xl shadow-lg p-6">
                                <h3 className="text-lg font-semibold text-gray-900 mb-4">Ground Images</h3>
                                <div className="relative">
                                    <img
                                        src={images[selectedImageIndex]?.image_url || '/images/placeholder.jpg'}
                                        alt={ground?.name || 'Ground'}
                                        className="w-full h-64 object-cover rounded-lg mb-4"
                                    />
                                    {images.length > 1 && (
                                        <div className="flex space-x-2 overflow-x-auto">
                                            {images.map((image, index) => (
                                                <img
                                                    key={image.id || index}
                                                    src={image.image_url}
                                                    alt={`${ground?.name || 'Ground'} ${index + 1}`}
                                                    className={`w-20 h-20 object-cover rounded cursor-pointer border-2 ${
                                                        selectedImageIndex === index
                                                            ? 'border-green-500'
                                                            : 'border-gray-200'
                                                    }`}
                                                    onClick={() => setSelectedImageIndex(index)}
                                                />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default BookingDetailsPage;
