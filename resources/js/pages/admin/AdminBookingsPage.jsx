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
	Select,
	DatePicker,
	InputNumber,
	Form,
	Descriptions,
	TimePicker,
} from 'antd';
import {
	PlusOutlined,
	EyeOutlined,
	DeleteOutlined,
	SearchOutlined,
} from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';
import dayjs from 'dayjs';

const { Title } = Typography;
const { Search } = Input;
const { Option } = Select;
const { TextArea } = Input;

export default function AdminBookingsPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [bookings, setBookings] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [pagination, setPagination] = useState({
		current: 1,
		pageSize: 10,
		total: 0,
	});
	const [showViewModal, setShowViewModal] = useState(false);
	const [showCreateModal, setShowCreateModal] = useState(false);
	const [viewingBooking, setViewingBooking] = useState(null);
	const [searchTerm, setSearchTerm] = useState('');
	const [statusFilter, setStatusFilter] = useState('');
	const [isMobile, setIsMobile] = useState(false);
	const [users, setUsers] = useState([]);
	const [grounds, setGrounds] = useState([]);
	const [availableSlots, setAvailableSlots] = useState([]);
	const [selectedGround, setSelectedGround] = useState(null);
	const [selectedDate, setSelectedDate] = useState(null);
	const [form] = Form.useForm();

	useEffect(() => {
		if (id) {
			loadBookingDetails(id);
		} else {
			loadBookings();
		}
		loadUsers();
		loadGrounds();
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id, pagination.current]);

	const loadBookings = async (page = 1) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get('/admin/bookings', {
				params: { page, ajax: true },
			});
			if (response.data.bookings) {
				setBookings(response.data.bookings);
				setPagination({
					current: response.data.pagination.current_page,
					pageSize: response.data.pagination.per_page,
					total: response.data.pagination.total,
				});
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load bookings');
			console.error('Error loading bookings:', err);
		} finally {
			setLoading(false);
		}
	};

	const loadBookingDetails = async (bookingId) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get(`/admin/bookings/${bookingId}/edit`);
			if (response.data.status === 'success') {
				setViewingBooking(response.data.booking);
				setShowViewModal(true);
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load booking details');
			console.error('Error loading booking:', err);
		} finally {
			setLoading(false);
		}
	};

	const loadUsers = async () => {
		try {
			const response = await axios.get('/admin/api/users');
			setUsers(response.data);
		} catch (err) {
			console.error('Error loading users:', err);
		}
	};

	const loadGrounds = async () => {
		try {
			const response = await axios.get('/admin/api/grounds');
			setGrounds(response.data);
		} catch (err) {
			console.error('Error loading grounds:', err);
		}
	};

	const loadAvailableSlots = async (groundId, date, bookingId = null) => {
		if (!groundId || !date) return;
		try {
			const response = await axios.get(`/admin/grounds/${groundId}/available-slots`, {
				params: { date, booking_id: bookingId },
			});
			if (response.data.status === 'success') {
				setAvailableSlots(response.data.slots || []);
			}
		} catch (err) {
			console.error('Error loading slots:', err);
			message.error('Failed to load available slots');
		}
	};

	const handleTableChange = (newPagination) => {
		setPagination(newPagination);
		loadBookings(newPagination.current);
	};

	const handleCreate = () => {
		setShowCreateModal(true);
		form.resetFields();
		setSelectedGround(null);
		setSelectedDate(null);
		setAvailableSlots([]);
	};

	const handleView = async (bookingId) => {
		setLoading(true);
		try {
			const response = await axios.get(`/admin/bookings/${bookingId}/edit`);
			if (response.data.status === 'success') {
				// Ensure details is an array
				const booking = response.data.booking;
				if (booking.details && !Array.isArray(booking.details)) {
					booking.details = Array.isArray(booking.details) ? booking.details : [];
				}
				setViewingBooking(booking);
				setShowViewModal(true);
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load booking details');
			console.error('Error loading booking:', err);
		} finally {
			setLoading(false);
		}
	};

	const handleDelete = async (bookingId) => {
		try {
			await axios.delete(`/admin/bookings/${bookingId}`);
			message.success('Booking deleted successfully!');
			loadBookings(pagination.current);
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to delete booking');
		}
	};

	const handleSubmit = async (values) => {
		try {
			const payload = {
				...values,
				booking_date: values.booking_date.format('Y-MM-DD'),
				booking_time: values.booking_time ? values.booking_time.format('HH:mm') : null,
			};

			await axios.post('/admin/bookings', payload);
			message.success('Booking created successfully!');
			setShowCreateModal(false);
			form.resetFields();
			setSelectedGround(null);
			setSelectedDate(null);
			setAvailableSlots([]);
			loadBookings(pagination.current);
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to save booking');
			if (err.response?.data?.errors) {
				const errors = err.response.data.errors;
				Object.keys(errors).forEach((key) => {
					form.setFields([
						{
							name: key,
							errors: errors[key],
						},
					]);
				});
			}
		}
	};

	const handleGroundChange = (groundId) => {
		setSelectedGround(groundId);
		if (groundId && selectedDate) {
			loadAvailableSlots(groundId, selectedDate.format('Y-MM-DD'));
		} else {
			setAvailableSlots([]);
		}
	};

	const handleDateChange = (date) => {
		setSelectedDate(date);
		if (selectedGround && date) {
			loadAvailableSlots(selectedGround, date.format('Y-MM-DD'));
		} else {
			setAvailableSlots([]);
		}
	};

	const filteredBookings = bookings.filter((booking) => {
		if (!searchTerm && !statusFilter) return true;
		const search = searchTerm.toLowerCase();
		const matchesSearch =
			!searchTerm ||
			(booking.booking_sku && booking.booking_sku.toLowerCase().includes(search)) ||
			(booking.user_name && booking.user_name.toLowerCase().includes(search)) ||
			(booking.user_email && booking.user_email.toLowerCase().includes(search)) ||
			(booking.ground_name && booking.ground_name.toLowerCase().includes(search));
		const matchesStatus = !statusFilter || booking.booking_status === statusFilter;
		return matchesSearch && matchesStatus;
	});

	const getStatusColor = (status) => {
		const colors = {
			pending: 'orange',
			confirmed: 'blue',
			completed: 'green',
			cancelled: 'red',
		};
		return colors[status] || 'default';
	};

	const getPaymentStatusColor = (status) => {
		const colors = {
			pending: 'orange',
			initiated: 'blue',
			processing: 'cyan',
			completed: 'green',
			failed: 'red',
			cancelled: 'red',
			refunded: 'purple',
		};
		return colors[status] || 'default';
	};

	const columns = [
		{
			title: 'SKU',
			dataIndex: 'booking_sku',
			key: 'booking_sku',
			width: 120,
		},
		{
			title: 'User',
			key: 'user',
			render: (_, record) => (
				<div>
					<div>{record.user_name || 'N/A'}</div>
					<div style={{ fontSize: '12px', color: '#999' }}>{record.user_email || ''}</div>
				</div>
			),
		},
		{
			title: 'Ground',
			dataIndex: 'ground_name',
			key: 'ground_name',
		},
		{
			title: 'Date',
			dataIndex: 'booking_date_formatted',
			key: 'booking_date',
		},
		{
			title: 'Time/Slots',
			key: 'time',
			render: (_, record) => (
				<div>
					<div>{record.booking_time || 'N/A'}</div>
					{record.slots && record.slots !== 'N/A' && (
						<div style={{ fontSize: '12px', color: '#999' }}>{record.slots}</div>
					)}
				</div>
			),
		},
		{
			title: 'Amount',
			dataIndex: 'amount_formatted',
			key: 'amount',
			align: 'right',
		},
		{
			title: 'Booking Status',
			dataIndex: 'booking_status',
			key: 'booking_status',
			render: (status) => (
				<Tag color={getStatusColor(status)}>{status?.toUpperCase() || 'N/A'}</Tag>
			),
		},
		{
			title: 'Payment Status',
			dataIndex: 'payment_status',
			key: 'payment_status',
			render: (status) => (
				<Tag color={getPaymentStatusColor(status)}>{status?.toUpperCase() || 'N/A'}</Tag>
			),
		},
		{
			title: 'Actions',
			key: 'actions',
			width: 120,
			fixed: 'right',
			render: (_, record) => (
				<Space size="small">
					<Button
						type="link"
						icon={<EyeOutlined />}
						onClick={() => handleView(record.id)}
						title="View"
					/>
					<Popconfirm
						title="Delete Booking"
						description={`Are you sure you want to delete booking ${record.booking_sku}?`}
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

	if (id && !viewingBooking) {
		return (
			<AntDesignAdminLayout>
				<Spin spinning={loading} tip="Loading booking details...">
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
			<Title level={2} style={{ marginBottom: 24 }}>Bookings</Title>

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
				title="Booking Management"
				extra={
					<Button
						type="primary"
						icon={<PlusOutlined />}
						onClick={handleCreate}
					>
						Add New Booking
					</Button>
				}
			>
				<Space direction="vertical" style={{ width: '100%' }} size="large">
					<Row gutter={16}>
						<Col xs={24} sm={12} md={12}>
							<Search
								placeholder="Search by SKU, user name, email, or ground..."
								allowClear
								enterButton={<SearchOutlined />}
								size="large"
								onSearch={setSearchTerm}
								onChange={(e) => setSearchTerm(e.target.value)}
								style={{ width: '100%' }}
							/>
						</Col>
						<Col xs={24} sm={12} md={6}>
							<Select
								placeholder="Filter by Status"
								allowClear
								size="large"
								style={{ width: '100%' }}
								onChange={setStatusFilter}
								value={statusFilter || undefined}
							>
								<Option value="pending">Pending</Option>
								<Option value="confirmed">Confirmed</Option>
								<Option value="completed">Completed</Option>
								<Option value="cancelled">Cancelled</Option>
							</Select>
						</Col>
					</Row>

					<Table
						columns={columns}
						dataSource={filteredBookings}
						rowKey="id"
						loading={loading}
						pagination={{
							...pagination,
							showSizeChanger: !isMobile,
							showTotal: (total, range) =>
								`${range[0]}-${range[1]} of ${total} bookings`,
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
				title="Booking Details"
				open={showViewModal}
				onCancel={() => {
					setShowViewModal(false);
					setViewingBooking(null);
					if (id) navigate('/admin/bookings');
				}}
				footer={[
					<Button key="close" onClick={() => {
						setShowViewModal(false);
						setViewingBooking(null);
						if (id) navigate('/admin/bookings');
					}}>
						Close
					</Button>,
				]}
				width={isMobile ? '95%' : 800}
			>
				{viewingBooking ? (
					<Descriptions bordered column={1}>
							<Descriptions.Item label="Booking SKU">
								{viewingBooking.booking_sku || 'N/A'}
							</Descriptions.Item>
							<Descriptions.Item label="User">
								{viewingBooking.user?.name || 'N/A'} ({viewingBooking.user?.email || 'N/A'})
							</Descriptions.Item>
							<Descriptions.Item label="Ground">
								{viewingBooking.details && viewingBooking.details.length > 0
									? (viewingBooking.details[0]?.ground?.name || viewingBooking.details[0]?.ground_id || 'N/A')
									: 'N/A'}
							</Descriptions.Item>
							<Descriptions.Item label="Booking Date">
								{viewingBooking.booking_date
									? dayjs(viewingBooking.booking_date).format('DD MMM YYYY')
									: 'N/A'}
							</Descriptions.Item>
							<Descriptions.Item label="Booking Time">
								{viewingBooking.details && viewingBooking.details.length > 0
									? (viewingBooking.details[0]?.time_slot || viewingBooking.details[0]?.booking_time || viewingBooking.booking_time || 'N/A')
									: (viewingBooking.booking_time || 'N/A')}
							</Descriptions.Item>
							<Descriptions.Item label="Slots">
								{viewingBooking.details && viewingBooking.details.length > 0
									? viewingBooking.details.map((detail, idx) => (
										<span key={idx}>
											{detail.slot?.slot_name || detail.slot_id || 'N/A'}
											{idx < viewingBooking.details.length - 1 && ', '}
										</span>
									))
									: 'N/A'}
							</Descriptions.Item>
							<Descriptions.Item label="Duration">
								{viewingBooking.details && viewingBooking.details.length > 0
									? (viewingBooking.details[0]?.duration || viewingBooking.duration || 'N/A')
									: (viewingBooking.duration || 'N/A')} hours
							</Descriptions.Item>
							<Descriptions.Item label="Amount">
								₹{viewingBooking.amount ? Number(viewingBooking.amount).toFixed(2) : '0.00'}
							</Descriptions.Item>
							<Descriptions.Item label="Booking Status">
								<Tag color={getStatusColor(viewingBooking.booking_status)}>
									{viewingBooking.booking_status?.toUpperCase() || 'N/A'}
								</Tag>
							</Descriptions.Item>
							<Descriptions.Item label="Payment Status">
								<Tag color={getPaymentStatusColor(viewingBooking.payment_status)}>
									{viewingBooking.payment_status?.toUpperCase() || 'N/A'}
								</Tag>
							</Descriptions.Item>
							{viewingBooking.payment && (
								<Descriptions.Item label="Transaction ID">
									{viewingBooking.payment.transaction_id || 'N/A'}
								</Descriptions.Item>
							)}
							<Descriptions.Item label="Notes">
								{viewingBooking.notes || 'N/A'}
							</Descriptions.Item>
							<Descriptions.Item label="Created At">
								{viewingBooking.created_at
									? dayjs(viewingBooking.created_at).format('DD MMM YYYY, hh:mm A')
									: 'N/A'}
							</Descriptions.Item>
						</Descriptions>
				) : (
					<Spin spinning={true} tip="Loading booking details..." />
				)}
			</Modal>

			{/* Create Modal */}
			<Modal
				title="Create New Booking"
				open={showCreateModal}
				onCancel={() => {
					setShowCreateModal(false);
					form.resetFields();
					setSelectedGround(null);
					setSelectedDate(null);
					setAvailableSlots([]);
				}}
				footer={null}
				width={isMobile ? '95%' : 700}
			>
				<Form
					form={form}
					layout="vertical"
					onFinish={handleSubmit}
					initialValues={{
						duration: 1,
						booking_status: 'pending',
						payment_status: 'pending',
					}}
				>
					<Form.Item
						name="user_id"
						label="User"
						rules={[{ required: true, message: 'Please select a user' }]}
					>
						<Select
							placeholder="Select a user"
							showSearch
							optionFilterProp="children"
							filterOption={(input, option) =>
								(option?.children ?? '').toLowerCase().includes(input.toLowerCase())
							}
						>
							{users.map((user) => (
								<Option key={user.id} value={user.id}>
									{user.name} ({user.email})
								</Option>
							))}
						</Select>
					</Form.Item>

					<Form.Item
						name="ground_id"
						label="Ground"
						rules={[{ required: true, message: 'Please select a ground' }]}
					>
						<Select
							placeholder="Select a ground"
							showSearch
							optionFilterProp="children"
							filterOption={(input, option) =>
								(option?.children ?? '').toLowerCase().includes(input.toLowerCase())
							}
							onChange={handleGroundChange}
						>
							{grounds.map((ground) => (
								<Option key={ground.id} value={ground.id}>
									{ground.name} - {ground.location}
								</Option>
							))}
						</Select>
					</Form.Item>

					<Row gutter={16}>
						<Col xs={24} sm={12}>
							<Form.Item
								name="booking_date"
								label="Booking Date"
								rules={[{ required: true, message: 'Please select a date' }]}
							>
								<DatePicker
									style={{ width: '100%' }}
									format="YYYY-MM-DD"
									disabledDate={(current) => current && current < dayjs().startOf('day')}
									onChange={handleDateChange}
								/>
							</Form.Item>
						</Col>
						<Col xs={24} sm={12}>
							<Form.Item
								name="booking_time"
								label="Booking Time"
								rules={[{ required: true, message: 'Please select a time' }]}
							>
								<TimePicker
									style={{ width: '100%' }}
									format="HH:mm"
								/>
							</Form.Item>
						</Col>
					</Row>

					<Form.Item
						name="slot_ids"
						label="Available Slots"
						rules={[{ required: true, message: 'Please select at least one slot' }]}
					>
						<Select
							mode="multiple"
							placeholder="Select slots"
							disabled={!selectedGround || !selectedDate || availableSlots.length === 0}
						>
							{availableSlots.map((slot) => (
								<Option key={slot.id} value={slot.id}>
									{slot.slot_name} - ₹{Number(slot.price_per_slot || 0).toFixed(2)}
								</Option>
							))}
						</Select>
					</Form.Item>

					<Row gutter={16}>
						<Col xs={24} sm={12}>
							<Form.Item
								name="duration"
								label="Duration (hours)"
								rules={[{ required: true, message: 'Please enter duration' }]}
							>
								<InputNumber
									min={1}
									max={24}
									style={{ width: '100%' }}
								/>
							</Form.Item>
						</Col>
						<Col xs={24} sm={12}>
							<Form.Item
								name="amount"
								label="Amount (₹)"
								rules={[{ required: true, message: 'Please enter amount' }]}
							>
								<InputNumber
									min={0}
									step={0.01}
									style={{ width: '100%' }}
									formatter={(value) => `₹ ${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
									parser={(value) => value.replace(/₹\s?|(,*)/g, '')}
								/>
							</Form.Item>
						</Col>
					</Row>

					<Row gutter={16}>
						<Col xs={24} sm={12}>
							<Form.Item
								name="booking_status"
								label="Booking Status"
								rules={[{ required: true, message: 'Please select booking status' }]}
							>
								<Select>
									<Option value="pending">Pending</Option>
									<Option value="confirmed">Confirmed</Option>
									<Option value="completed">Completed</Option>
									<Option value="cancelled">Cancelled</Option>
								</Select>
							</Form.Item>
						</Col>
						<Col xs={24} sm={12}>
							<Form.Item
								name="payment_status"
								label="Payment Status"
								rules={[{ required: true, message: 'Please select payment status' }]}
							>
								<Select>
									<Option value="pending">Pending</Option>
									<Option value="initiated">Initiated</Option>
									<Option value="processing">Processing</Option>
									<Option value="completed">Completed</Option>
									<Option value="failed">Failed</Option>
									<Option value="cancelled">Cancelled</Option>
									<Option value="refunded">Refunded</Option>
								</Select>
							</Form.Item>
						</Col>
					</Row>

					<Form.Item
						name="notes"
						label="Notes"
					>
						<TextArea rows={4} placeholder="Additional notes..." />
					</Form.Item>

					<Form.Item>
						<Space>
							<Button type="primary" htmlType="submit">
								Create Booking
							</Button>
							<Button
								onClick={() => {
									setShowCreateModal(false);
									form.resetFields();
									setSelectedGround(null);
									setSelectedDate(null);
									setAvailableSlots([]);
								}}
							>
								Cancel
							</Button>
						</Space>
					</Form.Item>
				</Form>
			</Modal>
		</AntDesignAdminLayout>
	);
}
