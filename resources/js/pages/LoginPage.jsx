import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import {
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/react/24/outline';
import axios from 'axios';

function LoginPage() {
    const navigate = useNavigate();
    const location = useLocation();
    const [formData, setFormData] = useState({
        email: '',
        password: '',
        remember: false,
    });
    const [showPassword, setShowPassword] = useState(false);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState(null);
    const [messageType, setMessageType] = useState(null); // 'success' or 'error'

    // Check if user is already logged in - but only redirect on initial load if needed
    // Allow logged-in users to stay on login page (they might want to logout or switch accounts)
    useEffect(() => {
        const checkAuth = async () => {
            try {
                const response = await axios.get('/api/user');
                if (response.data) {
                    // User is already logged in
                    // Don't auto-redirect - let them stay on login page if they want
                    // They can manually navigate or logout if needed
                    console.log('User already logged in:', response.data);
                    // Optionally show a message that they're already logged in
                }
            } catch (error) {
                // User is not authenticated, stay on login page
                console.log('User not authenticated');
            }
        };
        checkAuth();
    }, [navigate]);

    // Show message for a few seconds then hide
    useEffect(() => {
        if (message) {
            const timer = setTimeout(() => {
                setMessage(null);
                setMessageType(null);
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [message]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value,
        }));
        // Clear error for this field when user starts typing
        if (errors[name]) {
            setErrors((prev) => {
                const newErrors = { ...prev };
                delete newErrors[name];
                return newErrors;
            });
        }
    };

    const validateForm = () => {
        const newErrors = {};

        if (!formData.email.trim()) {
            newErrors.email = 'Email is required';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            newErrors.email = 'Please enter a valid email address';
        }

        if (!formData.password) {
            newErrors.password = 'Password is required';
        } else if (formData.password.length < 8) {
            newErrors.password = 'Password must be at least 8 characters';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        setLoading(true);
        setMessage(null);
        setMessageType(null);

        try {
            console.log('🚀 Login attempt started', {
                email: formData.email,
                remember: formData.remember,
            });

            // Check CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('🔑 CSRF Token:', csrfToken ? 'Found' : 'Missing');

            if (!csrfToken) {
                console.error('❌ CSRF Token not found! Make sure meta tag exists in document head.');
                setMessage('CSRF token missing. Please refresh the page and try again.');
                setMessageType('error');
                setLoading(false);
                return;
            }

            // Log request config
            console.log('📤 Request config:', {
                url: '/login',
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                data: {
                    email: formData.email,
                    password: '***hidden***',
                    remember: formData.remember,
                },
            });

            const response = await axios.post('/login', {
                email: formData.email,
                password: formData.password,
                remember: formData.remember,
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            // Check if response is HTML (error case)
            if (typeof response.data === 'string' && response.data.trim().startsWith('<!DOCTYPE')) {
                console.error('❌ Server returned HTML instead of JSON!');
                console.error('Response preview:', response.data.substring(0, 200));
                setMessage('Server configuration error. Please contact support.');
                setMessageType('error');
                setLoading(false);
                return;
            }

            console.log('✅ Login response received:', {
                status: response.status,
                data: response.data,
            });

            if (response.data && response.data.success) {
                setMessage('Login successful! Redirecting...');
                setMessageType('success');

                // Redirect based on user type
                setTimeout(() => {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        const userType = response.data.user?.user_type || 'user';
                        if (userType === 'admin') {
                            window.location.href = '/admin/dashboard';
                        } else if (userType === 'client') {
                            window.location.href = '/client/dashboard';
                        } else {
                            window.location.href = '/home';
                        }
                    }
                }, 1000);
            } else {
                setMessage(response.data.message || 'Login failed. Please try again.');
                setMessageType('error');
                setLoading(false);
            }
        } catch (error) {
            console.error('❌ Login error occurred:', error);
            console.error('Error details:', {
                message: error.message,
                code: error.code,
                response: error.response?.data,
                status: error.response?.status,
                statusText: error.response?.statusText,
                headers: error.response?.headers,
                request: error.request ? 'Request sent but no response' : 'No request sent',
            });

            // Network error check
            if (!error.response && error.request) {
                console.error('🌐 Network Error: Request was sent but no response received');
                console.error('Possible causes: Server not running, CORS issue, or timeout');
            }

            if (error.response) {
                const { status, data } = error.response;

                if (status === 422 && data.errors) {
                    // Validation errors
                    setErrors(data.errors);
                    setMessage('Please correct the errors in the form.');
                } else if (status === 401) {
                    setMessage(data.message || 'Invalid email or password.');
                } else {
                    setMessage(data.message || 'Login failed. Please try again.');
                }
            } else {
                setMessage('Network error. Please check your internet connection.');
            }

            setMessageType('error');
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 flex items-center justify-center p-4">
            <div className="w-full max-w-md">
                {/* Logo and Header */}
                <div className="text-center mb-8">
                    <Link to="/home" className="inline-block">
                        <div className="flex items-center justify-center space-x-2 mb-4">
                            <div className="bg-gradient-to-r from-green-500 to-blue-500 p-3 rounded-xl shadow-lg">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                            </div>
                            <span className="text-3xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                                GetBooking
                            </span>
                        </div>
                    </Link>
                    <h1 className="text-3xl font-bold text-gray-900 mb-2">Welcome Back!</h1>
                    <p className="text-gray-600">Sign in to continue to your account</p>
                </div>

                {/* Message Alert */}
                {message && (
                    <div
                        className={`mb-6 p-4 rounded-lg flex items-start space-x-3 ${
                            messageType === 'success'
                                ? 'bg-green-50 border border-green-200 text-green-800'
                                : 'bg-red-50 border border-red-200 text-red-800'
                        }`}
                    >
                        {messageType === 'success' ? (
                            <CheckCircleIcon className="w-5 h-5 flex-shrink-0 mt-0.5" />
                        ) : (
                            <ExclamationCircleIcon className="w-5 h-5 flex-shrink-0 mt-0.5" />
                        )}
                        <p className="text-sm font-medium">{message}</p>
                    </div>
                )}

                {/* Login Form */}
                <div className="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Email Field */}
                        <div>
                            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <EnvelopeIcon className="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autoComplete="email"
                                    required
                                    value={formData.email}
                                    onChange={handleChange}
                                    className={`block w-full pl-10 pr-3 py-3 border ${
                                        errors.email ? 'border-red-300' : 'border-gray-300'
                                    } rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors`}
                                    placeholder="you@example.com"
                                />
                            </div>
                            {errors.email && (
                                <p className="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                    <ExclamationCircleIcon className="w-4 h-4" />
                                    <span>{Array.isArray(errors.email) ? errors.email[0] : errors.email}</span>
                                </p>
                            )}
                        </div>

                        {/* Password Field */}
                        <div>
                            <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <LockClosedIcon className="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type={showPassword ? 'text' : 'password'}
                                    autoComplete="current-password"
                                    required
                                    value={formData.password}
                                    onChange={handleChange}
                                    className={`block w-full pl-10 pr-12 py-3 border ${
                                        errors.password ? 'border-red-300' : 'border-gray-300'
                                    } rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors`}
                                    placeholder="Enter your password"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    {showPassword ? (
                                        <EyeSlashIcon className="h-5 w-5" />
                                    ) : (
                                        <EyeIcon className="h-5 w-5" />
                                    )}
                                </button>
                            </div>
                            {errors.password && (
                                <p className="mt-1 text-sm text-red-600 flex items-center space-x-1">
                                    <ExclamationCircleIcon className="w-4 h-4" />
                                    <span>{Array.isArray(errors.password) ? errors.password[0] : errors.password}</span>
                                </p>
                            )}
                        </div>

                        {/* Remember Me & Forgot Password */}
                        <div className="flex items-center justify-between">
                            <div className="flex items-center">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    checked={formData.remember}
                                    onChange={handleChange}
                                    className="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                />
                                <label htmlFor="remember" className="ml-2 block text-sm text-gray-700">
                                    Remember me
                                </label>
                            </div>
                            <Link
                                to="/password/reset"
                                className="text-sm font-medium text-green-600 hover:text-green-500 transition-colors"
                            >
                                Forgot password?
                            </Link>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={loading}
                            className="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-green-500 to-blue-500 text-white py-3 px-4 rounded-lg font-semibold hover:from-green-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {loading ? (
                                <>
                                    <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Signing in...</span>
                                </>
                            ) : (
                                <>
                                    <span>Sign In</span>
                                    <ArrowRightIcon className="w-5 h-5" />
                                </>
                            )}
                        </button>
                    </form>

                    {/* Divider */}
                    <div className="mt-6">
                        <div className="relative">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-gray-300"></div>
                            </div>
                            <div className="relative flex justify-center text-sm">
                                <span className="px-2 bg-white text-gray-500">Don't have an account?</span>
                            </div>
                        </div>
                    </div>

                    {/* Sign Up Link */}
                    <div className="mt-6 text-center">
                        <Link
                            to="/register"
                            className="inline-flex items-center space-x-2 text-green-600 hover:text-green-700 font-medium transition-colors"
                        >
                            <span>Create an account</span>
                            <ArrowRightIcon className="w-4 h-4" />
                        </Link>
                    </div>
                </div>

                {/* Footer */}
                <div className="mt-6 text-center text-sm text-gray-600">
                    <p>
                        By signing in, you agree to our{' '}
                        <Link to="/terms-and-conditions" className="text-green-600 hover:text-green-700 font-medium">
                            Terms of Service
                        </Link>{' '}
                        and{' '}
                        <Link to="/privacy-policy" className="text-green-600 hover:text-green-700 font-medium">
                            Privacy Policy
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    );
}

export default LoginPage;

