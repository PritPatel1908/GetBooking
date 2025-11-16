import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import 'antd/dist/reset.css';
import './bootstrap';
import '../css/admin.css';
import HomePage from './pages/HomePage';
import GroundDetailsPage from './pages/GroundDetailsPage';
import AllGroundsPage from './pages/AllGroundsPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import BookingPage from './pages/BookingPage';
import MyBookingsPage from './pages/MyBookingsPage';
import BookingDetailsPage from './pages/BookingDetailsPage';
import ProfilePage from './pages/ProfilePage';
import PasswordResetPage from './pages/PasswordResetPage';

// Admin Pages
import AdminDashboardPage from './pages/admin/AdminDashboardPage';
import AdminClientsPage from './pages/admin/AdminClientsPage';
import AdminCreateClientPage from './pages/admin/AdminCreateClientPage';
import AdminEditClientPage from './pages/admin/AdminEditClientPage';
import AdminGroundsPage from './pages/admin/AdminGroundsPage';
import AdminCreateGroundPage from './pages/admin/AdminCreateGroundPage';
import AdminEditGroundPage from './pages/admin/AdminEditGroundPage';
import AdminBookingsPage from './pages/admin/AdminBookingsPage';
import AdminPaymentsPage from './pages/admin/AdminPaymentsPage';
import AdminUsersPage from './pages/admin/AdminUsersPage';
import AdminCreateUserPage from './pages/admin/AdminCreateUserPage';
import AdminEditUserPage from './pages/admin/AdminEditUserPage';

function App() {
    return (
        <BrowserRouter>
            <Routes>
                {/* User Routes */}
                <Route path="/home" element={<HomePage />} />
                <Route path="/login" element={<LoginPage />} />
                <Route path="/register" element={<RegisterPage />} />
                <Route path="/grounds" element={<AllGroundsPage />} />
                <Route path="/grounds/:id" element={<GroundDetailsPage />} />
                <Route path="/view_ground/:id" element={<GroundDetailsPage />} />
                <Route path="/booking" element={<BookingPage />} />
                <Route path="/my_bookings" element={<MyBookingsPage />} />
                <Route path="/my-bookings/:bookingSku" element={<BookingDetailsPage />} />
                <Route path="/profile" element={<ProfilePage />} />
                <Route path="/password/reset" element={<PasswordResetPage />} />
                <Route path="/password/reset/:token" element={<PasswordResetPage />} />
                
                {/* Admin Routes */}
                <Route path="/admin/dashboard" element={<AdminDashboardPage />} />
                <Route path="/admin/clients" element={<AdminClientsPage />} />
                <Route path="/admin/clients/create" element={<AdminCreateClientPage />} />
                <Route path="/admin/clients/:id/edit" element={<AdminEditClientPage />} />
                <Route path="/admin/clients/:id" element={<AdminClientsPage />} />
                <Route path="/admin/grounds" element={<AdminGroundsPage />} />
                <Route path="/admin/grounds/create" element={<AdminCreateGroundPage />} />
                <Route path="/admin/grounds/:id/edit" element={<AdminEditGroundPage />} />
                <Route path="/admin/grounds/:id" element={<AdminGroundsPage />} />
                <Route path="/admin/bookings" element={<AdminBookingsPage />} />
                <Route path="/admin/bookings/:id" element={<AdminBookingsPage />} />
                <Route path="/admin/payments" element={<AdminPaymentsPage />} />
                <Route path="/admin/payments/:id" element={<AdminPaymentsPage />} />
                <Route path="/admin/users" element={<AdminUsersPage />} />
                <Route path="/admin/users/create" element={<AdminCreateUserPage />} />
                <Route path="/admin/users/:id/edit" element={<AdminEditUserPage />} />
                <Route path="/admin/users/:id" element={<AdminUsersPage />} />
                
                <Route path="/" element={<Navigate to="/home" replace />} />
            </Routes>
        </BrowserRouter>
    );
}

const root = ReactDOM.createRoot(document.getElementById('app'));
root.render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);

