import React, { useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import axios from 'axios';
import AdminHeader from './AdminHeader';
import AdminSidebar from './AdminSidebar';

/**
 * Simple Admin Layout Component
 * 
 * A clean, minimal layout for the admin panel using the simple header and sidebar components.
 * 
 * Usage:
 * <SimpleAdminLayout title="Dashboard">
 *   <YourContent />
 * </SimpleAdminLayout>
 */
export default function SimpleAdminLayout({ children, title = 'Dashboard' }) {
	const [sidebarOpen, setSidebarOpen] = useState(false);
	const [user, setUser] = useState(null);
	const [loading, setLoading] = useState(true);
	const [isMobile, setIsMobile] = useState(false);
	const location = useLocation();

	useEffect(() => {
		fetchUser();
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, []);

	const fetchUser = async () => {
		try {
			const response = await axios.get('/api/user');
			setUser(response.data);
		} catch (error) {
			console.error('Error fetching user:', error);
		} finally {
			setLoading(false);
		}
	};

	const getPageTitle = () => {
		const pathTitles = {
			'/admin/dashboard': 'Dashboard',
			'/admin/clients': 'Clients',
			'/admin/grounds': 'Grounds',
			'/admin/bookings': 'Bookings',
			'/admin/payments': 'Payments',
			'/admin/users': 'Users',
			'/admin/contact-messages': 'Messages',
		};

		return pathTitles[location.pathname] || title;
	};

	if (loading) {
		return (
			<div style={{
				display: 'flex',
				justifyContent: 'center',
				alignItems: 'center',
				height: '100vh',
				background: '#f9fafb'
			}}>
				<div className="admin-spinner"></div>
			</div>
		);
	}

	return (
		<div style={{ display: 'flex', minHeight: '100vh' }}>
			<AdminSidebar 
				isOpen={sidebarOpen}
				onClose={() => setSidebarOpen(false)}
			/>

			<div style={{
				marginLeft: isMobile ? 0 : '250px',
				flex: 1,
				display: 'flex',
				flexDirection: 'column',
				minHeight: '100vh',
				background: '#f9fafb'
			}}>
				<AdminHeader
					title={getPageTitle()}
					user={user}
					onMenuClick={() => setSidebarOpen(!sidebarOpen)}
				/>

				<main style={{
					flex: 1,
					padding: '1.5rem',
					overflowY: 'auto'
				}}>
					{children}
				</main>
			</div>
		</div>
	);
}

