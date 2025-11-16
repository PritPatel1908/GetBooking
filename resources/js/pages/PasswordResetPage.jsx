import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
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

function PasswordResetPage() {
    const navigate = useNavigate();
    const params = useParams();
    const [searchParams] = useSearchParams();
    
    // Get token from URL params (/:token) or query string (?token=...)
    const token = params.token || searchParams.get('token');
    const email = searchParams.get('email');

    const [step, setStep] = useState(token ? 'reset' : 'request'); // 'request' or 'reset'
    const [formData, setFormData] = useState({
        email: email || '',
        password: '',
        password_confirmation: '',
        token: token || '',
    });
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState(null);
    const [messageType, setMessageType] = useState(null); // 'success' or 'error'

    // Clear message after 5 seconds
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
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
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

    const validateRequestForm = () => {
        const newErrors = {};
        if (!formData.email.trim()) {
            newErrors.email = 'Email is required';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            newErrors.email = 'Please enter a valid email address';
        }
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const validateResetForm = () => {
        const newErrors = {};
        if (!formData.password) {
            newErrors.password = 'Password is required';
        } else if (formData.password.length < 8) {
            newErrors.password = 'Password must be at least 8 characters';
        }
        if (!formData.password_confirmation) {
            newErrors.password_confirmation = 'Please confirm your password';
        } else if (formData.password !== formData.password_confirmation) {
            newErrors.password_confirmation = 'Passwords do not match';
        }
        if (!formData.token) {
            newErrors.token = 'Reset token is required';
        }
        if (!formData.email.trim()) {
            newErrors.email = 'Email is required';
        }
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSendResetLink = async (e) => {
        e.preventDefault();

        if (!validateRequestForm()) {
            return;
        }

        setLoading(true);
        setMessage(null);
        setErrors({});

        try {
            const response = await axios.post('/password/email', {
                email: formData.email,
            });

            if (response.data.success) {
                let message = response.data.message || 'Password reset link has been sent to your email.';
                
                // If token is returned (for testing), show it and auto-redirect
                if (response.data.reset_token && response.data.reset_url) {
                    message += `\n\nReset URL: ${response.data.reset_url}`;
                    message += `\n\n(For testing: Click the URL or copy it to reset your password)`;
                    
                    // Auto-redirect to reset form after 2 seconds
                    setTimeout(() => {
                        window.location.href = response.data.reset_url;
                    }, 2000);
                }
                
                setMessage(message);
                setMessageType('success');
                
                // Don't clear email if token is provided (for testing)
                if (!response.data.reset_token) {
                    setFormData((prev) => ({ ...prev, email: '' }));
                }
            }
        } catch (error) {
            const errorMessage = error.response?.data?.message || 'Failed to send reset link. Please try again.';
            const errorData = error.response?.data?.errors || {};
            
            setMessage(errorMessage);
            setMessageType('error');
            setErrors(errorData);
        } finally {
            setLoading(false);
        }
    };

    const handleResetPassword = async (e) => {
        e.preventDefault();

        if (!validateResetForm()) {
            return;
        }

        setLoading(true);
        setMessage(null);
        setErrors({});

        try {
            const response = await axios.post('/password/reset', {
                email: formData.email,
                password: formData.password,
                password_confirmation: formData.password_confirmation,
                token: formData.token,
            });

            if (response.data.success) {
                setMessage(response.data.message || 'Your password has been reset successfully!');
                setMessageType('success');
                
                // Redirect to login after 3 seconds
                setTimeout(() => {
                    navigate('/login', { 
                        state: { message: 'Password reset successful. Please login with your new password.' }
                    });
                }, 3000);
            }
        } catch (error) {
            const errorMessage = error.response?.data?.message || 'Failed to reset password. Please try again.';
            const errorData = error.response?.data?.errors || {};
            
            setMessage(errorMessage);
            setMessageType('error');
            setErrors(errorData);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8">
                {/* Logo and Header */}
                <div className="text-center">
                    <div className="flex justify-center">
                        <div className="bg-gradient-to-r from-green-500 to-blue-500 rounded-full p-3">
                            <LockClosedIcon className="h-8 w-8 text-white" />
                        </div>
                    </div>
                    <h2 className="mt-6 text-3xl font-extrabold text-gray-900">
                        {step === 'request' ? 'Reset Password' : 'Set New Password'}
                    </h2>
                    <p className="mt-2 text-sm text-gray-600">
                        {step === 'request'
                            ? 'Enter your email address and we\'ll send you a link to reset your password.'
                            : 'Please enter your new password below.'}
                    </p>
                </div>

                {/* Message Alert */}
                {message && (
                    <div
                        className={`rounded-md p-4 ${
                            messageType === 'success'
                                ? 'bg-green-50 border border-green-200'
                                : 'bg-red-50 border border-red-200'
                        }`}
                    >
                        <div className="flex">
                            <div className="flex-shrink-0">
                                {messageType === 'success' ? (
                                    <CheckCircleIcon className="h-5 w-5 text-green-400" />
                                ) : (
                                    <ExclamationCircleIcon className="h-5 w-5 text-red-400" />
                                )}
                            </div>
                            <div className="ml-3">
                                <p
                                    className={`text-sm font-medium ${
                                        messageType === 'success' ? 'text-green-800' : 'text-red-800'
                                    }`}
                                >
                                    {message}
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Request Reset Link Form */}
                {step === 'request' && (
                    <form className="mt-8 space-y-6" onSubmit={handleSendResetLink}>
                        <div className="space-y-4">
                            {/* Email Field */}
                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
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
                                        className={`appearance-none relative block w-full pl-10 pr-3 py-3 border ${
                                            errors.email
                                                ? 'border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500'
                                                : 'border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500'
                                        } rounded-lg focus:z-10 sm:text-sm transition-colors`}
                                        placeholder="you@example.com"
                                    />
                                </div>
                                {errors.email && (
                                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                                )}
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                disabled={loading}
                                className="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                            >
                                {loading ? (
                                    <span className="flex items-center">
                                        <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Sending...
                                    </span>
                                ) : (
                                    <span className="flex items-center">
                                        Send Reset Link
                                        <ArrowRightIcon className="ml-2 h-5 w-5" />
                                    </span>
                                )}
                            </button>
                        </div>

                        <div className="text-center">
                            <Link
                                to="/login"
                                className="text-sm font-medium text-green-600 hover:text-green-500 transition-colors"
                            >
                                ← Back to Login
                            </Link>
                        </div>
                    </form>
                )}

                {/* Reset Password Form */}
                {step === 'reset' && (
                    <form className="mt-8 space-y-6" onSubmit={handleResetPassword}>
                        <div className="space-y-4">
                            {/* Email Field (hidden if token is provided) */}
                            {!email && (
                                <div>
                                    <label htmlFor="reset-email" className="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address
                                    </label>
                                    <div className="relative">
                                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <EnvelopeIcon className="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            id="reset-email"
                                            name="email"
                                            type="email"
                                            autoComplete="email"
                                            required
                                            value={formData.email}
                                            onChange={handleChange}
                                            className={`appearance-none relative block w-full pl-10 pr-3 py-3 border ${
                                                errors.email
                                                    ? 'border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500'
                                                    : 'border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500'
                                            } rounded-lg focus:z-10 sm:text-sm transition-colors`}
                                            placeholder="you@example.com"
                                        />
                                    </div>
                                    {errors.email && (
                                        <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                                    )}
                                </div>
                            )}

                            {/* Password Field */}
                            <div>
                                <label htmlFor="reset-password" className="block text-sm font-medium text-gray-700 mb-1">
                                    New Password
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <LockClosedIcon className="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        id="reset-password"
                                        name="password"
                                        type={showPassword ? 'text' : 'password'}
                                        autoComplete="new-password"
                                        required
                                        value={formData.password}
                                        onChange={handleChange}
                                        className={`appearance-none relative block w-full pl-10 pr-10 py-3 border ${
                                            errors.password
                                                ? 'border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500'
                                                : 'border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500'
                                        } rounded-lg focus:z-10 sm:text-sm transition-colors`}
                                        placeholder="Enter new password"
                                    />
                                    <button
                                        type="button"
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        onClick={() => setShowPassword(!showPassword)}
                                    >
                                        {showPassword ? (
                                            <EyeSlashIcon className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                        ) : (
                                            <EyeIcon className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                        )}
                                    </button>
                                </div>
                                {errors.password && (
                                    <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                                )}
                            </div>

                            {/* Confirm Password Field */}
                            <div>
                                <label htmlFor="reset-password-confirmation" className="block text-sm font-medium text-gray-700 mb-1">
                                    Confirm Password
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <LockClosedIcon className="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        id="reset-password-confirmation"
                                        name="password_confirmation"
                                        type={showConfirmPassword ? 'text' : 'password'}
                                        autoComplete="new-password"
                                        required
                                        value={formData.password_confirmation}
                                        onChange={handleChange}
                                        className={`appearance-none relative block w-full pl-10 pr-10 py-3 border ${
                                            errors.password_confirmation
                                                ? 'border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500'
                                                : 'border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500'
                                        } rounded-lg focus:z-10 sm:text-sm transition-colors`}
                                        placeholder="Confirm new password"
                                    />
                                    <button
                                        type="button"
                                        className="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                    >
                                        {showConfirmPassword ? (
                                            <EyeSlashIcon className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                        ) : (
                                            <EyeIcon className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                                        )}
                                    </button>
                                </div>
                                {errors.password_confirmation && (
                                    <p className="mt-1 text-sm text-red-600">{errors.password_confirmation}</p>
                                )}
                            </div>
                        </div>

                        <div>
                            <button
                                type="submit"
                                disabled={loading}
                                className="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl"
                            >
                                {loading ? (
                                    <span className="flex items-center">
                                        <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Resetting...
                                    </span>
                                ) : (
                                    <span className="flex items-center">
                                        Reset Password
                                        <ArrowRightIcon className="ml-2 h-5 w-5" />
                                    </span>
                                )}
                            </button>
                        </div>

                        <div className="text-center">
                            <Link
                                to="/login"
                                className="text-sm font-medium text-green-600 hover:text-green-500 transition-colors"
                            >
                                ← Back to Login
                            </Link>
                        </div>
                    </form>
                )}
            </div>
        </div>
    );
}

export default PasswordResetPage;

