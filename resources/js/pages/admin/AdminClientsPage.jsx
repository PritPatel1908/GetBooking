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
} from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';

const { Title } = Typography;
const { Search } = Input;

export default function AdminClientsPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [clients, setClients] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [pagination, setPagination] = useState({
		current: 1,
		pageSize: 10,
		total: 0,
	});
	const [showViewModal, setShowViewModal] = useState(false);
	const [viewingClient, setViewingClient] = useState(null);
	const [searchTerm, setSearchTerm] = useState('');
	const [isMobile, setIsMobile] = useState(false);

	useEffect(() => {
		if (id) {
			loadClientDetails(id);
		} else {
			loadClients();
		}
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id, pagination.current]);

	const loadClients = async (page = 1) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get('/admin/clients', {
				params: { page, ajax: true },
			});
			if (response.data.clients) {
				setClients(response.data.clients);
				setPagination({
					current: response.data.pagination.current_page,
					pageSize: response.data.pagination.per_page,
					total: response.data.pagination.total,
				});
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load clients');
			console.error('Error loading clients:', err);
		} finally {
			setLoading(false);
		}
	};

	const loadClientDetails = async (clientId) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get(`/admin/clients/${clientId}/view`);
			if (response.data.status === 'success') {
				setViewingClient(response.data.client);
				setShowViewModal(true);
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load client details');
			console.error('Error loading client:', err);
		} finally {
			setLoading(false);
		}
	};

	const handleTableChange = (newPagination) => {
		setPagination(newPagination);
		loadClients(newPagination.current);
	};

	const handleCreate = () => {
		navigate('/admin/clients/create');
	};

	const handleEdit = (clientId) => {
		navigate(`/admin/clients/${clientId}/edit`);
	};

	const handleView = async (clientId) => {
		try {
			const response = await axios.get(`/admin/clients/${clientId}/view`);
			if (response.data.status === 'success') {
				setViewingClient(response.data.client);
				setShowViewModal(true);
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load client details');
		}
	};

	const handleDelete = async (clientId) => {
		try {
			await axios.delete(`/admin/clients/${clientId}`);
			message.success('Client deleted successfully!');
			loadClients(pagination.current);
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to delete client');
			console.error('Error deleting client:', err);
		}
	};

	const filteredClients = clients.filter((client) => {
		if (!searchTerm) return true;
		const search = searchTerm.toLowerCase();
		return (
			(client.name && client.name.toLowerCase().includes(search)) ||
			(client.email && client.email.toLowerCase().includes(search)) ||
			(client.phone && client.phone.includes(search))
		);
	});

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
						src={record.profile_picture}
						icon={!record.profile_picture && <SearchOutlined />}
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
			title: 'Gender',
			dataIndex: 'gender',
			key: 'gender',
			render: (gender) => {
				const colors = {
					male: 'blue',
					female: 'pink',
				};
				return gender ? (
					<Tag color={colors[gender] || 'default'}>{gender.toUpperCase()}</Tag>
				) : (
					'N/A'
				);
			},
		},
		{
			title: 'Status',
			dataIndex: 'status',
			key: 'status',
			render: (status) => (
				<Tag color={status === 'active' ? 'green' : 'red'}>
					{status?.toUpperCase() || 'N/A'}
				</Tag>
			),
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
						title="Delete Client"
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

	if (id && !viewingClient) {
		return (
			<AntDesignAdminLayout>
				<Spin spinning={loading} tip="Loading client details...">
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
			<Title level={2} style={{ marginBottom: 24 }}>Clients</Title>
			
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
				title="Client Management"
				extra={
					<Button
						type="primary"
						icon={<PlusOutlined />}
						onClick={handleCreate}
					>
						Add New Client
					</Button>
				}
			>
				<Space direction="vertical" style={{ width: '100%' }} size="large">
					<Search
						placeholder="Search clients by name, email, or phone..."
						allowClear
						enterButton={<SearchOutlined />}
						size="large"
						onSearch={setSearchTerm}
						onChange={(e) => setSearchTerm(e.target.value)}
						style={{ maxWidth: isMobile ? '100%' : 400 }}
					/>

					<Table
						columns={columns}
						dataSource={filteredClients}
						rowKey="id"
						loading={loading}
						pagination={{
							...pagination,
							showSizeChanger: !isMobile,
							showTotal: (total, range) =>
								`${range[0]}-${range[1]} of ${total} clients`,
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
				title="Client Details"
				open={showViewModal}
				onCancel={() => setShowViewModal(false)}
				footer={[
					<Button key="close" onClick={() => setShowViewModal(false)}>
						Close
					</Button>,
					<Button
						key="edit"
						type="primary"
						onClick={() => {
							setShowViewModal(false);
							navigate(`/admin/clients/${viewingClient?.id}/edit`);
						}}
					>
						Edit Client
					</Button>,
				]}
				width={isMobile ? '95%' : 600}
			>
				{viewingClient && (
					<Row gutter={[16, 16]}>
						{viewingClient.profile_picture && (
							<Col span={24} style={{ textAlign: 'center' }}>
								<Avatar
									size={120}
									src={viewingClient.profile_picture}
									icon={<SearchOutlined />}
								/>
							</Col>
						)}
						<Col xs={24} sm={12}>
							<strong>Name:</strong>
							<div>{viewingClient.name || 'N/A'}</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Email:</strong>
							<div>{viewingClient.email || 'N/A'}</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Phone:</strong>
							<div>{viewingClient.phone || 'N/A'}</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Gender:</strong>
							<div>
								<Tag color={viewingClient.gender === 'male' ? 'blue' : 'pink'}>
									{viewingClient.gender?.toUpperCase() || 'N/A'}
								</Tag>
							</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Status:</strong>
							<div>
								<Tag color={viewingClient.status === 'active' ? 'green' : 'red'}>
									{viewingClient.status?.toUpperCase() || 'N/A'}
								</Tag>
							</div>
						</Col>
						<Col xs={24} sm={12}>
							<strong>Registration Date:</strong>
							<div>{viewingClient.registration_date || 'N/A'}</div>
						</Col>
						<Col span={24}>
							<strong>Address:</strong>
							<div>
								{viewingClient.full_address || 'N/A'}
								{viewingClient.area && `, ${viewingClient.area}`}
								{viewingClient.city && `, ${viewingClient.city}`}
								{viewingClient.pincode && ` - ${viewingClient.pincode}`}
								{viewingClient.state && `, ${viewingClient.state}`}
								{viewingClient.country && `, ${viewingClient.country}`}
							</div>
						</Col>
					</Row>
				)}
			</Modal>
		</AntDesignAdminLayout>
	);
}
