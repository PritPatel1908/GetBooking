import React from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

export default function AdminHeader({ title, user, onMenuClick }) {
	const navigate = useNavigate();

	const handleLogout = async () => {
		try {
			await axios.post('/logout');
			navigate('/login');
		} catch (error) {
			console.error('Logout error:', error);
		}
	};

	return (
		<header className="admin-header-simple">
			<div className="admin-header-simple-content">
				<div className="admin-header-left">
					{onMenuClick && (
						<button 
							className="admin-menu-toggle"
							onClick={onMenuClick}
							aria-label="Toggle menu"
						>
							<i className="fas fa-bars"></i>
						</button>
					)}
					<h1 className="admin-header-title">{title || 'Dashboard'}</h1>
				</div>
				
				<div className="admin-header-right">
					<div className="admin-header-user">
						{user && (
							<>
								<div className="admin-header-user-info">
									<span className="admin-header-user-name">{user.name || 'Admin'}</span>
									<span className="admin-header-user-role">Administrator</span>
								</div>
								<div className="admin-header-user-avatar">
									<img
										src={user.profile_photo_path || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name || 'Admin')}&background=6366F1&color=fff&size=32`}
										alt={user.name || 'Admin'}
									/>
								</div>
								<button 
									className="admin-header-logout"
									onClick={handleLogout}
									title="Logout"
								>
									<i className="fas fa-sign-out-alt"></i>
								</button>
							</>
						)}
					</div>
				</div>
			</div>
		</header>
	);
}


