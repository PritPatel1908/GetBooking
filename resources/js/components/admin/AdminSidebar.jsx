import React from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import axios from 'axios';

export default function AdminSidebar({ isOpen, onClose }) {
	const location = useLocation();
	const navigate = useNavigate();

	const navItems = [
		{ path: '/admin/dashboard', icon: 'fa-home', label: 'Dashboard' },
		{ path: '/admin/clients', icon: 'fa-users', label: 'Clients' },
		{ path: '/admin/grounds', icon: 'fa-map-marker-alt', label: 'Grounds' },
		{ path: '/admin/bookings', icon: 'fa-calendar-check', label: 'Bookings' },
		{ path: '/admin/payments', icon: 'fa-money-bill-wave', label: 'Payments' },
		{ path: '/admin/users', icon: 'fa-user-cog', label: 'Users' },
		{ path: '/admin/contact-messages', icon: 'fa-envelope', label: 'Messages' },
	];

	const isActive = (path) => {
		return location.pathname === path || location.pathname.startsWith(path + '/');
	};

	const handleLogout = async () => {
		try {
			await axios.post('/logout');
			navigate('/login');
		} catch (error) {
			console.error('Logout error:', error);
		}
	};

	return (
		<>
			{isOpen && (
				<div 
					className="admin-sidebar-overlay"
					onClick={onClose}
				/>
			)}
			<aside className={`admin-sidebar-simple ${isOpen ? 'open' : ''}`}>
				<div className="admin-sidebar-simple-content">
					<div className="admin-sidebar-logo">
						<div className="admin-sidebar-logo-icon">
							<i className="fas fa-futbol"></i>
						</div>
						<span className="admin-sidebar-logo-text">Get Booking</span>
					</div>

					<nav className="admin-sidebar-nav">
						{navItems.map((item) => (
							<Link
								key={item.path}
								to={item.path}
								className={`admin-sidebar-nav-item ${isActive(item.path) ? 'active' : ''}`}
								onClick={onClose}
							>
								<i className={`fas ${item.icon}`}></i>
								<span>{item.label}</span>
							</Link>
						))}

						<button
							onClick={handleLogout}
							className="admin-sidebar-nav-item admin-sidebar-logout"
						>
							<i className="fas fa-sign-out-alt"></i>
							<span>Logout</span>
						</button>
					</nav>
				</div>
			</aside>
		</>
	);
}


