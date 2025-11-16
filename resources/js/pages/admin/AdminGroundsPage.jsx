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
	Typography,
	Popconfirm,
	message,
	Spin,
	Alert,
	Row,
	Col,
	Image,
	Descriptions,
} from 'antd';
import {
	PlusOutlined,
	EditOutlined,
	EyeOutlined,
	DeleteOutlined,
	SearchOutlined,
	EnvironmentOutlined,
} from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';

const { Title } = Typography;
const { Search } = Input;

export default function AdminGroundsPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [grounds, setGrounds] = useState([]);
	const [clients, setClients] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [pagination, setPagination] = useState({
		current: 1,
		pageSize: 10,
		total: 0,
	});
	const [showViewModal, setShowViewModal] = useState(false);
	const [viewingGround, setViewingGround] = useState(null);
	const [searchTerm, setSearchTerm] = useState('');
	const [isMobile, setIsMobile] = useState(false);

	useEffect(() => {
		loadClients();
		if (id) {
			loadGroundDetails(id);
		} else {
			loadGrounds();
		}
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id, pagination.current]);

	const loadClients = async () => {
		try {
			const response = await axios.get('/admin/api/clients');
			setClients(response.data);
		} catch (err) {
			console.error('Error loading clients:', err);
		}
	};

	const loadGrounds = async (page = 1) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get('/admin/grounds', {
				params: { page, ajax: true },
			});
			if (response.data.grounds) {
				setGrounds(response.data.grounds);
				setPagination({
					current: response.data.pagination.current_page,
					pageSize: response.data.pagination.per_page,
					total: response.data.pagination.total,
				});
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load grounds');
			console.error('Error loading grounds:', err);
		} finally {
			setLoading(false);
		}
	};

	const loadGroundDetails = async (groundId) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get(`/admin/grounds/${groundId}/view`);
			if (response.data.status === 'success') {
				setViewingGround(response.data.ground);
				setShowViewModal(true);
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load ground details');
			console.error('Error loading ground:', err);
		} finally {
			setLoading(false);
		}
	};

	const handleTableChange = (newPagination) => {
		setPagination(newPagination);
		loadGrounds(newPagination.current);
	};

	const handleCreate = () => {
		navigate('/admin/grounds/create');
	};

	const handleEdit = (groundId) => {
		navigate(`/admin/grounds/${groundId}/edit`);
	};

	const handleView = async (groundId) => {
		try {
			const response = await axios.get(`/admin/grounds/${groundId}/view`);
			if (response.data.status === 'success') {
				setViewingGround(response.data.ground);
				setShowViewModal(true);
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load ground details');
		}
	};

	const handleDelete = async (groundId) => {
		try {
			await axios.delete(`/admin/grounds/${groundId}`);
			message.success('Ground deleted successfully!');
			loadGrounds(pagination.current);
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to delete ground');
			console.error('Error deleting ground:', err);
		}
	};

	const filteredGrounds = grounds.filter((ground) => {
		if (!searchTerm) return true;
		const search = searchTerm.toLowerCase();
		return (
			(ground.name && ground.name.toLowerCase().includes(search)) ||
			(ground.location && ground.location.toLowerCase().includes(search)) ||
			(ground.email && ground.email.toLowerCase().includes(search))
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
				<Space direction="vertical" size="small">
					<span style={{ fontWeight: 600 }}>{text}</span>
					<Space size="small">
						{record.is_featured && (
							<Tag color="green">Featured</Tag>
						)}
						{record.is_new && (
							<Tag color="blue">New</Tag>
						)}
					</Space>
				</Space>
			),
		},
		{
			title: 'Location',
			dataIndex: 'location',
			key: 'location',
			render: (text) => (
				<Space>
					<EnvironmentOutlined />
					<span>{text}</span>
				</Space>
			),
		},
		{
			title: 'Client',
			key: 'client',
			render: (_, record) => {
				const client = clients.find(c => c.id === record.client_id);
				return client ? client.name : 'N/A';
			},
		},
		{
			title: 'Capacity',
			dataIndex: 'capacity',
			key: 'capacity',
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
						title="Delete Ground"
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

	if (id && !viewingGround) {
		return (
			<AntDesignAdminLayout>
				<Spin spinning={loading} tip="Loading ground details...">
					{error && (
						<Alert
							message="Error"
							description={error}
							type="error"
							showIcon
							style={{ marginBottom: 24 }}
							action={
								<Button size="small" onClick={() => navigate('/admin/grounds')}>
									Back to Grounds
								</Button>
							}
						/>
					)}
				</Spin>
			</AntDesignAdminLayout>
		);
	}

	return (
		<AntDesignAdminLayout>
			<Title level={2} style={{ marginBottom: 24 }}>Grounds</Title>
			
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
				title="Ground Management"
				extra={
					<Button
						type="primary"
						icon={<PlusOutlined />}
						onClick={handleCreate}
					>
						Add New Ground
					</Button>
				}
			>
				<Space direction="vertical" style={{ width: '100%' }} size="large">
					<Search
						placeholder="Search grounds by name, location, or email..."
						allowClear
						enterButton={<SearchOutlined />}
						size="large"
						onSearch={setSearchTerm}
						onChange={(e) => setSearchTerm(e.target.value)}
						style={{ maxWidth: isMobile ? '100%' : 400 }}
					/>

					<Table
						columns={columns}
						dataSource={filteredGrounds}
						rowKey="id"
						loading={loading}
						pagination={{
							...pagination,
							showSizeChanger: !isMobile,
							showTotal: (total, range) =>
								`${range[0]}-${range[1]} of ${total} grounds`,
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
				title="Ground Details"
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
							navigate(`/admin/grounds/${viewingGround?.id}/edit`);
						}}
					>
						Edit Ground
					</Button>,
				]}
				width={isMobile ? '95%' : 800}
			>
				{viewingGround && (
					<>
						<Descriptions 
							title="Basic Information" 
							bordered 
							column={isMobile ? 1 : 2} 
							style={{ marginBottom: 24 }}
						>
							<Descriptions.Item label="Name" span={2}>
								{viewingGround.name}
							</Descriptions.Item>
							<Descriptions.Item label="Location">
								<Space>
									<EnvironmentOutlined />
									{viewingGround.location}
								</Space>
							</Descriptions.Item>
							<Descriptions.Item label="Capacity">
								{viewingGround.capacity}
							</Descriptions.Item>
							<Descriptions.Item label="Ground Type">
								{viewingGround.ground_type || 'N/A'}
							</Descriptions.Item>
							<Descriptions.Item label="Ground Category">
								{viewingGround.ground_category || 'N/A'}
							</Descriptions.Item>
							<Descriptions.Item label="Status">
								<Tag color={viewingGround.status === 'active' ? 'green' : 'red'}>
									{viewingGround.status?.toUpperCase() || 'N/A'}
								</Tag>
							</Descriptions.Item>
							<Descriptions.Item label="Flags" span={2}>
								<Space>
									{viewingGround.is_featured && <Tag color="green">Featured</Tag>}
									{viewingGround.is_new && <Tag color="blue">New</Tag>}
								</Space>
							</Descriptions.Item>
							{viewingGround.description && (
								<Descriptions.Item label="Description" span={2}>
									{viewingGround.description}
								</Descriptions.Item>
							)}
							{viewingGround.rules && (
								<Descriptions.Item label="Rules" span={2}>
									{viewingGround.rules}
								</Descriptions.Item>
							)}
							{viewingGround.opening_time && (
								<Descriptions.Item label="Opening Time">
									{viewingGround.opening_time}
								</Descriptions.Item>
							)}
							{viewingGround.closing_time && (
								<Descriptions.Item label="Closing Time">
									{viewingGround.closing_time}
								</Descriptions.Item>
							)}
							{viewingGround.phone && (
								<Descriptions.Item label="Phone">
									{viewingGround.phone}
								</Descriptions.Item>
							)}
							{viewingGround.email && (
								<Descriptions.Item label="Email">
									{viewingGround.email}
								</Descriptions.Item>
							)}
						</Descriptions>

						{viewingGround.images && viewingGround.images.length > 0 && (
							<div style={{ marginTop: 24 }}>
								<Title level={5} style={{ marginBottom: 16 }}>Images</Title>
								<Image.PreviewGroup>
									<Row gutter={[16, 16]}>
										{viewingGround.images.map((img) => (
											<Col key={img.id} xs={12} sm={8} md={6}>
												<Image
													src={img.image_path}
													alt="Ground"
													style={{
														width: '100%',
														height: '150px',
														objectFit: 'cover',
														borderRadius: 4,
													}}
												/>
											</Col>
										))}
									</Row>
								</Image.PreviewGroup>
							</div>
						)}
					</>
				)}
			</Modal>
		</AntDesignAdminLayout>
	);
}
