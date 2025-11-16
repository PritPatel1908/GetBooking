import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import {
	Layout,
	Menu,
	Avatar,
	Dropdown,
	Button,
	Space,
	Badge,
	theme,
	ConfigProvider,
	Drawer,
} from 'antd';
import {
	MenuFoldOutlined,
	MenuUnfoldOutlined,
	DashboardOutlined,
	TeamOutlined,
	EnvironmentOutlined,
	CalendarOutlined,
	DollarOutlined,
	UserOutlined,
	MailOutlined,
	LogoutOutlined,
	BellOutlined,
} from '@ant-design/icons';
import axios from 'axios';
import './AntDesignAdminLayout.css';

const { Header, Sider, Content } = Layout;

export default function AntDesignAdminLayout({ children }) {
	const [collapsed, setCollapsed] = useState(false);
	const [user, setUser] = useState(null);
	const [loading, setLoading] = useState(true);
	const [isMobile, setIsMobile] = useState(false);
	const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
	const navigate = useNavigate();
	const location = useLocation();
	const {
		token: { colorBgContainer, borderRadiusLG },
	} = theme.useToken();

	useEffect(() => {
		fetchUser();
		const checkMobile = () => {
			const mobile = window.innerWidth < 768;
			setIsMobile(mobile);
			if (mobile) {
				setCollapsed(true);
			}
		};
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, []);

	const fetchUser = async () => {
		try {
			const response = await axios.get('/api/user');
			if (response.data?.user_type !== 'admin') {
				navigate('/home');
				return;
			}
			setUser(response.data);
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

	const menuItems = [
		{
			key: '/admin/dashboard',
			icon: <DashboardOutlined />,
			label: 'Dashboard',
		},
		{
			key: '/admin/clients',
			icon: <TeamOutlined />,
			label: 'Clients',
		},
		{
			key: '/admin/grounds',
			icon: <EnvironmentOutlined />,
			label: 'Grounds',
		},
		{
			key: '/admin/bookings',
			icon: <CalendarOutlined />,
			label: 'Bookings',
		},
		{
			key: '/admin/payments',
			icon: <DollarOutlined />,
			label: 'Payments',
		},
		{
			key: '/admin/users',
			icon: <UserOutlined />,
			label: 'Users',
		},
		{
			key: '/admin/contact-messages',
			icon: <MailOutlined />,
			label: 'Messages',
		},
	];

	const userMenuItems = [
		{
			key: 'profile',
			label: 'Profile',
			icon: <UserOutlined />,
		},
		{
			type: 'divider',
		},
		{
			key: 'logout',
			label: 'Logout',
			icon: <LogoutOutlined />,
			danger: true,
			onClick: handleLogout,
		},
	];

	const handleMenuClick = ({ key }) => {
		navigate(key);
		if (isMobile) {
			setMobileMenuOpen(false);
		}
	};

	const handleToggleMenu = () => {
		if (isMobile) {
			setMobileMenuOpen(!mobileMenuOpen);
		} else {
			setCollapsed(!collapsed);
		}
	};

	if (loading) {
		return (
			<div style={{
				display: 'flex',
				justifyContent: 'center',
				alignItems: 'center',
				height: '100vh',
				background: '#f0f2f5'
			}}>
				<div className="ant-spin ant-spin-lg" />
			</div>
		);
	}

	const siderContent = (
		<>
			<div style={{
				height: 64,
				margin: 16,
				display: 'flex',
				alignItems: 'center',
				justifyContent: collapsed && !isMobile ? 'center' : 'flex-start',
				gap: 12,
				fontSize: collapsed && !isMobile ? 20 : 18,
				fontWeight: 'bold',
				color: '#6366F1',
			}}>
				<span style={{ fontSize: 24 }}>⚽</span>
				{(!collapsed || isMobile) && <span>Get Booking</span>}
			</div>
			<Menu
				mode="inline"
				selectedKeys={[location.pathname]}
				items={menuItems}
				onClick={handleMenuClick}
				style={{ borderRight: 0 }}
			/>
		</>
	);

	return (
		<ConfigProvider
			theme={{
				token: {
					colorPrimary: '#6366F1',
					borderRadius: 6,
				},
			}}
		>
			<Layout style={{ minHeight: '100vh' }}>
				{/* Desktop Sider */}
				{!isMobile && (
					<Sider
						trigger={null}
						collapsible
						collapsed={collapsed}
						style={{
							overflow: 'auto',
							height: '100vh',
							position: 'fixed',
							left: 0,
							top: 0,
							bottom: 0,
						}}
						theme="light"
					>
						{siderContent}
					</Sider>
				)}

				{/* Mobile Drawer */}
				{isMobile && (
					<Drawer
						title="Get Booking"
						placement="left"
						onClose={() => setMobileMenuOpen(false)}
						open={mobileMenuOpen}
						bodyStyle={{ padding: 0 }}
						width={250}
					>
						{siderContent}
					</Drawer>
				)}

				<Layout style={{ 
					marginLeft: isMobile ? 0 : (collapsed ? 80 : 200), 
					transition: 'margin-left 0.2s' 
				}}>
					<Header
						style={{
							padding: isMobile ? '0 16px' : '0 24px',
							background: colorBgContainer,
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'space-between',
							boxShadow: '0 2px 8px rgba(0,0,0,0.06)',
							position: 'sticky',
							top: 0,
							zIndex: 1000,
						}}
					>
						<Button
							type="text"
							icon={isMobile ? <MenuUnfoldOutlined /> : (collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />)}
							onClick={handleToggleMenu}
							style={{
								fontSize: 16,
								width: isMobile ? 48 : 64,
								height: isMobile ? 48 : 64,
							}}
						/>
						<Space size="middle">
							{!isMobile && (
								<Badge count={0} showZero>
									<Button
										type="text"
										icon={<BellOutlined />}
										style={{ fontSize: 18 }}
									/>
								</Badge>
							)}
							<Dropdown
								menu={{ items: userMenuItems }}
								placement="bottomRight"
								arrow
							>
								<Space style={{ cursor: 'pointer' }}>
									<Avatar
										src={user?.profile_photo_path || `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || 'Admin')}&background=6366F1&color=fff`}
										icon={<UserOutlined />}
										size={isMobile ? 'default' : 'large'}
									/>
									{!isMobile && !collapsed && (
										<span style={{ fontWeight: 500 }}>
											{user?.name || 'Admin'}
										</span>
									)}
								</Space>
							</Dropdown>
						</Space>
					</Header>
					<Content
						style={{
							margin: isMobile ? '16px 8px' : '24px 16px',
							padding: isMobile ? 16 : 24,
							minHeight: 280,
							background: colorBgContainer,
							borderRadius: borderRadiusLG,
						}}
					>
						{children}
					</Content>
				</Layout>
			</Layout>
		</ConfigProvider>
	);
}

