import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import axios from 'axios';

const AdminLayout = ({ children, title = 'Dashboard' }) => {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isMobile, setIsMobile] = useState(false);
    const [theme, setTheme] = useState(() => localStorage.getItem('admin-theme') || 'light');
    const navigate = useNavigate();
    const location = useLocation();

    useEffect(() => {
        fetchUser();
        const checkMobile = () => setIsMobile(window.innerWidth < 768);
        checkMobile();
        window.addEventListener('resize', checkMobile);
        return () => window.removeEventListener('resize', checkMobile);
    }, []);

    useEffect(() => {
        document.body.dataset.theme = theme;
        localStorage.setItem('admin-theme', theme);
    }, [theme]);

    const fetchUser = async () => {
        try {
            const response = await axios.get('/api/user');
            setUser(response.data);
            // Enforce admin-only access
            if (response.data?.user_type !== 'admin') {
                navigate('/home');
                return;
            }
        } catch (error) {
            console.error('Error fetching user:', error);
            if (error.response?.status === 401) {
                navigate('/login');
            }
        } finally {
            setLoading(false);
        }
    };

    const handleLogout = async () => {
        try {
            await axios.post('/logout');
            navigate('/login');
        } catch (error) {
            console.error('Logout error:', error);
        }
    };

    const isActive = (path) => {
        return location.pathname === path || location.pathname.startsWith(path + '/');
    };

    if (loading) {
        return (
            <div style={{
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                height: '100vh',
                background: 'linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%)'
            }}>
                <div className="admin-spinner"></div>
            </div>
        );
    }

    const navItems = [
        { path: '/admin/dashboard', icon: 'fa-home', label: 'Dashboard' },
        { path: '/admin/clients', icon: 'fa-users', label: 'Clients' },
        { path: '/admin/grounds', icon: 'fa-map-marker-alt', label: 'Grounds' },
        { path: '/admin/bookings', icon: 'fa-calendar-check', label: 'Bookings' },
        { path: '/admin/payments', icon: 'fa-money-bill-wave', label: 'Payments' },
        { path: '/admin/users', icon: 'fa-user-cog', label: 'Users' },
        { path: '/admin/contact-messages', icon: 'fa-envelope', label: 'Messages' },
    ];

    return (
        <div className="admin-container">
            {/* Mobile Overlay */}
            {sidebarOpen && isMobile && (
                <div
                    className="fixed inset-0 bg-black bg-opacity-50 z-[999]"
                    onClick={() => setSidebarOpen(false)}
                    style={{
                        transition: 'opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                    }}
                />
            )}

            <div style={{ display: 'flex', minHeight: '100vh' }}>
                {/* Modern Sidebar */}
                <aside
                    className={`admin-sidebar ${sidebarOpen ? 'open' : ''} ${!isMobile ? '' : 'hidden'}`}
                    style={{
                        background: 'linear-gradient(180deg, #6366F1 0%, #4F46E5 50%, #4338CA 100%)',
                        width: '260px',
                        position: !isMobile ? 'relative' : 'fixed',
                        transform: !isMobile ? 'translateX(0)' : (sidebarOpen ? 'translateX(0)' : 'translateX(-100%)'),
                        zIndex: 1000,
                        boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)'
                    }}
                >
                    <div className="admin-sidebar-menu">
                        {/* Logo Section */}
                        <div className="admin-logo">
                            <div className="admin-logo-icon">
                                <i className="fas fa-futbol"></i>
                            </div>
                            <div className="admin-logo-text">Get Booking</div>
                        </div>

                        {/* Navigation */}
                        <nav className="admin-nav">
                            {navItems.map((item) => (
                                <Link
                                    key={item.path}
                                    to={item.path}
                                    className={`admin-sidebar-item ${isActive(item.path) ? 'active' : ''}`}
                                    onClick={() => setSidebarOpen(false)}
                                >
                                    <i className={`fas ${item.icon}`}></i>
                                    <span>{item.label}</span>
                                </Link>
                            ))}

                            <button
                                onClick={handleLogout}
                                className="admin-sidebar-item"
                                style={{
                                    width: '100%',
                                    background: 'none',
                                    border: 'none',
                                    textAlign: 'left',
                                    cursor: 'pointer',
                                    color: 'rgba(255, 255, 255, 0.9)'
                                }}
                            >
                                <i className="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </button>
                        </nav>

                        {/* Support Card */}
                        <div className="admin-support-card">
                            <div className="admin-support-card-title">Need help?</div>
                            <button className="admin-support-card-button">
                                <i className="fas fa-headset" style={{ marginRight: '0.5rem' }}></i>
                                Support Center
                            </button>
                        </div>
                    </div>
                </aside>

                {/* Main Content */}
                <div className="admin-main" style={{
                    marginLeft: !isMobile ? '260px' : '0',
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    minHeight: '100vh',
                    transition: 'margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                }}>
                    {/* Modern Header */}
                    <header className="admin-header" style={{
                        background: '#FFFFFF',
                        backdropFilter: 'blur(10px)',
                        boxShadow: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                        padding: '1rem 1.25rem',
                        position: 'sticky',
                        top: 0,
                        zIndex: 100,
                        borderBottom: '1px solid #E5E7EB'
                    }}>
                        <div className="admin-header-content">
                            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                                <button
                                    onClick={() => setSidebarOpen(!sidebarOpen)}
                                    style={{
                                        padding: '0.75rem',
                                        borderRadius: '0.75rem',
                                        border: 'none',
                                        background: '#F3F4F6',
                                        cursor: 'pointer',
                                        fontSize: '1.125rem',
                                        color: '#374151',
                                        transition: 'all 0.15s cubic-bezier(0.4, 0, 0.2, 1)'
                                    }}
                                    onMouseEnter={(e) => {
                                        e.target.style.background = '#E5E7EB';
                                        e.target.style.transform = 'scale(1.05)';
                                    }}
                                    onMouseLeave={(e) => {
                                        e.target.style.background = '#F3F4F6';
                                        e.target.style.transform = 'scale(1)';
                                    }}
                                >
                                    <i className="fas fa-bars"></i>
                                </button>
                                <h1 style={{
                                    fontSize: '1.5rem',
                                    fontWeight: '700',
                                    color: '#111827',
                                    letterSpacing: '-0.02em',
                                    display: isMobile ? 'none' : 'block',
                                    margin: 0
                                }}>{title}</h1>
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '1.5rem' }}>
                                {/* Search */}
                                <div className="admin-search-wrapper" style={{
                                    display: isMobile ? 'none' : 'block'
                                }}>
                                    <input
                                        type="text"
                                        placeholder="Search anything..."
                                        className="admin-input"
                                        style={{
                                            width: isMobile ? '128px' : '280px',
                                            paddingLeft: '3rem',
                                            borderRadius: '9999px',
                                            border: '2px solid #E5E7EB',
                                            background: '#F9FAFB'
                                        }}
                                    />
                                    <i className="fas fa-search"></i>
                                </div>

                                {/* Theme Toggle */}
                                <button
                                    onClick={() => setTheme(theme === 'dark' ? 'light' : 'dark')}
                                    title={theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'}
                                    style={{
                                        padding: '0.75rem',
                                        borderRadius: '50%',
                                        border: 'none',
                                        background: '#F3F4F6',
                                        cursor: 'pointer',
                                        fontSize: '1.125rem',
                                        color: '#374151',
                                        transition: 'all 0.15s cubic-bezier(0.4, 0, 0.2, 1)'
                                    }}
                                    onMouseEnter={(e) => {
                                        e.target.style.background = '#E5E7EB';
                                        e.target.style.transform = 'scale(1.1)';
                                    }}
                                    onMouseLeave={(e) => {
                                        e.target.style.background = '#F3F4F6';
                                        e.target.style.transform = 'scale(1)';
                                    }}
                                >
                                    {theme === 'dark' ? <i className="fas fa-sun"></i> : <i className="fas fa-moon"></i>}
                                </button>

                                {/* Notifications */}
                                <button style={{
                                    position: 'relative',
                                    padding: '0.75rem',
                                    borderRadius: '50%',
                                    border: 'none',
                                    background: '#F3F4F6',
                                    cursor: 'pointer',
                                    fontSize: '1.125rem',
                                    color: '#374151',
                                    transition: 'all 0.15s cubic-bezier(0.4, 0, 0.2, 1)'
                                }}
                                onMouseEnter={(e) => {
                                    e.target.style.background = '#E5E7EB';
                                    e.target.style.transform = 'scale(1.1)';
                                }}
                                onMouseLeave={(e) => {
                                    e.target.style.background = '#F3F4F6';
                                    e.target.style.transform = 'scale(1)';
                                }}
                                >
                                    <i className="fas fa-bell"></i>
                                    <span style={{
                                        position: 'absolute',
                                        top: '0.5rem',
                                        right: '0.5rem',
                                        width: '8px',
                                        height: '8px',
                                        borderRadius: '50%',
                                        background: 'linear-gradient(135deg, #EF4444, #DC2626)',
                                        boxShadow: '0 0 0 2px white'
                                    }}></span>
                                </button>

                                {/* User Profile */}
                                <div style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: '0.75rem',
                                    paddingLeft: '1rem',
                                    borderLeft: '1px solid #E5E7EB'
                                }}>
                                    <div style={{ position: 'relative' }}>
                                        <img
                                            src={user?.profile_photo_path || `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || 'Admin')}&background=6366F1&color=fff&size=40&bold=true`}
                                            alt="Admin"
                                            style={{
                                                width: '40px',
                                                height: '40px',
                                                borderRadius: '50%',
                                                border: '3px solid #6366F1',
                                                boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                                                objectFit: 'cover'
                                            }}
                                        />
                                        <span style={{
                                            position: 'absolute',
                                            bottom: '0',
                                            right: '0',
                                            width: '12px',
                                            height: '12px',
                                            borderRadius: '50%',
                                            background: '#10B981',
                                            border: '2px solid white',
                                            boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)'
                                        }}></span>
                                    </div>
                                    <div style={{ display: isMobile ? 'none' : 'block' }}>
                                        <div style={{
                                            fontSize: '0.875rem',
                                            fontWeight: '600',
                                            color: '#111827'
                                        }}>
                                            {user?.name || 'Admin User'}
                                        </div>
                                        <div style={{
                                            fontSize: '0.75rem',
                                            color: '#6B7280',
                                            fontWeight: '500'
                                        }}>
                                            Administrator
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Main Content Area */}
                    <main className="admin-content" style={{
                        flex: 1,
                        padding: '1rem 1.25rem',
                        overflowY: 'auto',
                        background: 'linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%)',
                        minHeight: 'calc(100vh - 80px)'
                    }}>
                        <div className="admin-content-inner">
                            {children}
                        </div>
                    </main>
                </div>
            </div>
        </div>
    );
};

export default AdminLayout;
