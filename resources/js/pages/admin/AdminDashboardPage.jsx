import React from 'react';
import axios from 'axios';
import { Card, Row, Col, Statistic, Spin, Alert, Typography } from 'antd';
import {
	ArrowUpOutlined,
	ArrowDownOutlined,
	CalendarOutlined,
	EnvironmentOutlined,
	DollarOutlined,
	ExclamationCircleOutlined,
} from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';
import SimpleBarChart from '../../components/admin/SimpleBarChart';
import SimpleLineChart from '../../components/admin/SimpleLineChart';

const { Title } = Typography;

export default function AdminDashboardPage() {
	const [loading, setLoading] = React.useState(true);
	const [error, setError] = React.useState(null);
	const [stats, setStats] = React.useState({
		total_bookings: { value: 0, change: 0 },
		active_grounds: { value: 0, change: 0 },
		revenue: { value: 0, change: 0 },
		pending_payments: { value: 0, change: 0 },
	});

	const [barData, setBarData] = React.useState([12, 18, 9, 22, 27, 19, 31]);
	const [lineData, setLineData] = React.useState([10, 14, 13, 18, 22, 21, 28, 26, 30, 34, 33, 38]);
	const [isMobile, setIsMobile] = React.useState(false);

	React.useEffect(() => {
		fetchDashboardStats();
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, []);

	const fetchDashboardStats = async () => {
		try {
			setLoading(true);
			setError(null);
			const response = await axios.get('/api/admin/dashboard/stats');
			
			if (response.data && response.data.status === 'success') {
				const data = response.data.stats;
				
				setStats({
					total_bookings: {
						value: data.total_bookings || 0,
						change: parseFloat(data.changes?.bookings?.replace('%', '') || 0),
					},
					active_grounds: {
						value: data.active_grounds || 0,
						change: parseFloat(data.changes?.grounds?.replace('%', '') || 0),
					},
					revenue: {
						value: parseFloat(data.revenue || 0),
						change: parseFloat(data.changes?.revenue?.replace('%', '') || 0),
					},
					pending_payments: {
						value: parseFloat(data.pending_payments || 0),
						change: parseFloat(data.changes?.pending_payments?.replace('%', '') || 0),
					},
				});
			} else if (response.data && response.data.status === 'error') {
				setError(response.data.message || 'Failed to load dashboard statistics.');
			}
		} catch (err) {
			console.error('Error fetching dashboard stats:', err);
			let errorMessage = 'Failed to load dashboard statistics. Please try again.';
			if (err.response?.data?.message) {
				errorMessage = err.response.data.message;
			} else if (err.response?.data?.error) {
				errorMessage = err.response.data.error;
			} else if (err.message) {
				errorMessage = err.message;
			}
			setError(errorMessage);
		} finally {
			setLoading(false);
		}
	};

	return (
		<AntDesignAdminLayout>
			<Title level={2} style={{ marginBottom: 24 }}>Dashboard</Title>
			
			{error && (
				<Alert
					message="Error"
					description={error}
					type="error"
					showIcon
					closable
					style={{ marginBottom: 24 }}
					onClose={() => setError(null)}
				/>
			)}

			<Spin spinning={loading}>
				<Row gutter={[16, 16]}>
					<Col xs={24} sm={12} lg={6}>
						<Card>
							<Statistic
								title="Total Bookings"
								value={stats.total_bookings.value}
								prefix={<CalendarOutlined />}
								valueStyle={{ color: '#10B981' }}
								suffix={
									stats.total_bookings.change !== 0 && (
										<span style={{ fontSize: 14, marginLeft: 8 }}>
											{stats.total_bookings.change > 0 ? (
												<ArrowUpOutlined style={{ color: '#10B981' }} />
											) : (
												<ArrowDownOutlined style={{ color: '#EF4444' }} />
											)}
											{Math.abs(stats.total_bookings.change)}%
										</span>
									)
								}
							/>
						</Card>
					</Col>
					<Col xs={24} sm={12} lg={6}>
						<Card>
							<Statistic
								title="Active Grounds"
								value={stats.active_grounds.value}
								prefix={<EnvironmentOutlined />}
								valueStyle={{ color: '#8B5CF6' }}
								suffix={
									stats.active_grounds.change !== 0 && (
										<span style={{ fontSize: 14, marginLeft: 8 }}>
											{stats.active_grounds.change > 0 ? (
												<ArrowUpOutlined style={{ color: '#10B981' }} />
											) : (
												<ArrowDownOutlined style={{ color: '#EF4444' }} />
											)}
											{Math.abs(stats.active_grounds.change)}%
										</span>
									)
								}
							/>
						</Card>
					</Col>
					<Col xs={24} sm={12} lg={6}>
						<Card>
							<Statistic
								title="Revenue"
								value={stats.revenue.value}
								prefix={<DollarOutlined />}
								precision={2}
								valueStyle={{ color: '#F59E0B' }}
								suffix={
									stats.revenue.change !== 0 && (
										<span style={{ fontSize: 14, marginLeft: 8 }}>
											{stats.revenue.change > 0 ? (
												<ArrowUpOutlined style={{ color: '#10B981' }} />
											) : (
												<ArrowDownOutlined style={{ color: '#EF4444' }} />
											)}
											{Math.abs(stats.revenue.change)}%
										</span>
									)
								}
							/>
						</Card>
					</Col>
					<Col xs={24} sm={12} lg={6}>
						<Card>
							<Statistic
								title="Pending Payments"
								value={stats.pending_payments.value}
								prefix={<ExclamationCircleOutlined />}
								precision={2}
								valueStyle={{ color: '#EF4444' }}
								suffix={
									stats.pending_payments.change !== 0 && (
										<span style={{ fontSize: 14, marginLeft: 8 }}>
											{stats.pending_payments.change > 0 ? (
												<ArrowUpOutlined style={{ color: '#10B981' }} />
											) : (
												<ArrowDownOutlined style={{ color: '#EF4444' }} />
											)}
											{Math.abs(stats.pending_payments.change)}%
										</span>
									)
								}
							/>
						</Card>
					</Col>
				</Row>

				<Row gutter={[16, 16]} style={{ marginTop: 24 }}>
					<Col xs={24} sm={24} lg={16}>
						<Card title="Bookings Trend" style={{ height: '100%' }}>
							<SimpleLineChart data={lineData} height={isMobile ? 200 : 220} />
						</Card>
					</Col>
					<Col xs={24} sm={24} lg={8}>
						<Card title="Weekly Revenue" style={{ height: '100%' }}>
							<SimpleBarChart data={barData} height={isMobile ? 200 : 220} />
						</Card>
					</Col>
				</Row>
			</Spin>
		</AntDesignAdminLayout>
	);
}
