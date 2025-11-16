import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import {
	Table,
	Button,
	Space,
	Input,
	Modal,
	Card,
	Tag,
	Avatar,
	Row,
	Col,
	Typography,
	Popconfirm,
	message,
	Spin,
	Alert,
} from 'antd';
import {
	PlusOutlined,
	EditOutlined,
	EyeOutlined,
	DeleteOutlined,
	SearchOutlined,
	UserOutlined,
} from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';

const { Title } = Typography;
const { Search } = Input;

export default function AdminUsersPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [users, setUsers] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [pagination, setPagination] = useState({
		current: 1,
		pageSize: 10,
		total: 0,
	});
	const [showViewModal, setShowViewModal] = useState(false);
	const [viewingUser, setViewingUser] = useState(null);
	const [searchTerm, setSearchTerm] = useState('');
	const [isMobile, setIsMobile] = useState(false);

	useEffect(() => {
		if (id) {
			loadUserDetails(id);
		} else {
			loadUsers();
		}
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id, pagination.current]);

	const loadUsers = async (page = 1) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get('/admin/users', {
				params: { page, ajax: true },
			});
			if (response.data.users) {
				setUsers(response.data.users);
				setPagination({
					current: response.data.pagination.current_page,
					pageSize: response.data.pagination.per_page,
					total: response.data.pagination.total,
				});
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load users');
			console.error('Error loading users:', err);
		} finally {
			setLoading(false);
		}
	};

	const loadUserDetails = async (userId) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get(`/admin/users/${userId}/view`);
			if (response.data.status === 'success') {
				setViewingUser(response.data.user);
				setShowViewModal(true);
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load user details');
			console.error('Error loading user:', err);
		} finally {
			setLoading(false);
		}
	};

	const handleTableChange = (newPagination) => {
		setPagination(newPagination);
		loadUsers(newPagination.current);
	};

	const handleCreate = () => {
		navigate('/admin/users/create');
	};

	const handleEdit = (userId) => {
		navigate(`/admin/users/${userId}/edit`);
	};

	const handleView = async (userId) => {
		try {
			const response = await axios.get(`/admin/users/${userId}/view`);
			if (response.data.status === 'success') {
				setViewingUser(response.data.user);
				setShowViewModal(true);
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load user details');
		}
	};

	const handleDelete = async (userId) => {
		try {
			await axios.delete(`/admin/users/${userId}`);
			message.success('User deleted successfully!');
			loadUsers(pagination.current);
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to delete user');
			console.error('Error deleting user:', err);
		}
	};

	const handleSearch = async (value) => {
		setSearchTerm(value);
		setLoading(true);
		try {
			const response = await axios.get('/admin/users/pagination', {
				params: { search: value, per_page: pagination.pageSize, page: 1 },
			});
			if (response.data.users) {
				setUsers(response.data.users);
				setPagination({
					current: response.data.pagination.current_page,
					pageSize: response.data.pagination.per_page,
					total: response.data.pagination.total,
				});
			}
		} catch (err) {
			message.error('Failed to search users');
			console.error('Error searching users:', err);
		} finally {
			setLoading(false);
		}
	};

	const getUserTypeColor = (userType) => {
		const colors = {
			admin: 'red',
			client: 'blue',
			user: 'green',
		};
		return colors[userType] || 'default';
	};

	const columns = [
		{
			title: 'ID',
			dataIndex: 'id',
			key: 'id',
			width: 80,
		},
		{
			title: 'Name',
			dataIndex: 'name',
			key: 'name',
			render: (text, record) => (
				<Space>
					<Avatar
						icon={<UserOutlined />}
						src={record.profile_photo_path}
					>
						{text?.charAt(0)}
					</Avatar>
					<span>{text || 'N/A'}</span>
				</Space>
			),
		},
		{
			title: 'Email',
			dataIndex: 'email',
			key: 'email',
		},
		{
			title: 'Phone',
			dataIndex: 'phone',
			key: 'phone',
		},
		{
			title: 'User Type',
			dataIndex: 'user_type',
			key: 'user_type',
			render: (userType) => {
				return userType ? (
					<Tag color={getUserTypeColor(userType)}>
						{userType.toUpperCase()}
					</Tag>
				) : (
					'N/A'
				);
			},
		},
		{
			title: 'Role',
			dataIndex: 'role',
			key: 'role',
			render: (role) => role || 'N/A',
		},
		{
			title: 'Bookings',
			dataIndex: 'bookings_count',
			key: 'bookings_count',
			render: (count) => count || 0,
		},
		{
			title: 'Last Booking',
			dataIndex: 'last_booking_date',
			key: 'last_booking_date',
			render: (date) => date || 'N/A',
		},
		{
			title: 'Created',
			dataIndex: 'created_at',
			key: 'created_at',
		},
		{
			title: 'Actions',
			key: 'actions',
			width: 150,
			render: (_, record) => (
				<Space size="small">
					<Button
						type="link"
						icon={<EyeOutlined />}
						onClick={() => handleView(record.id)}
						title="View"
					/>
					<Button
						type="link"
						icon={<EditOutlined />}
						onClick={() => handleEdit(record.id)}
						title="Edit"
					/>
					<Popconfirm
						title="Delete User"
						description={`Are you sure you want to delete ${record.name}?`}
						onConfirm={() => handleDelete(record.id)}
						okText="Yes"
						cancelText="No"
						okButtonProps={{ danger: true }}
					>
						<Button
							type="link"
							danger
							icon={<DeleteOutlined />}
							title="Delete"
						/>
					</Popconfirm>
				</Space>
			),
		},
	];

	if (id && !viewingUser) {
		return (
			<AntDesignAdminLayout>
				<Spin spinning={loading} tip="Loading user details...">
					{error && (
						<Alert
							message="Error"
							description={error}
							type="error"
							showIcon
							style={{ marginBottom: 24 }}
						/>
					)}
				</Spin>
			</AntDesignAdminLayout>
		);
	}

	return (
		<AntDesignAdminLayout>
			<Title level={2} style={{ marginBottom: 24 }}>Users</Title>
			
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

			<Card
				title="User Management"
				extra={
					<Button
						type="primary"
						icon={<PlusOutlined />}
						onClick={handleCreate}
					>
						Add New User
					</Button>
				}
			>
				<Space direction="vertical" style={{ width: '100%' }} size="large">
					<Search
						placeholder="Search users by name, email, or phone..."
						allowClear
						enterButton={<SearchOutlined />}
						size="large"
						onSearch={handleSearch}
						onChange={(e) => {
							if (!e.target.value) {
								loadUsers(1);
								setSearchTerm('');
							}
						}}
						style={{ maxWidth: isMobile ? '100%' : 400 }}
					/>

					<Table
						columns={columns}
						dataSource={users}
						rowKey="id"
						loading={loading}
						pagination={{
							...pagination,
							showSizeChanger: !isMobile,
							showTotal: (total, range) =>
								`${range[0]}-${range[1]} of ${total} users`,
							pageSizeOptions: ['10', '20', '50', '100'],
							responsive: true,
						}}
						onChange={handleTableChange}
						scroll={{ x: 'max-content' }}
						size={isMobile ? 'small' : 'middle'}
					/>
				</Space>
			</Card>

			{/* View Modal */}
			<Modal
				title="User Details"
				open={showViewModal}
				onCancel={() => {
					setShowViewModal(false);
					setViewingUser(null);
					if (id) {
						navigate('/admin/users');
					}
				}}
				footer={[
					<Button key="close" onClick={() => {
						setShowViewModal(false);
						setViewingUser(null);
						if (id) {
							navigate('/admin/users');
						}
					}}>
						Close
					</Button>,
					<Button
						key="edit"
						type="primary"
						onClick={() => {
							setShowViewModal(false);
							navigate(`/admin/users/${viewingUser?.id}/edit`);
						}}
					>
						Edit User
					</Button>,
				]}
				width={isMobile ? '95%' : 600}
			>
				{viewingUser && (
					<Row gutter={[16, 16]}>
						<Col span={24} style={{ textAlign: 'center' }}>
							<Avatar
								size={120}
								icon={<UserOutlined />}
								src={viewingUser.profile_photo_path}
							>
								{viewingUser.name?.charAt(0)}
							</Avatar>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Name:</strong>
							<div>{viewingUser.name || 'N/A'}</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Email:</strong>
							<div>{viewingUser.email || 'N/A'}</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Phone:</strong>
							<div>{viewingUser.phone || 'N/A'}</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>User Type:</strong>
							<div>
								<Tag color={getUserTypeColor(viewingUser.user_type)}>
									{viewingUser.user_type?.toUpperCase() || 'N/A'}
								</Tag>
							</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Role:</strong>
							<div>{viewingUser.role || 'N/A'}</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Created:</strong>
							<div>{viewingUser.created_at || 'N/A'}</div>
						</Col>
						<Col span={24}>
							<strong>Address:</strong>
							<div>
								{viewingUser.address || 'N/A'}
								{viewingUser.city && `, ${viewingUser.city}`}
								{viewingUser.state && `, ${viewingUser.state}`}
								{viewingUser.postal_code && ` - ${viewingUser.postal_code}`}
								{viewingUser.country && `, ${viewingUser.country}`}
							</div>
						</Col>
						{viewingUser.bookings && viewingUser.bookings.length > 0 && (
							<Col span={24}>
								<strong>Recent Bookings:</strong>
								<div>
									{viewingUser.bookings.slice(0, 5).map((booking) => (
										<div key={booking.id} style={{ marginTop: 8 }}>
											Booking #{booking.id} - {booking.created_at}
										</div>
									))}
								</div>
							</Col>
						)}
					</Row>
				)}
			</Modal>
		</AntDesignAdminLayout>
	);
}
