import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import {
    HomeIcon,
    MapPinIcon,
    CalendarIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
    ArrowLeftIcon,
    CreditCardIcon,
    BanknotesIcon,
    UserIcon,
    Bars3Icon,
} from '@heroicons/react/24/outline';
import axios from 'axios';

function BookingPage() {
    const navigate = useNavigate();
    const location = useLocation();
    const [user, setUser] = useState(null);
    const [ground, setGround] = useState(null);
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [paymentMethod, setPaymentMethod] = useState('online'); // 'online' or 'offline'
    const [bookingData, setBookingData] = useState(null);
    const [error, setError] = useState(null);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    useEffect(() => {
        fetchUserData();
        
        // Get booking data from location state or URL params
        if (location.state) {
            setBookingData(location.state);
            fetchGroundDetails(location.state.ground_id);
        } else {
            setError('No booking data found. Please go back and select slots again.');
            setLoading(false);
        }

        // Load Razorpay script
        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        document.body.appendChild(script);

        return () => {
            // Cleanup: remove script when component unmounts
            if (document.body.contains(script)) {
                document.body.removeChild(script);
            }
        };
    }, [location]);

    const fetchUserData = async () => {
        try {
            const response = await axios.get('/api/user');
            setUser(response.data);
        } catch (error) {
            console.log('User not authenticated');
            navigate('/login');
        }
    };

    const fetchGroundDetails = async (groundId) => {
        try {
            setLoading(true);
            const response = await axios.get(`/api/grounds/${groundId}`);
            if (response.data.success && response.data.ground) {
                setGround(response.data.ground);
            } else {
                setError('Ground not found');
            }
        } catch (error) {
            console.error('Error fetching ground details:', error);
            setError('Failed to load ground details.');
        } finally {
            setLoading(false);
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

    const handleOfflinePayment = async () => {
        if (!bookingData) {
            alert('No booking data found');
            return;
        }

        try {
            setProcessing(true);
            setError(null);

            // Create booking with offline payment
            const response = await axios.post('/book-ground-offline', {
                ground_id: bookingData.ground_id,
                date: bookingData.date,
                slot_ids: bookingData.slot_ids,
                time_slots: bookingData.time_slots,
                total_price: bookingData.total_price,
                payment_method: 'offline'
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.data.success) {
                // Clear localStorage selection after successful booking
                if (bookingData && bookingData.ground_id) {
                    const storageKey = `ground_${bookingData.ground_id}_booking_selection`;
                    localStorage.removeItem(storageKey);
                }
                // Redirect to booking confirmation
                if (response.data.booking_sku) {
                    window.location.href = `/my-bookings/${response.data.booking_sku}`;
                } else {
                    window.location.href = '/my_bookings';
                }
            } else {
                setError(response.data.message || 'Failed to create booking. Please try again.');
            }
        } catch (error) {
            console.error('Offline booking error:', error);
            const errorMessage = error.response?.data?.message || error.message || 'Failed to create booking. Please try again.';
            setError(errorMessage);
        } finally {
            setProcessing(false);
        }
    };

    const handleOnlinePayment = async () => {
        if (!bookingData) {
            alert('No booking data found');
            return;
        }

        try {
            setProcessing(true);
            setError(null);

            // Create Razorpay order
            const response = await axios.post('/book-ground', {
                ground_id: bookingData.ground_id,
                date: bookingData.date,
                slot_ids: bookingData.slot_ids,
                time_slots: bookingData.time_slots,
                total_price: bookingData.total_price
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.data.success && response.data.order_id) {
                // Wait for Razorpay script to load
                if (!window.Razorpay) {
                    setError('Payment gateway is loading. Please wait a moment and try again.');
                    setProcessing(false);
                    return;
                }

                // Initialize Razorpay payment
                const options = {
                    key: response.data.key || 'rzp_test_AO2NIeW6cw7UhG', // Razorpay key from config
                    amount: response.data.amount,
                    currency: response.data.currency || 'INR',
                    name: 'GetBooking',
                    description: `Booking for ${ground?.name || 'Ground'}`,
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
                                // Clear localStorage selection after successful booking
                                if (bookingData && bookingData.ground_id) {
                                    const storageKey = `ground_${bookingData.ground_id}_booking_selection`;
                                    localStorage.removeItem(storageKey);
                                }
                                // Redirect to booking confirmation
                                if (verifyResponse.data.booking_sku) {
                                    window.location.href = `/my-bookings/${verifyResponse.data.booking_sku}`;
                                } else if (verifyResponse.data.booking_id) {
                                    window.location.href = '/my_bookings';
                                } else {
                                    window.location.href = '/my_bookings';
                                }
                            } else {
                                alert('Payment verification failed: ' + (verifyResponse.data.message || 'Unknown error'));
                                setProcessing(false);
                            }
                        } catch (error) {
                            console.error('Payment verification error:', error);
                            console.error('Error response:', error.response?.data);
                            
                            let errorMessage = 'Payment verification failed. Please contact support.';
                            if (error.response?.data?.message) {
                                errorMessage = error.response.data.message;
                            }
                            
                            alert(errorMessage);
                            setProcessing(false);
                            
                            // Refresh page to show updated booking status
                            setTimeout(() => {
                                window.location.href = '/my_bookings';
                            }, 2000);
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
                            setProcessing(false);
                        }
                    }
                };

                const razorpay = new window.Razorpay(options);
                razorpay.on('payment.failed', function (response) {
                    setError('Payment failed: ' + (response.error?.description || 'Unknown error'));
                    setProcessing(false);
                });
                razorpay.open();
            } else {
                setError(response.data.message || 'Failed to initialize payment. Please try again.');
                setProcessing(false);
            }
        } catch (error) {
            console.error('Online payment error:', error);
            console.error('Error response:', error.response?.data);
            let errorMessage = 'Failed to initialize payment. Please try again.';
            
            if (error.response) {
                // Server responded with error
                const responseData = error.response.data;
                errorMessage = responseData?.message || responseData?.error || `Server error: ${error.response.status}`;
                
                // Log debug info if available
                if (responseData?.debug) {
                    console.error('Debug info:', responseData.debug);
                }
            } else if (error.request) {
                // Request made but no response
                errorMessage = 'No response from server. Please check your internet connection.';
            } else {
                // Error setting up request
                errorMessage = error.message || 'Failed to initialize payment. Please try again.';
            }
            
            setError(errorMessage);
            setProcessing(false);
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

    if (error && !bookingData) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex items-center justify-center">
                <div className="text-center bg-white p-8 rounded-xl shadow-lg max-w-md">
                    <XMarkIcon className="h-16 w-16 text-red-500 mx-auto mb-4" />
                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Error</h2>
                    <p className="text-gray-600 mb-6">{error}</p>
                    <button
                        onClick={() => navigate('/home')}
                        className="px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all"
                    >
                        Go Back Home
                    </button>
                </div>
            </div>
        );
    }

    const selectedSlots = bookingData?.selectedSlots || [];
    const totalPrice = selectedSlots.reduce((total, slot) => total + parseFloat(slot.price || 0), 0);

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
                                onClick={() => {
                                    // Set scroll flag for automatic scroll when user comes back
                                    if (bookingData && bookingData.ground_id) {
                                        sessionStorage.setItem(`scroll_to_booking_${bookingData.ground_id}`, 'true');
                                        navigate(`/grounds/${bookingData.ground_id}`);
                                    } else {
                                        navigate(-1);
                                    }
                                }}
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
                                        className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-green-600 transition-colors"
                                    >
                                        Bookings
                                    </Link>
                                )}
                            </nav>
                        </div>
                        {user && (
                            <div className="hidden sm:flex items-center space-x-2 sm:space-x-4">
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
                        )}
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

            {/* Booking Page Content */}
            <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div className="bg-white rounded-xl shadow-lg p-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-6">Complete Your Booking</h1>

                    {error && (
                        <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <p className="text-red-800">{error}</p>
                        </div>
                    )}

                    {/* Booking Summary */}
                    <div className="mb-8 bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-6 border-2 border-green-200">
                        <h2 className="text-xl font-bold text-gray-900 mb-4">Booking Summary</h2>
                        
                        {ground && (
                            <div className="mb-4">
                                <p className="text-sm text-gray-600">Ground Name</p>
                                <p className="text-lg font-semibold text-gray-900">{ground.name}</p>
                                <p className="text-sm text-gray-600 mt-1">
                                    <MapPinIcon className="h-4 w-4 inline mr-1" />
                                    {ground.location}
                                </p>
                            </div>
                        )}

                        {bookingData?.date && (
                            <div className="mb-4">
                                <p className="text-sm text-gray-600">Selected Date</p>
                                <p className="text-lg font-semibold text-gray-900">
                                    {new Date(bookingData.date).toLocaleDateString('en-US', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })}
                                </p>
                            </div>
                        )}

                        {selectedSlots.length > 0 && (
                            <>
                                <div className="mb-4">
                                    <p className="text-sm text-gray-600 mb-2">Selected Time Slots ({selectedSlots.length})</p>
                                    <div className="space-y-2 max-h-48 overflow-y-auto">
                                        {selectedSlots.map((slot, index) => {
                                            const timeParts = slot.time ? slot.time.split('-') : [];
                                            const startTime = timeParts[0] || '';
                                            const endTime = timeParts[1] || '';
                                            return (
                                                <div key={slot.id || index} className="bg-white rounded-lg p-3 border border-green-200">
                                                    <div className="flex items-center justify-between">
                                                        <div>
                                                            <p className="text-sm font-semibold text-gray-900">
                                                                {startTime && endTime 
                                                                    ? `${formatTime(startTime)} - ${formatTime(endTime)}`
                                                                    : slot.time || 'Slot'}
                                                            </p>
                                                            {slot.hours && (
                                                                <p className="text-xs text-gray-500">
                                                                    {slot.hours} {slot.hours === 1 ? 'hour' : 'hours'}
                                                                </p>
                                                            )}
                                                        </div>
                                                        <p className="text-sm font-bold text-green-600">
                                                            ₹{parseFloat(slot.price || 0).toFixed(2)}
                                                        </p>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                                <div className="border-t border-green-200 pt-3">
                                    <div className="flex items-center justify-between">
                                        <p className="text-lg font-semibold text-gray-900">Total Amount</p>
                                        <p className="text-2xl font-bold text-green-600">
                                            ₹{totalPrice.toFixed(2)}
                                        </p>
                                    </div>
                                </div>
                            </>
                        )}
                    </div>

                    {/* Payment Method Selection */}
                    <div className="mb-8">
                        <h2 className="text-xl font-bold text-gray-900 mb-4">Select Payment Method</h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Online Payment Option */}
                            <button
                                onClick={() => setPaymentMethod('online')}
                                disabled={processing}
                                className={`p-6 rounded-xl border-2 transition-all text-left ${
                                    paymentMethod === 'online'
                                        ? 'border-green-500 bg-green-50 shadow-lg ring-2 ring-green-300'
                                        : 'border-gray-200 hover:border-green-300 hover:bg-green-50'
                                } ${processing ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                                <div className="flex items-center space-x-4">
                                    <div className={`p-3 rounded-lg ${
                                        paymentMethod === 'online' ? 'bg-green-500' : 'bg-gray-200'
                                    }`}>
                                        <CreditCardIcon className={`h-6 w-6 ${
                                            paymentMethod === 'online' ? 'text-white' : 'text-gray-600'
                                        }`} />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="text-lg font-semibold text-gray-900">Online Payment</h3>
                                        <p className="text-sm text-gray-600 mt-1">Pay securely with Razorpay</p>
                                        <p className="text-xs text-gray-500 mt-2">
                                            • Credit/Debit Cards
                                            • UPI
                                            • Net Banking
                                            • Wallets
                                        </p>
                                    </div>
                                    {paymentMethod === 'online' && (
                                        <CheckCircleIcon className="h-6 w-6 text-green-500" />
                                    )}
                                </div>
                            </button>

                            {/* Offline Payment Option */}
                            <button
                                onClick={() => setPaymentMethod('offline')}
                                disabled={processing}
                                className={`p-6 rounded-xl border-2 transition-all text-left ${
                                    paymentMethod === 'offline'
                                        ? 'border-green-500 bg-green-50 shadow-lg ring-2 ring-green-300'
                                        : 'border-gray-200 hover:border-green-300 hover:bg-green-50'
                                } ${processing ? 'opacity-50 cursor-not-allowed' : ''}`}
                            >
                                <div className="flex items-center space-x-4">
                                    <div className={`p-3 rounded-lg ${
                                        paymentMethod === 'offline' ? 'bg-green-500' : 'bg-gray-200'
                                    }`}>
                                        <BanknotesIcon className={`h-6 w-6 ${
                                            paymentMethod === 'offline' ? 'text-white' : 'text-gray-600'
                                        }`} />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="text-lg font-semibold text-gray-900">Offline Payment</h3>
                                        <p className="text-sm text-gray-600 mt-1">Pay at the ground</p>
                                        <p className="text-xs text-gray-500 mt-2">
                                            • Cash payment
                                            • Pay when you arrive
                                            • Confirmation required
                                        </p>
                                    </div>
                                    {paymentMethod === 'offline' && (
                                        <CheckCircleIcon className="h-6 w-6 text-green-500" />
                                    )}
                                </div>
                            </button>
                        </div>
                    </div>

                    {/* Payment Button */}
                    <div className="flex flex-col sm:flex-row gap-4">
                        <button
                            onClick={paymentMethod === 'online' ? handleOnlinePayment : handleOfflinePayment}
                            disabled={processing || !bookingData}
                            className={`flex-1 py-4 px-6 rounded-lg font-semibold text-lg transition-all ${
                                processing || !bookingData
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    : paymentMethod === 'online'
                                    ? 'bg-gradient-to-r from-green-500 to-blue-500 text-white hover:from-green-600 hover:to-blue-600 shadow-lg hover:shadow-xl'
                                    : 'bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 shadow-lg hover:shadow-xl'
                            }`}
                        >
                            {processing ? (
                                <span className="flex items-center justify-center">
                                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                                    Processing...
                                </span>
                            ) : paymentMethod === 'online' ? (
                                'Pay ₹' + totalPrice.toFixed(2) + ' Online'
                            ) : (
                                'Confirm Offline Payment'
                            )}
                        </button>
                        <button
                            onClick={() => {
                                // Set scroll flag for automatic scroll when user comes back
                                if (bookingData && bookingData.ground_id) {
                                    sessionStorage.setItem(`scroll_to_booking_${bookingData.ground_id}`, 'true');
                                    navigate(`/grounds/${bookingData.ground_id}`);
                                } else {
                                    navigate(-1);
                                }
                            }}
                            disabled={processing}
                            className="px-6 py-4 rounded-lg font-semibold border-2 border-gray-300 text-gray-700 hover:border-gray-400 transition-all"
                        >
                            Cancel
                        </button>
                    </div>

                    {/* Important Notes */}
                    <div className="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p className="text-sm text-yellow-800">
                            <strong>Note:</strong> {paymentMethod === 'online' 
                                ? 'Your booking will be confirmed immediately after successful payment.'
                                : 'Your booking will be confirmed after ground verification. Please arrive on time with payment ready.'}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default BookingPage;

