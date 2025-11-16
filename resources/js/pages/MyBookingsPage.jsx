import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
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
    EyeIcon,
    MapPinIcon,
    UserIcon,
    Bars3Icon,
} from '@heroicons/react/24/outline';
import axios from 'axios';

function MyBookingsPage() {
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filter, setFilter] = useState('all'); // 'all', 'upcoming', 'completed', 'cancelled', 'pending'
    const [processingPayment, setProcessingPayment] = useState(null);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [cancelDialog, setCancelDialog] = useState(null); // { booking: {...}, show: true }
    const [successDialog, setSuccessDialog] = useState(null); // { message: '...', refundInfo: '...', show: true }

    useEffect(() => {
        // Fetch user data first, then bookings
        const initializePage = async () => {
            try {
                const userResponse = await axios.get('/api/user');
                if (userResponse.data && userResponse.data.id) {
                    setUser(userResponse.data);
                    // User is authenticated, fetch bookings
                    await fetchBookings();
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

        initializePage();
        
        // Load Razorpay script
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.body.appendChild(script);

        return () => {
            if (document.body.contains(script)) {
                document.body.removeChild(script);
            }
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const fetchBookings = async () => {
        try {
            setLoading(true);
            setError(null);
            console.log('Fetching bookings from /api/bookings...');
            const response = await axios.get('/api/bookings', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            console.log('Bookings API response:', response);
            console.log('Bookings data:', response.data);
            
            if (response.data && response.data.success) {
                const bookingsData = response.data.bookings || [];
                console.log('Setting bookings:', bookingsData);
                setBookings(bookingsData);
            } else {
                console.warn('Bookings response format unexpected:', response.data);
                setBookings([]);
            }
        } catch (error) {
            console.error('Error fetching bookings:', error);
            console.error('Error response:', error.response);
            if (error.response && error.response.status === 401) {
                // Unauthorized - redirect to login
                console.log('Unauthorized, redirecting to login');
                navigate('/login');
            } else {
                const errorMessage = error.response?.data?.message || error.message || 'Failed to load bookings. Please try again later.';
                console.error('Setting error:', errorMessage);
                setError(errorMessage);
                setBookings([]);
            }
        } finally {
            setLoading(false);
            console.log('Loading set to false');
        }
    };

    const handleCompletePayment = async (booking) => {
        if (!booking) return;

        try {
            setProcessingPayment(booking.id);
            setError(null);

            // Create Razorpay order for existing booking
            const response = await axios.post(`/complete-payment/${booking.id}`, {}, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('Complete payment response:', response.data);

            if (response.data.success && response.data.order_id) {
                // Wait for Razorpay script to load (with timeout)
                let attempts = 0;
                const maxAttempts = 20; // 2 seconds max wait
                while (!window.Razorpay && attempts < maxAttempts) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                    attempts++;
                }
                
                if (!window.Razorpay) {
                    setError('Payment gateway is loading. Please wait a moment and try again.');
                    setProcessingPayment(null);
                    return;
                }

                // Validate required fields
                if (!response.data.key) {
                    setError('Payment gateway key is missing. Please contact support.');
                    setProcessingPayment(null);
                    return;
                }

                if (!response.data.order_id) {
                    setError('Payment order ID is missing. Please try again.');
                    setProcessingPayment(null);
                    return;
                }

                if (!response.data.amount || response.data.amount < 100) {
                    setError('Invalid payment amount. Please contact support.');
                    setProcessingPayment(null);
                    return;
                }

                // Initialize Razorpay payment
                const options = {
                    key: response.data.key,
                    amount: parseInt(response.data.amount), // Ensure it's an integer
                    currency: response.data.currency || 'INR',
                    name: 'GetBooking',
                    description: `Payment for booking ${booking.booking_sku}`,
                    order_id: response.data.order_id,
                    handler: async function (paymentResponse) {
                        try {
                            // Verify payment on backend
                            const verifyResponse = await axios.post('/payment-callback', {
                                razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                razorpay_order_id: paymentResponse.razorpay_order_id,
                                razorpay_signature: paymentResponse.razorpay_signature
                            });

                            if (verifyResponse.data.success) {
                                // Refresh bookings list
                                await fetchBookings();
                                alert('Payment completed successfully!');
                            } else {
                                alert('Payment verification failed: ' + (verifyResponse.data.message || 'Unknown error'));
                            }
                        } catch (error) {
                            console.error('Payment verification error:', error);
                            console.error('Error response:', error.response?.data);
                            
                            let errorMessage = 'Payment verification failed. Please contact support.';
                            if (error.response?.data?.message) {
                                errorMessage = error.response.data.message;
                            }
                            
                            alert(errorMessage);
                            
                            // Refresh bookings list to show current status
                            await fetchBookings();
                        } finally {
                            setProcessingPayment(null);
                        }
                    },
                    prefill: {
                        name: user?.name || '',
                        email: user?.email || '',
                        contact: user?.phone || ''
                    },
                    theme: {
                        color: '#22c55e'
                    },
                    modal: {
                        ondismiss: function() {
                            setProcessingPayment(null);
                        }
                    }
                };

                try {
                    const razorpay = new window.Razorpay(options);
                    razorpay.on('payment.failed', function (response) {
                        console.error('Razorpay payment failed:', response);
                        setError('Payment failed: ' + (response.error?.description || 'Unknown error'));
                        setProcessingPayment(null);
                    });
                    razorpay.open();
                } catch (error) {
                    console.error('Error opening Razorpay checkout:', error);
                    console.error('Razorpay options:', options);
                    setError('Failed to open payment window. Please try again or contact support.');
                    setProcessingPayment(null);
                }
            } else {
                setError(response.data.message || 'Failed to initialize payment. Please try again.');
                setProcessingPayment(null);
            }
        } catch (error) {
            console.error('Payment error:', error);
            const errorMessage = error.response?.data?.message || error.message || 'Failed to process payment. Please try again.';
            setError(errorMessage);
            setProcessingPayment(null);
        }
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

    const handleCancelBooking = (booking) => {
        // Check if booking can be cancelled
        const bookingDate = new Date(booking.booking_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (bookingDate < today) {
            alert('Past bookings cannot be cancelled.');
            return;
        }
        
        if (booking.booking_status === 'cancelled') {
            alert('This booking is already cancelled.');
            return;
        }
        
        if (booking.booking_status === 'completed') {
            alert('Completed bookings cannot be cancelled.');
            return;
        }
        
        // Show custom confirmation dialog
        setCancelDialog({ booking, show: true });
    };

    const confirmCancelBooking = async () => {
        if (!cancelDialog || !cancelDialog.booking) return;
        
        const booking = cancelDialog.booking;
        
        try {
            const response = await axios.post(`/user/bookings/${booking.id}/cancel`, {}, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.data.success) {
                // Close cancel dialog
                setCancelDialog(null);
                
                // Prepare success message
                let refundInfo = '';
                if (response.data.refundPercentage > 0) {
                    const refundAmount = parseFloat(response.data.refundAmount || 0);
                    const refundPercentage = parseFloat(response.data.refundPercentage || 0);
                    refundInfo = `Refund: ${refundPercentage}% (₹${refundAmount.toFixed(2)})`;
                } else {
                    refundInfo = 'No refund applicable.';
                }
                
                // Show custom success dialog
                setSuccessDialog({
                    message: 'Booking cancelled successfully!',
                    refundInfo: refundInfo,
                    show: true
                });
                
                // Refresh bookings list
                await fetchBookings();
            } else {
                alert('Failed to cancel booking: ' + (response.data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error cancelling booking:', error);
            const errorMessage = error.response?.data?.message || error.message || 'Failed to cancel booking. Please try again.';
            alert('Error: ' + errorMessage);
        }
    };

    const closeCancelDialog = () => {
        setCancelDialog(null);
    };

    const closeSuccessDialog = () => {
        setSuccessDialog(null);
    };

    const filteredBookings = bookings.filter(booking => {
        if (filter === 'all') return true;
        if (filter === 'pending') {
            return booking.payment?.payment_status === 'pending' || booking.booking_status === 'pending';
        }
        return booking.booking_status?.toLowerCase() === filter;
    });
    
    const canCancelBooking = (booking) => {
        if (!booking) return false;
        if (booking.booking_status === 'cancelled') return false;
        if (booking.booking_status === 'completed') return false;
        
        // Check if booking date is in the future
        const bookingDate = new Date(booking.booking_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        return bookingDate >= today;
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex items-center justify-center">
                <div className="text-center">
                    <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
                    <p className="text-gray-600">Loading bookings...</p>
                </div>
            </div>
        );
    }

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
                                    className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-green-600 transition-colors"
                                >
                                    Grounds
                                </Link>
                                {user && (
                                    <Link
                                        to="/my_bookings"
                                        className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-green-600 transition-colors border-b-2 border-green-600"
                                    >
                                        Bookings
                                    </Link>
                                )}
                            </nav>
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
                                            className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors border-l-4 border-green-600"
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

            {/* Bookings Page Content */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
                <div className="mb-4 sm:mb-6">
                    <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">My Bookings</h1>
                    <p className="text-sm sm:text-base text-gray-600">View and manage your ground bookings</p>
                </div>

                {error && (
                    <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p className="text-red-800">{error}</p>
                    </div>
                )}

                {/* Filter Tabs */}
                <div className="bg-white rounded-xl shadow-lg p-3 sm:p-4 mb-4 sm:mb-6">
                    <div className="flex flex-wrap gap-2">
                        {['all', 'upcoming', 'pending', 'completed', 'cancelled'].map((filterOption) => (
                            <button
                                key={filterOption}
                                onClick={() => setFilter(filterOption)}
                                className={`px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-medium transition-all ${
                                    filter === filterOption
                                        ? 'bg-gradient-to-r from-green-500 to-blue-500 text-white shadow-lg'
                                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                }`}
                            >
                                {filterOption.charAt(0).toUpperCase() + filterOption.slice(1)}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Bookings List */}
                {loading ? null : filteredBookings.length === 0 ? (
                    <div className="bg-white rounded-xl shadow-lg p-12 text-center">
                        <CalendarIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                        <h3 className="text-xl font-semibold text-gray-900 mb-2">No Bookings Found</h3>
                        <p className="text-gray-600 mb-6">
                            {filter === 'all' 
                                ? "You don't have any bookings yet." 
                                : `No ${filter} bookings found.`}
                        </p>
                        {!error && (
                            <Link
                                to="/grounds"
                                className="inline-block px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all"
                            >
                                Browse Grounds
                            </Link>
                        )}
                    </div>
                ) : (
                    <div className="space-y-4">
                        {filteredBookings.map((booking) => {
                            const hasPendingPayment = booking.payment?.payment_status === 'pending' || 
                                                      (!booking.payment && booking.booking_status === 'pending');
                            const isProcessing = processingPayment === booking.id;

                            return (
                                <div key={booking.id} className="bg-white rounded-xl shadow-lg p-4 sm:p-6 hover:shadow-xl transition-shadow">
                                    <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                        {/* Booking Info */}
                                        <div className="flex-1">
                                            <div className="flex items-start justify-between mb-3 sm:mb-4">
                                                <div className="flex-1 pr-2">
                                                    <h3 className="text-lg sm:text-xl font-bold text-gray-900 mb-2">
                                                        {booking.ground?.name || 'Ground Booking'}
                                                    </h3>
                                                    <p className="text-sm text-gray-600 mb-1">
                                                        <MapPinIcon className="h-4 w-4 inline mr-1" />
                                                        {booking.ground?.location || 'Location not specified'}
                                                    </p>
                                                    <p className="text-sm text-gray-600">
                                                        Booking ID: <span className="font-semibold">{booking.booking_sku}</span>
                                                    </p>
                                                </div>
                                                <div className="flex flex-col items-end gap-1 sm:gap-2 flex-shrink-0">
                                                    <span className={`px-2 sm:px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(booking.booking_status)}`}>
                                                        {booking.booking_status?.charAt(0).toUpperCase() + booking.booking_status?.slice(1) || 'Unknown'}
                                                    </span>
                                                    {booking.payment && (
                                                        <span className={`px-2 sm:px-3 py-1 rounded-full text-xs font-semibold ${getPaymentStatusColor(booking.payment.payment_status)}`}>
                                                            Payment: {booking.payment.payment_status?.charAt(0).toUpperCase() + booking.payment.payment_status?.slice(1) || 'Unknown'}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                <div className="flex items-center space-x-2">
                                                    <CalendarIcon className="h-5 w-5 text-green-600" />
                                                    <div>
                                                        <p className="text-xs text-gray-600">Booking Date</p>
                                                        <p className="text-sm font-semibold text-gray-900">
                                                            {formatDate(booking.booking_date)}
                                                        </p>
                                                    </div>
                                                </div>
                                                {booking.details && booking.details.length > 0 && (
                                                    <div className="flex items-start space-x-2">
                                                        <ClockIcon className="h-5 w-5 text-blue-600 mt-1" />
                                                        <div className="flex-1">
                                                            <p className="text-xs text-gray-600 mb-1">Time Slot{booking.details.length > 1 ? 's' : ''}</p>
                                                            {booking.details.length === 1 ? (
                                                                <p className="text-sm font-semibold text-gray-900">
                                                                    {booking.details[0].time_slot || booking.details[0].booking_time || 'N/A'}
                                                                </p>
                                                            ) : (
                                                                <div className="flex flex-wrap gap-2">
                                                                    {booking.details.map((detail, index) => (
                                                                        <span 
                                                                            key={detail.id || index}
                                                                            className="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded-md text-sm font-semibold border border-blue-200"
                                                                        >
                                                                            {detail.time_slot || detail.booking_time || 'N/A'}
                                                                        </span>
                                                                    ))}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="flex items-center justify-between pt-4 border-t border-gray-200">
                                                <div>
                                                    <p className="text-sm text-gray-600">Total Amount</p>
                                                    <p className="text-xl font-bold text-green-600">₹{parseFloat(booking.amount || 0).toFixed(2)}</p>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Actions */}
                                        <div className="flex flex-col gap-2 w-full md:w-auto md:min-w-[200px]">
                                            {hasPendingPayment && booking.payment?.payment_method === 'online' && (
                                                <button
                                                    onClick={() => handleCompletePayment(booking)}
                                                    disabled={isProcessing}
                                                    className={`w-full px-4 py-3 rounded-lg font-semibold transition-all ${
                                                        isProcessing
                                                            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                                            : 'bg-gradient-to-r from-green-500 to-blue-500 text-white hover:from-green-600 hover:to-blue-600 shadow-lg hover:shadow-xl'
                                                    }`}
                                                >
                                                    {isProcessing ? (
                                                        <span className="flex items-center justify-center">
                                                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                                            Processing...
                                                        </span>
                                                    ) : (
                                                        <>
                                                            <CreditCardIcon className="h-5 w-5 inline mr-2" />
                                                            Complete Payment
                                                        </>
                                                    )}
                                                </button>
                                            )}
                                            
                                            {hasPendingPayment && booking.payment?.payment_method === 'offline' && (
                                                <div className="w-full px-4 py-3 rounded-lg bg-yellow-50 border border-yellow-200">
                                                    <div className="flex items-center space-x-2">
                                                        <ExclamationTriangleIcon className="h-5 w-5 text-yellow-600" />
                                                        <p className="text-sm font-semibold text-yellow-800">
                                                            Pay at Ground
                                                        </p>
                                                    </div>
                                                </div>
                                            )}

                                            {canCancelBooking(booking) && (
                                                <button
                                                    onClick={() => handleCancelBooking(booking)}
                                                    className="w-full px-4 py-3 rounded-lg font-semibold bg-red-500 text-white hover:bg-red-600 transition-all shadow-lg hover:shadow-xl"
                                                >
                                                    <XMarkIcon className="h-5 w-5 inline mr-2" />
                                                    Cancel Booking
                                                </button>
                                            )}
                                            
                                            <Link
                                                to={`/my-bookings/${booking.booking_sku}`}
                                                className="w-full px-4 py-3 rounded-lg font-semibold border-2 border-gray-300 text-gray-700 hover:border-green-500 hover:text-green-600 transition-all text-center"
                                            >
                                                <EyeIcon className="h-5 w-5 inline mr-2" />
                                                View Details
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>

            {/* Custom Cancel Booking Confirmation Dialog */}
            {cancelDialog && cancelDialog.show && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                        <div className="flex items-center justify-center mb-4">
                            <div className="bg-red-100 rounded-full p-3">
                                <XMarkIcon className="h-8 w-8 text-red-600" />
                            </div>
                        </div>
                        
                        <h3 className="text-2xl font-bold text-gray-900 text-center mb-4">
                            Cancel Booking?
                        </h3>
                        
                        <div className="bg-gray-50 rounded-lg p-4 mb-4">
                            <p className="text-sm text-gray-600 mb-3 text-center">
                                Are you sure you want to cancel this booking?
                            </p>
                            
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-sm font-semibold text-gray-700">Booking ID:</span>
                                    <span className="text-sm text-gray-900">{cancelDialog.booking.booking_sku}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-semibold text-gray-700">Ground:</span>
                                    <span className="text-sm text-gray-900">{cancelDialog.booking.ground?.name || 'N/A'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-semibold text-gray-700">Date:</span>
                                    <span className="text-sm text-gray-900">{formatDate(cancelDialog.booking.booking_date)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm font-semibold text-gray-700">Amount:</span>
                                    <span className="text-sm text-gray-900">₹{parseFloat(cancelDialog.booking.amount || 0).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                            <p className="text-xs text-yellow-800 text-center">
                                <ExclamationTriangleIcon className="h-4 w-4 inline mr-1" />
                                <strong>Note:</strong> Refund policy will apply based on cancellation time.
                            </p>
                        </div>
                        
                        <div className="flex gap-3">
                            <button
                                onClick={closeCancelDialog}
                                className="flex-1 px-4 py-3 rounded-lg font-semibold border-2 border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50 transition-all"
                            >
                                No, Keep Booking
                            </button>
                            <button
                                onClick={confirmCancelBooking}
                                className="flex-1 px-4 py-3 rounded-lg font-semibold bg-red-500 text-white hover:bg-red-600 transition-all shadow-lg hover:shadow-xl"
                            >
                                Yes, Cancel Booking
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Custom Success Dialog */}
            {successDialog && successDialog.show && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div className="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                        <div className="flex items-center justify-center mb-4">
                            <div className="bg-green-100 rounded-full p-3">
                                <CheckCircleIcon className="h-8 w-8 text-green-600" />
                            </div>
                        </div>
                        
                        <h3 className="text-2xl font-bold text-gray-900 text-center mb-4">
                            {successDialog.message || 'Success!'}
                        </h3>
                        
                        {successDialog.refundInfo && (
                            <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div className="flex items-center justify-center space-x-2">
                                    <CheckCircleIcon className="h-5 w-5 text-green-600" />
                                    <p className="text-sm font-semibold text-green-800 text-center">
                                        {successDialog.refundInfo}
                                    </p>
                                </div>
                            </div>
                        )}
                        
                        <div className="flex justify-center">
                            <button
                                onClick={closeSuccessDialog}
                                className="px-6 py-3 rounded-lg font-semibold bg-gradient-to-r from-green-500 to-blue-500 text-white hover:from-green-600 hover:to-blue-600 transition-all shadow-lg hover:shadow-xl"
                            >
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

export default MyBookingsPage;

