import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import {
    HomeIcon,
    UserIcon,
    ArrowLeftIcon,
    CameraIcon,
    LockClosedIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    CalendarIcon,
    CreditCardIcon,
    Bars3Icon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import axios from 'axios';

function ProfilePage() {
    const navigate = useNavigate();
    const [user, setUser] = useState(null);
    const [profileData, setProfileData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [activeTab, setActiveTab] = useState('profile');
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    const [uploadingPhoto, setUploadingPhoto] = useState(false);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    // Profile form fields
    const [formData, setFormData] = useState({
        first_name: '',
        middle_name: '',
        last_name: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        country: '',
        postal_code: '',
    });

    // Password form fields
    const [passwordData, setPasswordData] = useState({
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
    });

    useEffect(() => {
        const initializePage = async () => {
            try {
                const userResponse = await axios.get('/api/user');
                if (userResponse.data && userResponse.data.id) {
                    setUser(userResponse.data);
                    await fetchProfile();
                } else {
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
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const fetchProfile = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await axios.get('/api/profile');
            if (response.data && response.data.success) {
                setProfileData(response.data);
                const userData = response.data.user;
                setFormData({
                    first_name: userData.first_name || '',
                    middle_name: userData.middle_name || '',
                    last_name: userData.last_name || '',
                    phone: userData.phone || '',
                    address: userData.address || '',
                    city: userData.city || '',
                    state: userData.state || '',
                    country: userData.country || '',
                    postal_code: userData.postal_code || '',
                });
            }
        } catch (error) {
            console.error('Error fetching profile:', error);
            if (error.response && error.response.status === 401) {
                navigate('/login');
            } else {
                setError('Failed to load profile. Please try again.');
            }
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handlePasswordChange = (e) => {
        const { name, value } = e.target;
        setPasswordData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handlePhotoUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            setError('Please select an image file');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            setError('Image size should be less than 2MB');
            return;
        }

        try {
            setUploadingPhoto(true);
            setError(null);
            setSuccess(null);

            const formData = new FormData();
            formData.append('profile_photo', file);

            const response = await axios.post('/api/profile/photo', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            if (response.data && response.data.success) {
                setSuccess('Profile photo updated successfully!');
                await fetchProfile();
                // Update user state with new photo
                const userResponse = await axios.get('/api/user');
                if (userResponse.data) {
                    setUser(userResponse.data);
                }
            }
        } catch (error) {
            console.error('Error uploading photo:', error);
            setError(error.response?.data?.message || 'Failed to upload photo. Please try again.');
        } finally {
            setUploadingPhoto(false);
        }
    };

    const handleProfileUpdate = async (e) => {
        e.preventDefault();
        try {
            setSaving(true);
            setError(null);
            setSuccess(null);

            const response = await axios.post('/api/profile', formData);

            if (response.data && response.data.success) {
                setSuccess('Profile updated successfully!');
                await fetchProfile();
                // Update user state
                const userResponse = await axios.get('/api/user');
                if (userResponse.data) {
                    setUser(userResponse.data);
                }
            }
        } catch (error) {
            console.error('Error updating profile:', error);
            if (error.response?.data?.errors) {
                const errors = Object.values(error.response.data.errors).flat().join(', ');
                setError(errors);
            } else {
                setError(error.response?.data?.message || 'Failed to update profile. Please try again.');
            }
        } finally {
            setSaving(false);
        }
    };

    const handlePasswordUpdate = async (e) => {
        e.preventDefault();
        try {
            setSaving(true);
            setError(null);
            setSuccess(null);

            const response = await axios.post('/profile/password', passwordData);

            if (response.data && response.data.success) {
                setSuccess('Password updated successfully!');
                setPasswordData({
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                });
            }
        } catch (error) {
            console.error('Error updating password:', error);
            if (error.response?.data?.errors) {
                const errors = Object.values(error.response.data.errors).flat().join(', ');
                setError(errors);
            } else {
                setError(error.response?.data?.message || 'Failed to update password. Please try again.');
            }
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex items-center justify-center">
                <div className="text-center">
                    <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
                    <p className="text-gray-600">Loading profile...</p>
                </div>
            </div>
        );
    }

    if (!profileData) {
        return null;
    }

    const profilePhotoUrl = profileData.user?.profile_photo_path || null;

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
                                            className="block px-3 py-2 text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50 rounded-lg transition-colors flex items-center space-x-2 border-l-4 border-green-600"
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

            {/* Profile Page Content */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
                <div className="mb-4 sm:mb-6">
                    <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
                    <p className="text-sm sm:text-base text-gray-600">Manage your account information and settings</p>
                </div>

                {error && (
                    <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p className="text-red-800">{error}</p>
                    </div>
                )}

                {success && (
                    <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p className="text-green-800">{success}</p>
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Left Sidebar - Profile Overview */}
                    <div className="lg:col-span-1">
                        <div className="bg-white rounded-xl shadow-lg p-4 sm:p-6">
                            {/* Profile Photo */}
                            <div className="relative mb-6">
                                <div className="mx-auto w-32 h-32 rounded-full overflow-hidden bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center">
                                    {profilePhotoUrl ? (
                                        <img
                                            src={profilePhotoUrl}
                                            alt={profileData.user?.name || 'Profile'}
                                            className="w-full h-full object-cover"
                                        />
                                    ) : (
                                        <UserIcon className="w-16 h-16 text-white" />
                                    )}
                                </div>
                                <label className="absolute bottom-0 right-0 bg-green-500 text-white p-2 rounded-full cursor-pointer hover:bg-green-600 transition-colors">
                                    <CameraIcon className="w-5 h-5" />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        onChange={handlePhotoUpload}
                                        disabled={uploadingPhoto}
                                        className="hidden"
                                    />
                                </label>
                                {uploadingPhoto && (
                                    <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full">
                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
                                    </div>
                                )}
                            </div>

                            {/* User Info */}
                            <div className="text-center mb-6">
                                <h2 className="text-xl font-bold text-gray-900 mb-1">
                                    {profileData.user?.name || 'User'}
                                </h2>
                                <p className="text-gray-600 flex items-center justify-center">
                                    <EnvelopeIcon className="w-4 h-4 mr-2" />
                                    {profileData.user?.email}
                                </p>
                            </div>

                            {/* Stats */}
                            <div className="space-y-4 border-t border-gray-200 pt-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center space-x-2">
                                        <CalendarIcon className="w-5 h-5 text-blue-600" />
                                        <span className="text-gray-600">Bookings</span>
                                    </div>
                                    <span className="font-bold text-gray-900">{profileData.stats?.bookings || 0}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center space-x-2">
                                        <CreditCardIcon className="w-5 h-5 text-green-600" />
                                        <span className="text-gray-600">Total Spent</span>
                                    </div>
                                    <span className="font-bold text-gray-900">
                                        ₹{parseFloat(profileData.stats?.total_payments || 0).toFixed(2)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right Content - Forms */}
                    <div className="lg:col-span-2">
                        {/* Tabs */}
                        <div className="bg-white rounded-xl shadow-lg mb-4 sm:mb-6">
                            <div className="flex border-b border-gray-200">
                                <button
                                    onClick={() => setActiveTab('profile')}
                                    className={`flex-1 px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-center font-medium transition-colors ${
                                        activeTab === 'profile'
                                            ? 'text-green-600 border-b-2 border-green-600'
                                            : 'text-gray-600 hover:text-gray-900'
                                    }`}
                                >
                                    Profile Information
                                </button>
                                <button
                                    onClick={() => setActiveTab('password')}
                                    className={`flex-1 px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-center font-medium transition-colors ${
                                        activeTab === 'password'
                                            ? 'text-green-600 border-b-2 border-green-600'
                                            : 'text-gray-600 hover:text-gray-900'
                                    }`}
                                >
                                    Change Password
                                </button>
                            </div>

                            {/* Profile Form */}
                            {activeTab === 'profile' && (
                                <form onSubmit={handleProfileUpdate} className="p-4 sm:p-6">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                First Name *
                                            </label>
                                            <input
                                                type="text"
                                                name="first_name"
                                                value={formData.first_name}
                                                onChange={handleInputChange}
                                                required
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Middle Name
                                            </label>
                                            <input
                                                type="text"
                                                name="middle_name"
                                                value={formData.middle_name}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Last Name *
                                            </label>
                                            <input
                                                type="text"
                                                name="last_name"
                                                value={formData.last_name}
                                                onChange={handleInputChange}
                                                required
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Phone Number
                                            </label>
                                            <div className="relative">
                                                <PhoneIcon className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                                                <input
                                                    type="tel"
                                                    name="phone"
                                                    value={formData.phone}
                                                    onChange={handleInputChange}
                                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>
                                        <div className="md:col-span-2">
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Address
                                            </label>
                                            <input
                                                type="text"
                                                name="address"
                                                value={formData.address}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                City
                                            </label>
                                            <input
                                                type="text"
                                                name="city"
                                                value={formData.city}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                State
                                            </label>
                                            <input
                                                type="text"
                                                name="state"
                                                value={formData.state}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Country
                                            </label>
                                            <input
                                                type="text"
                                                name="country"
                                                value={formData.country}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Postal Code
                                            </label>
                                            <input
                                                type="text"
                                                name="postal_code"
                                                value={formData.postal_code}
                                                onChange={handleInputChange}
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                        </div>
                                    </div>
                                    <div className="mt-6 flex justify-end">
                                        <button
                                            type="submit"
                                            disabled={saving}
                                            className={`px-6 py-3 rounded-lg font-semibold transition-all ${
                                                saving
                                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                                    : 'bg-gradient-to-r from-green-500 to-blue-500 text-white hover:from-green-600 hover:to-blue-600 shadow-lg hover:shadow-xl'
                                            }`}
                                        >
                                            {saving ? 'Saving...' : 'Save Changes'}
                                        </button>
                                    </div>
                                </form>
                            )}

                            {/* Password Form */}
                            {activeTab === 'password' && (
                                <form onSubmit={handlePasswordUpdate} className="p-4 sm:p-6">
                                    <div className="space-y-4 sm:space-y-6 max-w-md">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Current Password *
                                            </label>
                                            <div className="relative">
                                                <LockClosedIcon className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                                                <input
                                                    type="password"
                                                    name="current_password"
                                                    value={passwordData.current_password}
                                                    onChange={handlePasswordChange}
                                                    required
                                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                New Password *
                                            </label>
                                            <div className="relative">
                                                <LockClosedIcon className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                                                <input
                                                    type="password"
                                                    name="new_password"
                                                    value={passwordData.new_password}
                                                    onChange={handlePasswordChange}
                                                    required
                                                    minLength={8}
                                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                />
                                            </div>
                                            <p className="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                                Confirm New Password *
                                            </label>
                                            <div className="relative">
                                                <LockClosedIcon className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                                                <input
                                                    type="password"
                                                    name="new_password_confirmation"
                                                    value={passwordData.new_password_confirmation}
                                                    onChange={handlePasswordChange}
                                                    required
                                                    minLength={8}
                                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <div className="mt-6 flex justify-end">
                                        <button
                                            type="submit"
                                            disabled={saving}
                                            className={`px-6 py-3 rounded-lg font-semibold transition-all ${
                                                saving
                                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                                    : 'bg-gradient-to-r from-green-500 to-blue-500 text-white hover:from-green-600 hover:to-blue-600 shadow-lg hover:shadow-xl'
                                            }`}
                                        >
                                            {saving ? 'Updating...' : 'Update Password'}
                                        </button>
                                    </div>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default ProfilePage;
