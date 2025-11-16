import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import {
    HomeIcon,
    MapPinIcon,
    CalendarIcon,
    ClockIcon,
    PhoneIcon,
    EnvelopeIcon,
    StarIcon,
    UserIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    BoltIcon,
    XMarkIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    PencilIcon,
    TrashIcon,
    ChatBubbleLeftRightIcon,
    Bars3Icon,
} from '@heroicons/react/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/react/24/solid';
import axios from 'axios';

function GroundDetailsPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [ground, setGround] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedImageIndex, setSelectedImageIndex] = useState(0);
    const [selectedDate, setSelectedDate] = useState('');
    const [selectedSlots, setSelectedSlots] = useState([]);
    const [availableSlots, setAvailableSlots] = useState([]);
    const [loadingSlots, setLoadingSlots] = useState(false);
    const [showBookingModal, setShowBookingModal] = useState(false);
    const [dateOptions, setDateOptions] = useState([]);
    const [isDragging, setIsDragging] = useState(false);
    const [startX, setStartX] = useState(0);
    const [scrollLeft, setScrollLeft] = useState(0);
    // Review states
    const [reviews, setReviews] = useState([]);
    const [userReview, setUserReview] = useState(null);
    const [loadingReviews, setLoadingReviews] = useState(false);
    const [showReviewForm, setShowReviewForm] = useState(false);
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewComment, setReviewComment] = useState('');
    const [editingReviewId, setEditingReviewId] = useState(null);
    const [averageRating, setAverageRating] = useState(0);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    // Reply states
    const [replyingToReviewId, setReplyingToReviewId] = useState(null);
    const [replyComment, setReplyComment] = useState('');
    const [editingReplyId, setEditingReplyId] = useState(null);
    // Toast notification states
    const [toast, setToast] = useState(null);
    // Reviews display states
    const [visibleReviewsCount, setVisibleReviewsCount] = useState(3);

    useEffect(() => {
        fetchUserData();
        fetchGroundDetails();
        generateDateOptions();
        fetchReviews();
        
        // Restore selected date from localStorage if available
        // Slots will be restored after available slots are loaded
        // NOTE: Scroll flag is ONLY set when coming from booking page (via BookingPage component)
        // We don't set scroll flag here to prevent unwanted scrolling on normal navigation
        const storageKey = `ground_${id}_booking_selection`;
        const savedSelection = localStorage.getItem(storageKey);
        
        if (savedSelection) {
            try {
                const selection = JSON.parse(savedSelection);
                // Check if the saved selection is for this ground
                if (selection.ground_id === id && selection.date) {
                    setSelectedDate(selection.date);
                }
            } catch (error) {
                console.error('Error restoring booking selection:', error);
                localStorage.removeItem(storageKey);
            }
        }
    }, [id]);

    const generateDateOptions = () => {
        const dates = [];
        const today = new Date();
        const oneMonthLater = new Date(today);
        oneMonthLater.setMonth(oneMonthLater.getMonth() + 1);

        for (let d = new Date(today); d <= oneMonthLater; d.setDate(d.getDate() + 1)) {
            const dateStr = d.toISOString().split('T')[0];
            const dayName = d.toLocaleDateString('en-US', { weekday: 'short' });
            const dayNumber = d.getDate();
            const monthName = d.toLocaleDateString('en-US', { month: 'short' });
            
            dates.push({
                date: dateStr,
                dayName,
                dayNumber,
                monthName,
                isToday: dateStr === today.toISOString().split('T')[0],
            });
        }
        setDateOptions(dates);
        
        // Auto-select today's date
        if (dates.length > 0 && !selectedDate) {
            setSelectedDate(dates[0].date);
        }
    };

    const fetchUserData = async () => {
        try {
            const response = await axios.get('/api/user');
            setUser(response.data);
        } catch (error) {
            console.log('User not authenticated or error fetching user data');
        }
    };

    const fetchGroundDetails = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await axios.get(`/api/grounds/${id}`);
            if (response.data.success && response.data.ground) {
                setGround(response.data.ground);
            } else {
                setError('Ground not found');
            }
        } catch (error) {
            console.error('Error fetching ground details:', error);
            setError('Failed to load ground details. Please try again later.');
        } finally {
            setLoading(false);
        }
    };

    const fetchAvailableSlots = async (date) => {
        if (!date) return;
        try {
            setLoadingSlots(true);
            const response = await axios.get(`/api/grounds/${id}/slots/${date}`);
            if (response.data.success && response.data.slots) {
                setAvailableSlots(response.data.slots);
            } else {
                setAvailableSlots([]);
            }
        } catch (error) {
            console.error('Error fetching available slots:', error);
            setAvailableSlots([]);
        } finally {
            setLoadingSlots(false);
        }
    };

    useEffect(() => {
        if (selectedDate) {
            fetchAvailableSlots(selectedDate);
        }
    }, [selectedDate, id]);

    // Scroll to booking section when coming back from booking page ONLY
    // This useEffect only triggers when we have the scroll flag (set from booking page)
    useEffect(() => {
        const shouldScroll = sessionStorage.getItem(`scroll_to_booking_${id}`);
        if (shouldScroll === 'true' && availableSlots.length > 0 && !loadingSlots && !loading) {
            // Only scroll if we came from booking page (indicated by scroll flag)
            setTimeout(() => {
                const bookingSection = document.getElementById('booking-section');
                if (bookingSection) {
                    const headerOffset = 80;
                    const elementPosition = bookingSection.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
                // Clear the scroll flag after scrolling
                sessionStorage.removeItem(`scroll_to_booking_${id}`);
            }, 300);
        }
    }, [availableSlots.length, loadingSlots, loading, id]);

    // Restore selected slots after available slots are loaded
    useEffect(() => {
        if (availableSlots.length > 0 && selectedSlots.length === 0) {
            // Check if we have saved slots to restore
            const storageKey = `ground_${id}_booking_selection`;
            const savedSelection = localStorage.getItem(storageKey);
            const shouldScroll = sessionStorage.getItem(`scroll_to_booking_${id}`);
            
            if (savedSelection) {
                try {
                    const selection = JSON.parse(savedSelection);
                    // Check if the saved selection is for this ground and date matches
                    if (selection.ground_id === id && selection.date === selectedDate && selection.selectedSlots && Array.isArray(selection.selectedSlots) && selection.selectedSlots.length > 0) {
                        // Match saved slot IDs with available slots
                        const restoredSlots = selection.selectedSlots
                            .map(savedSlot => {
                                // Find matching slot from available slots
                                const matchedSlot = availableSlots.find(avSlot => avSlot.id === savedSlot.id);
                                return matchedSlot;
                            })
                            .filter(slot => slot && slot.available); // Only restore if slot exists and is available
                        
                        if (restoredSlots.length > 0) {
                            setSelectedSlots(restoredSlots);
                            // Update localStorage with restored slots
                            const updatedSelection = {
                                ground_id: id,
                                date: selectedDate,
                                selectedSlots: restoredSlots
                            };
                            localStorage.setItem(storageKey, JSON.stringify(updatedSelection));
                        }
                        
                        // Scroll to booking section ONLY if we came from booking page (indicated by scroll flag)
                        if (shouldScroll === 'true') {
                            setTimeout(() => {
                                const bookingSection = document.getElementById('booking-section');
                                if (bookingSection) {
                                    // Calculate offset for sticky header
                                    const headerOffset = 80;
                                    const elementPosition = bookingSection.getBoundingClientRect().top;
                                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                                    window.scrollTo({
                                        top: offsetPosition,
                                        behavior: 'smooth'
                                    });
                                }
                                // Clear the scroll flag after scrolling
                                sessionStorage.removeItem(`scroll_to_booking_${id}`);
                            }, 300);
                        }
                    } else if (shouldScroll === 'true') {
                        // Even if slots don't match, scroll if we have the flag (user came back from booking page)
                        setTimeout(() => {
                            const bookingSection = document.getElementById('booking-section');
                            if (bookingSection) {
                                const headerOffset = 80;
                                const elementPosition = bookingSection.getBoundingClientRect().top;
                                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                                window.scrollTo({
                                    top: offsetPosition,
                                    behavior: 'smooth'
                                });
                            }
                            // Clear the scroll flag after scrolling
                            sessionStorage.removeItem(`scroll_to_booking_${id}`);
                        }, 300);
                    }
                } catch (error) {
                    console.error('Error restoring selected slots:', error);
                }
            } else if (shouldScroll === 'true') {
                // If no saved selection but we have scroll flag (came from booking page), still scroll
                setTimeout(() => {
                    const bookingSection = document.getElementById('booking-section');
                    if (bookingSection) {
                        const headerOffset = 80;
                        const elementPosition = bookingSection.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                    // Clear the scroll flag after scrolling
                    sessionStorage.removeItem(`scroll_to_booking_${id}`);
                }, 300);
            }
        }
    }, [availableSlots, id, selectedDate, selectedSlots.length]);


    const handleSlotSelect = (slot) => {
        if (slot.available) {
            setSelectedSlots(prevSlots => {
                const isSelected = prevSlots.some(s => s.id === slot.id);
                let newSlots;
                if (isSelected) {
                    // Remove slot if already selected
                    newSlots = prevSlots.filter(s => s.id !== slot.id);
                } else {
                    // Add slot if not selected
                    newSlots = [...prevSlots, slot];
                }
                
                // Save to localStorage
                const storageKey = `ground_${id}_booking_selection`;
                const selection = {
                    ground_id: id,
                    date: selectedDate,
                    selectedSlots: newSlots
                };
                localStorage.setItem(storageKey, JSON.stringify(selection));
                
                return newSlots;
            });
        }
    };

    const handleBookNow = () => {
        if (!user) {
            navigate('/login');
            return;
        }
        if (!selectedDate || selectedSlots.length === 0) {
            alert('Please select a date and at least one time slot');
            return;
        }

        // Prepare booking data with multiple slots
        const slotIds = selectedSlots.map(slot => slot.id);
        const timeSlots = selectedSlots.map(slot => slot.time || '');
        const totalPrice = selectedSlots.reduce((total, slot) => total + parseFloat(slot.price || 0), 0);

        const bookingData = {
            ground_id: id,
            date: selectedDate,
            slot_ids: slotIds,
            time_slots: timeSlots,
            total_price: totalPrice,
            selectedSlots: selectedSlots // Include full slot data for display
        };

        // Save to localStorage before navigating (this will be restored when user comes back)
        const storageKey = `ground_${id}_booking_selection`;
        const selection = {
            ground_id: id,
            date: selectedDate,
            selectedSlots: selectedSlots
        };
        localStorage.setItem(storageKey, JSON.stringify(selection));

        // Navigate to booking page with booking data
        navigate('/booking', { state: bookingData });
    };

    const formatTime = (timeString) => {
        if (!timeString) return '';
        try {
            // Remove any whitespace and ensure proper format
            const cleanedTime = timeString.trim();
            
            // If it's already in HH:mm format, use it directly
            if (/^\d{1,2}:\d{2}$/.test(cleanedTime)) {
                const [hours, minutes] = cleanedTime.split(':');
                const date = new Date(2000, 0, 1, parseInt(hours, 10), parseInt(minutes, 10));
                return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            }
            
            // Try to parse as ISO time string
            const date = new Date(`2000-01-01T${cleanedTime}`);
            if (isNaN(date.getTime())) {
                // If parsing fails, return the original string
                return cleanedTime;
            }
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        } catch {
            return timeString.trim();
        }
    };

    // Toast notification function
    const showToast = (message, type = 'success') => {
        setToast({ message, type });
        setTimeout(() => {
            setToast(null);
        }, 3000); // Auto hide after 3 seconds
    };

    // Review functions
    const fetchReviews = async () => {
        try {
            setLoadingReviews(true);
            const response = await axios.get(`/get-ground-reviews/${id}`);
            if (response.data.success) {
                setReviews(response.data.reviews || []);
                setUserReview(response.data.userReview || null);
                
                // Reset visible reviews count when new reviews are fetched
                setVisibleReviewsCount(3);
                
                // Use average rating from API response, or calculate if not provided
                if (response.data.average_rating !== undefined) {
                    setAverageRating(parseFloat(response.data.average_rating) || 0);
                } else if (response.data.reviews && response.data.reviews.length > 0) {
                    // Fallback calculation if API doesn't provide average
                    const ratings = response.data.reviews
                        .map(review => parseInt(review.rating) || 0)
                        .filter(rating => rating > 0 && rating <= 5);
                    if (ratings.length > 0) {
                        const totalRating = ratings.reduce((sum, rating) => sum + rating, 0);
                        const avgRating = totalRating / ratings.length;
                        setAverageRating(parseFloat(avgRating.toFixed(1)));
                    } else {
                        setAverageRating(0);
                    }
                } else {
                    setAverageRating(0);
                }
            }
        } catch (error) {
            console.error('Error fetching reviews:', error);
        } finally {
            setLoadingReviews(false);
        }
    };

    const handleSubmitReview = async () => {
        if (!user) {
            navigate('/login');
            return;
        }

        if (!reviewComment.trim() || reviewComment.trim().length < 5) {
            showToast('Please enter a review comment (minimum 5 characters)', 'error');
            return;
        }

        try {
            const reviewData = {
                ground_id: id,
                rating: reviewRating,
                comment: reviewComment.trim()
            };

            let response;
            if (editingReviewId) {
                // Update existing review
                response = await axios.put(`/update-review/${editingReviewId}`, reviewData);
            } else {
                // Create new review
                response = await axios.post('/store-review', reviewData);
            }

            if (response.data.success) {
                showToast(response.data.message || 'Review submitted successfully!', 'success');
                setShowReviewForm(false);
                setReviewComment('');
                setReviewRating(5);
                setEditingReviewId(null);
                fetchReviews(); // Refresh reviews
            } else {
                showToast(response.data.message || 'Error submitting review', 'error');
            }
        } catch (error) {
            console.error('Error submitting review:', error);
            showToast(error.response?.data?.message || 'Error submitting review. Please try again.', 'error');
        }
    };

    const handleEditReview = (review) => {
        setReviewRating(review.rating);
        setReviewComment(review.comment);
        setEditingReviewId(review.id);
        setShowReviewForm(true);
    };

    const handleDeleteReview = async (reviewId) => {
        // Show custom confirmation dialog
        const confirmed = window.confirm('Are you sure you want to delete your review?');
        if (!confirmed) {
            return;
        }

        try {
            const response = await axios.delete(`/delete-review/${reviewId}`);
            if (response.data.success) {
                showToast('Review deleted successfully!', 'success');
                fetchReviews(); // Refresh reviews
            } else {
                showToast(response.data.message || 'Error deleting review', 'error');
            }
        } catch (error) {
            console.error('Error deleting review:', error);
            showToast(error.response?.data?.message || 'Error deleting review. Please try again.', 'error');
        }
    };

    const handleCancelReview = () => {
        setShowReviewForm(false);
        setReviewComment('');
        setReviewRating(5);
        setEditingReviewId(null);
    };

    // Reply functions
    const handleSubmitReply = async (reviewId) => {
        if (!user) {
            navigate('/login');
            return;
        }

        if (!replyComment.trim() || replyComment.trim().length === 0) {
            showToast('Please enter a reply comment', 'error');
            return;
        }

        try {
            const replyData = {
                review_id: reviewId,
                comment: replyComment.trim()
            };

            let response;
            if (editingReplyId) {
                // Update existing reply
                response = await axios.put(`/update-reply/${editingReplyId}`, { comment: replyComment.trim() });
            } else {
                // Create new reply
                response = await axios.post('/store-reply', replyData);
            }

            if (response.data.success) {
                showToast(response.data.message || 'Reply submitted successfully!', 'success');
                setReplyingToReviewId(null);
                setReplyComment('');
                setEditingReplyId(null);
                fetchReviews(); // Refresh reviews
            } else {
                showToast(response.data.message || 'Error submitting reply', 'error');
            }
        } catch (error) {
            console.error('Error submitting reply:', error);
            showToast(error.response?.data?.message || 'Error submitting reply. Please try again.', 'error');
        }
    };

    const handleEditReply = (reply) => {
        setReplyComment(reply.comment);
        setEditingReplyId(reply.id);
        setReplyingToReviewId(reply.review_id);
    };

    const handleDeleteReply = async (replyId) => {
        // Show custom confirmation dialog
        const confirmed = window.confirm('Are you sure you want to delete this reply?');
        if (!confirmed) {
            return;
        }

        try {
            const response = await axios.delete(`/delete-reply/${replyId}`);
            if (response.data.success) {
                showToast('Reply deleted successfully!', 'success');
                fetchReviews(); // Refresh reviews
            } else {
                showToast(response.data.message || 'Error deleting reply', 'error');
            }
        } catch (error) {
            console.error('Error deleting reply:', error);
            showToast(error.response?.data?.message || 'Error deleting reply. Please try again.', 'error');
        }
    };

    const handleCancelReply = () => {
        setReplyingToReviewId(null);
        setReplyComment('');
        setEditingReplyId(null);
    };

    const handleShowReplyForm = (reviewId) => {
        setReplyingToReviewId(reviewId);
        setReplyComment('');
        setEditingReplyId(null);
    };


    if (loading) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex items-center justify-center">
                <div className="text-center">
                    <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
                    <p className="text-gray-600">Loading ground details...</p>
                </div>
            </div>
        );
    }

    if (error || !ground) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex items-center justify-center">
                <div className="text-center bg-white p-8 rounded-xl shadow-lg max-w-md">
                    <XMarkIcon className="h-16 w-16 text-red-500 mx-auto mb-4" />
                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Ground Not Found</h2>
                    <p className="text-gray-600 mb-6">{error || 'The ground you are looking for does not exist.'}</p>
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

    const images = ground.images || [];
    const features = ground.features || [];
    const slots = ground.slots || [];

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
                                    // Clear localStorage booking selection when going back to home
                                    const storageKey = `ground_${id}_booking_selection`;
                                    localStorage.removeItem(storageKey);
                                    // Also clear scroll flag
                                    sessionStorage.removeItem(`scroll_to_booking_${id}`);
                                    navigate('/home');
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
                        <div className="flex items-center space-x-2 sm:space-x-4">
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

            {/* Ground Details Section */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Ground Header */}
                <div className="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
                    {/* Main Image */}
                    <div className="relative h-96 bg-gradient-to-r from-green-400 to-blue-400">
                        {images.length > 0 ? (
                            <img
                                src={images[selectedImageIndex]?.image_url || images[0]?.image_url}
                                alt={ground.name}
                                className="w-full h-full object-cover"
                            />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <HomeIcon className="h-32 w-32 text-white opacity-50" />
                            </div>
                        )}
                        {images.length > 1 && (
                            <>
                                <button
                                    onClick={() => setSelectedImageIndex((prev) => (prev - 1 + images.length) % images.length)}
                                    className="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all"
                                >
                                    <ChevronLeftIcon className="h-6 w-6" />
                                </button>
                                <button
                                    onClick={() => setSelectedImageIndex((prev) => (prev + 1) % images.length)}
                                    className="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all"
                                >
                                    <ChevronRightIcon className="h-6 w-6" />
                                </button>
                                <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                                    {images.map((_, index) => (
                                        <button
                                            key={index}
                                            onClick={() => setSelectedImageIndex(index)}
                                            className={`h-2 rounded-full transition-all ${
                                                index === selectedImageIndex ? 'bg-white w-8' : 'bg-white/50 w-2 hover:bg-white/75'
                                            }`}
                                        />
                                    ))}
                                </div>
                            </>
                        )}
                    </div>

                    {/* Ground Info */}
                    <div className="p-8">
                        <div className="flex flex-col md:flex-row md:items-start md:justify-between mb-6">
                            <div className="flex-1">
                                <h1 className="text-4xl font-bold text-gray-900 mb-4">{ground.name}</h1>
                                <div className="flex items-center text-gray-600 mb-4">
                                    <MapPinIcon className="h-6 w-6 mr-2 text-green-600" />
                                    <span className="text-lg">{ground.location}</span>
                                </div>
                                <div className="flex flex-wrap gap-4 text-gray-600 mb-4">
                                    {ground.ground_type && (
                                        <div className="flex items-center">
                                            <BoltIcon className="h-5 w-5 mr-2 text-green-600" />
                                            <span>{ground.ground_type}</span>
                                        </div>
                                    )}
                                    {ground.capacity && (
                                        <div className="flex items-center">
                                            <UserIcon className="h-5 w-5 mr-2 text-green-600" />
                                            <span>Capacity: {ground.capacity} players</span>
                                        </div>
                                    )}
                                    {ground.opening_time && ground.closing_time && (
                                        <div className="flex items-center">
                                            <ClockIcon className="h-5 w-5 mr-2 text-green-600" />
                                            <span>{formatTime(ground.opening_time)} - {formatTime(ground.closing_time)}</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Features */}
                        {features.length > 0 && (
                            <div className="mb-6">
                                <h3 className="text-xl font-bold text-gray-900 mb-4">Facilities & Features</h3>
                                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    {features.map((feature) => (
                                        <div
                                            key={feature.id}
                                            className="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center space-x-2"
                                        >
                                            <CheckCircleIcon className="h-5 w-5 text-green-600 flex-shrink-0" />
                                            <span className="text-sm font-medium text-gray-700">{feature.feature_name}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Description */}
                        {ground.description && (
                            <div className="mb-6">
                                <h3 className="text-xl font-bold text-gray-900 mb-3">About</h3>
                                <p className="text-gray-600 leading-relaxed">{ground.description}</p>
                            </div>
                        )}

                        {/* Rules */}
                        {ground.rules && (
                            <div className="mb-6">
                                <h3 className="text-xl font-bold text-gray-900 mb-3">Rules & Guidelines</h3>
                                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p className="text-gray-700 whitespace-pre-line">{ground.rules}</p>
                                </div>
                            </div>
                        )}

                        {/* Contact Info */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            {ground.phone && (
                                <div className="flex items-center space-x-3 bg-blue-50 p-4 rounded-lg">
                                    <PhoneIcon className="h-6 w-6 text-blue-600" />
                                    <div>
                                        <p className="text-sm text-gray-600">Phone</p>
                                        <a href={`tel:${ground.phone}`} className="text-lg font-semibold text-gray-900 hover:text-blue-600">
                                            {ground.phone}
                                        </a>
                                    </div>
                                </div>
                            )}
                            {ground.email && (
                                <div className="flex items-center space-x-3 bg-blue-50 p-4 rounded-lg">
                                    <EnvelopeIcon className="h-6 w-6 text-blue-600" />
                                    <div>
                                        <p className="text-sm text-gray-600">Email</p>
                                        <a href={`mailto:${ground.email}`} className="text-lg font-semibold text-gray-900 hover:text-blue-600">
                                            {ground.email}
                                        </a>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Booking Section */}
                <div id="booking-section" className="bg-white rounded-xl shadow-lg p-8">
                    <h2 className="text-3xl font-bold text-gray-900 mb-6">Book Your Slot</h2>
                    
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {/* Left: Date and Slot Selection */}
                        <div>
                            {/* Date Slider */}
                            <div className="mb-6">
                                <div className="flex items-center justify-between mb-4">
                                    <label className="block text-lg font-semibold text-gray-900">
                                        Select Date
                                    </label>
                                    <div className="flex items-center space-x-2 text-sm text-gray-600">
                                        <div className="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                                        <span>Today</span>
                                    </div>
                                </div>
                                <div className="relative">
                                    {/* Gradient fade on edges */}
                                    <div className="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-white via-white/80 to-transparent z-10 pointer-events-none"></div>
                                    <div className="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-white via-white/80 to-transparent z-10 pointer-events-none"></div>
                                    
                                    {/* Scroll buttons */}
                                    <button
                                        onClick={() => {
                                            const container = document.getElementById('date-slider-container');
                                            if (container) {
                                                container.scrollBy({ left: -250, behavior: 'smooth' });
                                            }
                                        }}
                                        className="absolute left-2 top-1/2 transform -translate-y-1/2 z-20 bg-white/95 hover:bg-white shadow-lg rounded-full p-2.5 transition-all hover:scale-110 border border-gray-200"
                                    >
                                        <ChevronLeftIcon className="h-5 w-5 text-gray-700" />
                                    </button>
                                    <button
                                        onClick={() => {
                                            const container = document.getElementById('date-slider-container');
                                            if (container) {
                                                container.scrollBy({ left: 250, behavior: 'smooth' });
                                            }
                                        }}
                                        className="absolute right-2 top-1/2 transform -translate-y-1/2 z-20 bg-white/95 hover:bg-white shadow-lg rounded-full p-2.5 transition-all hover:scale-110 border border-gray-200"
                                    >
                                        <ChevronRightIcon className="h-5 w-5 text-gray-700" />
                                    </button>
                                    
                                    <div 
                                        id="date-slider-container"
                                        className={`flex space-x-4 overflow-x-auto pb-4 scrollbar-hide px-6 snap-x snap-mandatory scroll-smooth select-none ${
                                            isDragging ? 'cursor-grabbing' : 'cursor-grab'
                                        }`}
                                        style={{ 
                                            scrollbarWidth: 'none', 
                                            msOverflowStyle: 'none',
                                            scrollBehavior: isDragging ? 'auto' : 'smooth'
                                        }}
                                        onMouseDown={(e) => {
                                            setIsDragging(true);
                                            setStartX(e.pageX - e.currentTarget.offsetLeft);
                                            setScrollLeft(e.currentTarget.scrollLeft);
                                            e.currentTarget.style.cursor = 'grabbing';
                                        }}
                                        onMouseLeave={() => {
                                            setIsDragging(false);
                                            const container = document.getElementById('date-slider-container');
                                            if (container) {
                                                container.style.cursor = 'grab';
                                            }
                                        }}
                                        onMouseUp={() => {
                                            setIsDragging(false);
                                            const container = document.getElementById('date-slider-container');
                                            if (container) {
                                                container.style.cursor = 'grab';
                                            }
                                        }}
                                        onMouseMove={(e) => {
                                            if (!isDragging) return;
                                            e.preventDefault();
                                            const x = e.pageX - e.currentTarget.offsetLeft;
                                            const walk = (x - startX) * 2; // Scroll speed multiplier
                                            e.currentTarget.scrollLeft = scrollLeft - walk;
                                        }}
                                    >
                                        {dateOptions.map((dateOption, index) => (
                                            <button
                                                key={dateOption.date}
                                                 onClick={(e) => {
                                                     // Prevent click when dragging
                                                     if (isDragging) {
                                                         e.preventDefault();
                                                         return;
                                                     }
                                                     const newDate = dateOption.date;
                                                     setSelectedDate(newDate);
                                                     // Clear slots when date changes
                                                     setSelectedSlots([]);
                                                     
                                                     // Save to localStorage
                                                     const storageKey = `ground_${id}_booking_selection`;
                                                     const selection = {
                                                         ground_id: id,
                                                         date: newDate,
                                                         selectedSlots: []
                                                     };
                                                     localStorage.setItem(storageKey, JSON.stringify(selection));
                                                     
                                                     // Smooth scroll to selected date and center it
                                                     setTimeout(() => {
                                                         const container = document.getElementById('date-slider-container');
                                                         const button = document.getElementById(`date-btn-${index}`);
                                                         if (container && button) {
                                                             const containerRect = container.getBoundingClientRect();
                                                             const buttonRect = button.getBoundingClientRect();
                                                             const scrollLeft = container.scrollLeft + buttonRect.left - containerRect.left - (containerRect.width / 2) + (buttonRect.width / 2);
                                                             container.scrollTo({ left: scrollLeft, behavior: 'smooth' });
                                                         }
                                                     }, 10);
                                                 }}
                                                id={`date-btn-${index}`}
                                                className={`flex-shrink-0 w-24 snap-center flex flex-col items-center justify-center p-5 rounded-2xl border-2 transition-all duration-300 transform hover:scale-105 active:scale-100 ${
                                                    selectedDate === dateOption.date
                                                        ? 'bg-gradient-to-br from-green-500 via-green-500 to-blue-500 text-white border-white shadow-2xl scale-105 z-10'
                                                        : dateOption.isToday && selectedDate !== dateOption.date
                                                        ? 'bg-gradient-to-br from-green-100 to-green-50 border-green-400 text-green-700 hover:border-green-500 hover:shadow-lg'
                                                        : 'bg-white border-gray-200 text-gray-700 hover:border-gray-300 hover:bg-gray-50 hover:shadow-lg'
                                                }`}
                                                style={selectedDate === dateOption.date ? {
                                                    boxShadow: '0 0 0 4px rgba(34, 197, 94, 0.2), 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)'
                                                } : {}}
                                            >
                                                <span className={`text-[11px] font-bold uppercase tracking-wide mb-1 ${
                                                    selectedDate === dateOption.date 
                                                        ? 'text-white/90' 
                                                        : dateOption.isToday 
                                                        ? 'text-green-600' 
                                                        : 'text-gray-500'
                                                }`}>
                                                    {dateOption.dayName}
                                                </span>
                                                <span className={`text-2xl font-extrabold leading-none ${
                                                    selectedDate === dateOption.date 
                                                        ? 'text-white' 
                                                        : dateOption.isToday 
                                                        ? 'text-green-700' 
                                                        : 'text-gray-900'
                                                }`}>
                                                    {dateOption.dayNumber}
                                                </span>
                                                <span className={`text-[10px] font-semibold mt-1 ${
                                                    selectedDate === dateOption.date 
                                                        ? 'text-white/90' 
                                                        : dateOption.isToday 
                                                        ? 'text-green-600' 
                                                        : 'text-gray-500'
                                                }`}>
                                                    {dateOption.monthName}
                                                </span>
                                                {dateOption.isToday && selectedDate !== dateOption.date && (
                                                    <div className="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white animate-pulse shadow-lg">
                                                        <div className="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-75"></div>
                                                    </div>
                                                )}
                                                 {selectedDate === dateOption.date && (
                                                     <>
                                                         <div className="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-12 h-1.5 bg-white rounded-full shadow-lg"></div>
                                                         <div className="absolute -top-2 left-1/2 transform -translate-x-1/2 w-2.5 h-2.5 bg-white rounded-full shadow-lg ring-2 ring-green-300"></div>
                                                     </>
                                                 )}
                                            </button>
                                        ))}
                                    </div>
                                    {/* Scroll indicator */}
                                    {dateOptions.length > 7 && (
                                        <div className="flex justify-center mt-3">
                                            <div className="flex items-center space-x-2 text-xs text-gray-500 bg-gray-100 px-4 py-2 rounded-full">
                                                <ChevronLeftIcon className="h-4 w-4" />
                                                <span className="font-medium">Scroll to see more dates</span>
                                                <ChevronRightIcon className="h-4 w-4" />
                                            </div>
                                        </div>
                                    )}
                                </div>
                                
                                {/* Selected Date Display */}
                                {selectedDate && (
                                    <div className="mt-4 p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-xl border border-green-200">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center space-x-3">
                                                <div className="bg-green-500 p-2 rounded-lg">
                                                    <CalendarIcon className="h-5 w-5 text-white" />
                                                </div>
                                                <div>
                                                    <p className="text-xs text-gray-600 font-medium">Selected Date</p>
                                                    <p className="text-base font-bold text-gray-900">
                                                        {(() => {
                                                            const selected = dateOptions.find(d => d.date === selectedDate);
                                                            if (!selected) return '';
                                                            return `${selected.dayName}, ${selected.monthName} ${selected.dayNumber}, ${new Date(selectedDate).getFullYear()}`;
                                                        })()}
                                                    </p>
                                                </div>
                                            </div>
                                            {selectedDate === new Date().toISOString().split('T')[0] && (
                                                <span className="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                                    Today
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {selectedDate && (
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Available Time Slots
                                    </label>
                                    {loadingSlots ? (
                                        <div className="text-center py-8">
                                            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                                            <p className="mt-2 text-gray-600">Loading slots...</p>
                                        </div>
                                    ) : availableSlots.length > 0 ? (
                                        <div className="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto">
                                            {availableSlots.map((slot, index) => {
                                                // Parse time from slot.time which is in format "HH:mm - HH:mm" or "HH:mm-HH:mm"
                                                const timeParts = slot.time ? slot.time.split(/\s*-\s*/) : [];
                                                const startTime = timeParts[0] ? timeParts[0].trim() : '';
                                                const endTime = timeParts[1] ? timeParts[1].trim() : '';
                                                
                                                return (
                                                    <button
                                                        key={slot.id || index}
                                                        onClick={() => handleSlotSelect(slot)}
                                                        disabled={!slot.available}
                                                        className={`p-4 rounded-lg border-2 transition-all relative ${
                                                            !slot.available
                                                                ? 'border-red-200 bg-red-50 opacity-60 cursor-not-allowed'
                                                                : selectedSlots.some(s => s.id === slot.id)
                                                                ? 'border-green-500 bg-green-50 shadow-md ring-2 ring-green-300'
                                                                : 'border-gray-200 hover:border-green-300 hover:bg-green-50'
                                                        }`}
                                                    >
                                                        {!slot.available && (
                                                            <div className="absolute top-2 right-2">
                                                                <span className="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                                                    Booked
                                                                </span>
                                                            </div>
                                                        )}
                                                        {slot.available && selectedSlots.some(s => s.id === slot.id) && (
                                                            <div className="absolute top-2 right-2">
                                                                <CheckCircleIcon className="h-5 w-5 text-green-600" />
                                                            </div>
                                                        )}
                                                        <div className="text-center">
                                                            <p className={`font-semibold mb-1 ${
                                                                slot.available ? 'text-gray-900' : 'text-gray-500 line-through'
                                                            }`}>
                                                                {startTime && endTime 
                                                                    ? `${formatTime(startTime)} - ${formatTime(endTime)}`
                                                                    : slot.time || 'Slot'}
                                                            </p>
                                                            {slot.hours && (
                                                                <p className="text-xs text-gray-500 mb-2">
                                                                    {slot.hours} {slot.hours === 1 ? 'hour' : 'hours'}
                                                                </p>
                                                            )}
                                                            <p className={`text-lg font-bold ${
                                                                slot.available ? 'text-green-600' : 'text-gray-400'
                                                            }`}>
                                                                ₹{parseFloat(slot.price || 0).toFixed(2)}
                                                            </p>
                                                        </div>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    ) : selectedDate ? (
                                        <div className="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                                            <CalendarIcon className="h-12 w-12 text-gray-400 mx-auto mb-2" />
                                            <p className="text-gray-600">No slots available for this date</p>
                                        </div>
                                    ) : (
                                        <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                                            <CalendarIcon className="h-12 w-12 text-blue-400 mx-auto mb-2" />
                                            <p className="text-blue-600">Please select a date to view available slots</p>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Right: Booking Summary */}
                        <div>
                            <div className="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-6 border-2 border-green-200">
                                <h3 className="text-xl font-bold text-gray-900 mb-4">Booking Summary</h3>
                                <div className="space-y-4">
                                    <div>
                                        <p className="text-sm text-gray-600">Ground Name</p>
                                        <p className="text-lg font-semibold text-gray-900">{ground.name}</p>
                                    </div>
                                    {selectedDate && (
                                        <div>
                                            <p className="text-sm text-gray-600">Selected Date</p>
                                            <p className="text-lg font-semibold text-gray-900">
                                                {new Date(selectedDate).toLocaleDateString('en-US', {
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
                                            <div>
                                                <p className="text-sm text-gray-600 mb-2">Selected Time Slots ({selectedSlots.length})</p>
                                                <div className="space-y-2 max-h-48 overflow-y-auto">
                                                    {selectedSlots.map((slot, index) => {
                                                        // Parse time from slot.time which is in format "HH:mm - HH:mm" or "HH:mm-HH:mm"
                                                        const timeParts = slot.time ? slot.time.split(/\s*-\s*/) : [];
                                                        const startTime = timeParts[0] ? timeParts[0].trim() : '';
                                                        const endTime = timeParts[1] ? timeParts[1].trim() : '';
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
                                                <div className="flex items-center justify-between mb-2">
                                                    <p className="text-sm text-gray-600">Total Price</p>
                                                    <p className="text-2xl font-bold text-green-600">
                                                        ₹{selectedSlots.reduce((total, slot) => total + parseFloat(slot.price || 0), 0).toFixed(2)}
                                                    </p>
                                                </div>
                                                <p className="text-xs text-gray-500">
                                                    {selectedSlots.length} slot{selectedSlots.length !== 1 ? 's' : ''} selected
                                                </p>
                                            </div>
                                        </>
                                    )}
                                    <button
                                        onClick={handleBookNow}
                                        disabled={!selectedDate || selectedSlots.length === 0}
                                        className={`w-full py-4 rounded-lg font-semibold text-lg transition-all ${
                                            selectedDate && selectedSlots.length > 0
                                                ? 'bg-gradient-to-r from-green-500 to-blue-500 text-white hover:from-green-600 hover:to-blue-600 shadow-lg hover:shadow-xl transform hover:scale-105'
                                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                        }`}
                                    >
                                        {user ? `Proceed to Book ${selectedSlots.length > 0 ? `(${selectedSlots.length} slot${selectedSlots.length !== 1 ? 's' : ''})` : ''}` : 'Login to Book'}
                                    </button>
                                    {!user && (
                                        <p className="text-sm text-center text-gray-600">
                                            <Link to="/login" className="text-green-600 hover:underline">
                                                Login
                                            </Link>
                                            {' or '}
                                            <Link to="/register" className="text-green-600 hover:underline">
                                                Register
                                            </Link>
                                            {' to book this ground'}
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Gallery Section */}
                {images.length > 1 && (
                    <div className="bg-white rounded-xl shadow-lg p-8 mt-8">
                        <h2 className="text-3xl font-bold text-gray-900 mb-6">Gallery</h2>
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            {images.map((image, index) => (
                                <button
                                    key={image.id}
                                    onClick={() => setSelectedImageIndex(index)}
                                    className={`relative overflow-hidden rounded-lg aspect-square ${
                                        index === selectedImageIndex ? 'ring-4 ring-green-500' : ''
                                    }`}
                                >
                                    <img
                                        src={image.image_url}
                                        alt={`${ground.name} - Image ${index + 1}`}
                                        className="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                                    />
                                </button>
                            ))}
                        </div>
                    </div>
                )}

                {/* Reviews Section */}
                <div className="bg-white rounded-xl shadow-lg p-8 mt-8">
                    <div className="flex items-center justify-between mb-6">
                        <div className="flex items-center space-x-4">
                            <ChatBubbleLeftRightIcon className="h-8 w-8 text-green-600" />
                            <div>
                                <h2 className="text-3xl font-bold text-gray-900">Reviews & Ratings</h2>
                                {reviews.length > 0 && (
                                    <p className="text-sm text-gray-600 mt-1">
                                        {reviews.length} {reviews.length === 1 ? 'review' : 'reviews'}
                                    </p>
                                )}
                            </div>
                        </div>
                        {reviews.length > 0 && (
                            <div className="flex items-center space-x-2">
                                <div className="flex items-center">
                                    {[1, 2, 3, 4, 5].map((star) => (
                                        <StarIconSolid
                                            key={star}
                                            className={`h-6 w-6 ${
                                                star <= Math.round(averageRating)
                                                    ? 'text-yellow-400'
                                                    : 'text-gray-300'
                                            }`}
                                        />
                                    ))}
                                </div>
                                <span className="text-2xl font-bold text-gray-900">{averageRating}</span>
                            </div>
                        )}
                    </div>

                    {/* Write Review Button */}
                    {user && (
                        <div className="mb-6">
                            {!userReview && !showReviewForm && (
                                <button
                                    onClick={() => setShowReviewForm(true)}
                                    className="px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all shadow-md hover:shadow-lg"
                                >
                                    Write a Review
                                </button>
                            )}
                            {userReview && !showReviewForm && (
                                <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <p className="text-sm text-gray-700 mb-2">
                                        You have already reviewed this ground.
                                    </p>
                                    <button
                                        onClick={() => handleEditReview(userReview)}
                                        className="px-4 py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition-all mr-2"
                                    >
                                        Edit Your Review
                                    </button>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Review Form */}
                    {showReviewForm && user && (
                        <div className="bg-gradient-to-br from-green-50 to-blue-50 rounded-xl p-6 mb-6 border-2 border-green-200">
                            <h3 className="text-xl font-bold text-gray-900 mb-4">
                                {editingReviewId ? 'Edit Your Review' : 'Write a Review'}
                            </h3>
                            <div className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Rating
                                    </label>
                                    <div className="flex items-center space-x-2">
                                        {[1, 2, 3, 4, 5].map((star) => (
                                            <button
                                                key={star}
                                                type="button"
                                                onClick={() => setReviewRating(star)}
                                                className="focus:outline-none"
                                            >
                                                {star <= reviewRating ? (
                                                    <StarIconSolid className="h-8 w-8 text-yellow-400 hover:text-yellow-500 transition-colors" />
                                                ) : (
                                                    <StarIcon className="h-8 w-8 text-gray-300 hover:text-yellow-400 transition-colors" />
                                                )}
                                            </button>
                                        ))}
                                        <span className="ml-2 text-sm text-gray-600">
                                            {reviewRating} out of 5
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Your Review
                                    </label>
                                    <textarea
                                        value={reviewComment}
                                        onChange={(e) => setReviewComment(e.target.value)}
                                        placeholder="Share your experience with this ground..."
                                        className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                                        rows="4"
                                        minLength="5"
                                        maxLength="500"
                                    />
                                    <p className="text-xs text-gray-500 mt-1">
                                        {reviewComment.length}/500 characters (minimum 5 characters)
                                    </p>
                                </div>
                                <div className="flex space-x-3">
                                    <button
                                        onClick={handleSubmitReview}
                                        disabled={reviewComment.trim().length < 5}
                                        className={`px-6 py-2 rounded-lg font-semibold transition-all ${
                                            reviewComment.trim().length >= 5
                                                ? 'bg-gradient-to-r from-green-500 to-blue-500 text-white hover:from-green-600 hover:to-blue-600 shadow-md hover:shadow-lg'
                                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                        }`}
                                    >
                                        {editingReviewId ? 'Update Review' : 'Submit Review'}
                                    </button>
                                    <button
                                        onClick={handleCancelReview}
                                        className="px-6 py-2 rounded-lg font-semibold border-2 border-gray-300 text-gray-700 hover:border-gray-400 transition-all"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Reviews List */}
                    {loadingReviews ? (
                        <div className="text-center py-8">
                            <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                            <p className="mt-2 text-gray-600">Loading reviews...</p>
                        </div>
                    ) : reviews.length > 0 ? (
                        <div className="space-y-6">
                            {reviews.slice(0, visibleReviewsCount).map((review) => (
                                <div
                                    key={review.id}
                                    className="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
                                >
                                    <div className="flex items-start justify-between mb-3">
                                        <div className="flex items-center space-x-3">
                                            <div className="bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                                {review.user?.name?.charAt(0)?.toUpperCase() || 'A'}
                                            </div>
                                            <div>
                                                <p className="font-semibold text-gray-900">
                                                    {review.user?.name || 'Anonymous'}
                                                </p>
                                                <p className="text-xs text-gray-500">
                                                    {new Date(review.created_at).toLocaleDateString('en-US', {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric',
                                                    })}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <div className="flex items-center">
                                                {[1, 2, 3, 4, 5].map((star) => (
                                                    <StarIconSolid
                                                        key={star}
                                                        className={`h-5 w-5 ${
                                                            star <= review.rating
                                                                ? 'text-yellow-400'
                                                                : 'text-gray-300'
                                                        }`}
                                                    />
                                                ))}
                                            </div>
                                            {user && user.id === review.user_id && (
                                                <div className="flex items-center space-x-2 ml-4">
                                                    <button
                                                        onClick={() => handleEditReview(review)}
                                                        className="p-2 text-gray-600 hover:text-green-600 transition-colors"
                                                        title="Edit review"
                                                    >
                                                        <PencilIcon className="h-5 w-5" />
                                                    </button>
                                                    <button
                                                        onClick={() => handleDeleteReview(review.id)}
                                                        className="p-2 text-gray-600 hover:text-red-600 transition-colors"
                                                        title="Delete review"
                                                    >
                                                        <TrashIcon className="h-5 w-5" />
                                                    </button>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    <p className="text-gray-700 leading-relaxed mb-4">{review.comment}</p>
                                    
                                    {/* Reply Button */}
                                    {user && !replyingToReviewId && (
                                        <button
                                            onClick={() => handleShowReplyForm(review.id)}
                                            className="text-sm text-green-600 hover:text-green-700 font-medium flex items-center space-x-1 transition-colors"
                                        >
                                            <ChatBubbleLeftRightIcon className="h-4 w-4" />
                                            <span>Reply</span>
                                        </button>
                                    )}
                                    {user && replyingToReviewId && replyingToReviewId !== review.id && (
                                        <button
                                            disabled
                                            className="text-sm text-gray-400 font-medium flex items-center space-x-1 cursor-not-allowed"
                                        >
                                            <ChatBubbleLeftRightIcon className="h-4 w-4" />
                                            <span>Reply</span>
                                        </button>
                                    )}

                                    {/* Reply Form */}
                                    {replyingToReviewId === review.id && user && (
                                        <div className="mt-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                                            <h4 className="text-sm font-semibold text-gray-900 mb-2">
                                                {editingReplyId ? 'Edit Reply' : 'Write a Reply'}
                                            </h4>
                                            <textarea
                                                value={replyComment}
                                                onChange={(e) => setReplyComment(e.target.value)}
                                                placeholder="Write your reply..."
                                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none mb-2"
                                                rows="3"
                                                maxLength="500"
                                            />
                                            <p className="text-xs text-gray-500 mb-3">
                                                {replyComment.length}/500 characters
                                            </p>
                                            <div className="flex space-x-2">
                                                <button
                                                    onClick={() => handleSubmitReply(review.id)}
                                                    disabled={!replyComment.trim()}
                                                    className={`px-4 py-2 rounded-lg text-sm font-semibold transition-all ${
                                                        replyComment.trim()
                                                            ? 'bg-green-500 text-white hover:bg-green-600'
                                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                                    }`}
                                                >
                                                    {editingReplyId ? 'Update Reply' : 'Post Reply'}
                                                </button>
                                                <button
                                                    onClick={handleCancelReply}
                                                    className="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-700 hover:border-gray-400 transition-all"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    )}

                                    {/* Replies List */}
                                    {review.replies && review.replies.length > 0 && (
                                        <div className="mt-4 ml-6 pl-4 border-l-2 border-green-200 space-y-4">
                                            <p className="text-sm font-semibold text-gray-700 mb-2">
                                                Replies ({review.replies.length})
                                            </p>
                                            {review.replies.map((reply) => (
                                                <div
                                                    key={reply.id}
                                                    className="bg-gray-50 rounded-lg p-4 border border-gray-200"
                                                >
                                                    <div className="flex items-start justify-between mb-2">
                                                        <div className="flex items-center space-x-2">
                                                            <div className="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-xs">
                                                                {reply.user?.name?.charAt(0)?.toUpperCase() || 'R'}
                                                            </div>
                                                            <div>
                                                                <p className="text-sm font-semibold text-gray-900">
                                                                    {reply.user?.name || 'Anonymous'}
                                                                </p>
                                                                <p className="text-xs text-gray-500">
                                                                    {new Date(reply.created_at).toLocaleDateString('en-US', {
                                                                        year: 'numeric',
                                                                        month: 'short',
                                                                        day: 'numeric',
                                                                    })}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        {user && user.id === reply.user_id && (
                                                            <div className="flex items-center space-x-2">
                                                                <button
                                                                    onClick={() => handleEditReply(reply)}
                                                                    className="p-1 text-gray-600 hover:text-green-600 transition-colors"
                                                                    title="Edit reply"
                                                                >
                                                                    <PencilIcon className="h-4 w-4" />
                                                                </button>
                                                                <button
                                                                    onClick={() => handleDeleteReply(reply.id)}
                                                                    className="p-1 text-gray-600 hover:text-red-600 transition-colors"
                                                                    title="Delete reply"
                                                                >
                                                                    <TrashIcon className="h-4 w-4" />
                                                                </button>
                                                            </div>
                                                        )}
                                                    </div>
                                                    <p className="text-sm text-gray-700 leading-relaxed">{reply.comment}</p>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            ))}
                            
                            {/* View More Button */}
                            {reviews.length > visibleReviewsCount && (
                                <div className="text-center pt-4">
                                    <button
                                        onClick={() => setVisibleReviewsCount(prev => Math.min(prev + 3, reviews.length))}
                                        className="px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all shadow-md hover:shadow-lg"
                                    >
                                        View More ({reviews.length - visibleReviewsCount} remaining)
                                    </button>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                            <ChatBubbleLeftRightIcon className="h-16 w-16 text-gray-400 mx-auto mb-4" />
                            <p className="text-lg font-semibold text-gray-700 mb-2">No Reviews Yet</p>
                            <p className="text-gray-600 mb-4">
                                Be the first to share your experience with this ground!
                            </p>
                            {!user && (
                                <Link
                                    to="/login"
                                    className="inline-block px-6 py-3 bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 transition-all"
                                >
                                    Login to Write a Review
                                </Link>
                            )}
                        </div>
                    )}
                </div>
            </div>

            {/* Custom Toast Notification */}
            {toast && (
                <div className="fixed top-4 right-4 z-50 animate-slide-in-right">
                    <div className={`flex items-center space-x-3 px-6 py-4 rounded-lg shadow-lg ${
                        toast.type === 'success'
                            ? 'bg-green-500 text-white'
                            : 'bg-red-500 text-white'
                    }`}>
                        {toast.type === 'success' ? (
                            <CheckCircleIcon className="h-6 w-6 flex-shrink-0" />
                        ) : (
                            <XMarkIcon className="h-6 w-6 flex-shrink-0" />
                        )}
                        <p className="font-semibold">{toast.message}</p>
                        <button
                            onClick={() => setToast(null)}
                            className="ml-4 hover:opacity-80 transition-opacity"
                        >
                            <XMarkIcon className="h-5 w-5" />
                        </button>
                    </div>
                </div>
            )}

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
                                GetBooking is India's leading platform for sports ground bookings. We connect passionate players with premium sports facilities across major cities.
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
                                <li className="flex items-center space-x-2">
                                    <PhoneIcon className="h-5 w-5" />
                                    <span>+91 1800-123-4567</span>
                                </li>
                                <li className="flex items-center space-x-2">
                                    <EnvelopeIcon className="h-5 w-5" />
                                    <span>support@getbooking.in</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div className="border-t border-gray-800 pt-8 text-center text-gray-400">
                        <p>&copy; {new Date().getFullYear()} GetBooking. All rights reserved.</p>
                    </div>
                </div>
            </footer>

            {/* Add styles for scrollbar hiding and smooth scrolling */}
            <style>{`
                .scrollbar-hide::-webkit-scrollbar {
                    display: none;
                }
                .scrollbar-hide {
                    -ms-overflow-style: none;
                    scrollbar-width: none;
                }
                #date-slider-container {
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                }
                #date-slider-container > button {
                    scroll-snap-align: center;
                }
                @keyframes slide-in {
                    from {
                        opacity: 0;
                        transform: translateX(-10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
                .date-card-animate {
                    animation: slide-in 0.3s ease-out;
                }
                @keyframes slide-in-right {
                    from {
                        opacity: 0;
                        transform: translateX(100%);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }
                .animate-slide-in-right {
                    animation: slide-in-right 0.3s ease-out;
                }
            `}</style>
        </div>
    );
}

export default GroundDetailsPage;

