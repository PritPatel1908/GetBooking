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
	message,
	Spin,
	Alert,
	Row,
	Col,
	Select,
	Form,
	Descriptions,
	InputNumber,
} from 'antd';
import {
	EyeOutlined,
	SearchOutlined,
	ReloadOutlined,
} from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';
import dayjs from 'dayjs';

const { Title } = Typography;
const { Search } = Input;
const { Option } = Select;
const { TextArea } = Input;

export default function AdminPaymentsPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [payments, setPayments] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);
	const [pagination, setPagination] = useState({
		current: 1,
		pageSize: 10,
		total: 0,
	});
	const [showViewModal, setShowViewModal] = useState(false);
	const [showStatusModal, setShowStatusModal] = useState(false);
	const [showRefundModal, setShowRefundModal] = useState(false);
	const [viewingPayment, setViewingPayment] = useState(null);
	const [updatingPayment, setUpdatingPayment] = useState(null);
	const [searchTerm, setSearchTerm] = useState('');
	const [statusFilter, setStatusFilter] = useState('');
	const [isMobile, setIsMobile] = useState(false);
	const [statusForm] = Form.useForm();
	const [refundForm] = Form.useForm();

	useEffect(() => {
		if (id) {
			loadPaymentDetails(id);
		} else {
			loadPayments();
		}
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id, pagination.current]);

	const loadPayments = async (page = 1) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get('/admin/payments', {
				params: { page, ajax: true },
			});
			if (response.data.payments) {
				setPayments(response.data.payments);
				setPagination({
					current: response.data.pagination.current_page,
					pageSize: response.data.pagination.per_page,
					total: response.data.pagination.total,
				});
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load payments');
			console.error('Error loading payments:', err);
		} finally {
			setLoading(false);
		}
	};

	const loadPaymentDetails = async (paymentId) => {
		setLoading(true);
		setError(null);
		try {
			const response = await axios.get(`/admin/payments/${paymentId}/view`);
			if (response.data.status === 'success') {
				setViewingPayment(response.data.payment);
				setShowViewModal(true);
			}
		} catch (err) {
			setError(err.response?.data?.message || 'Failed to load payment details');
			console.error('Error loading payment:', err);
		} finally {
			setLoading(false);
		}
	};

	const handleTableChange = (newPagination) => {
		setPagination(newPagination);
		loadPayments(newPagination.current);
	};

	const handleView = async (paymentId) => {
		setLoading(true);
		try {
			const response = await axios.get(`/admin/payments/${paymentId}/view`);
			if (response.data.status === 'success') {
				setViewingPayment(response.data.payment);
				setShowViewModal(true);
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load payment details');
			console.error('Error loading payment:', err);
		} finally {
			setLoading(false);
		}
	};

	const handleStatusUpdate = (payment) => {
		setUpdatingPayment(payment);
		statusForm.setFieldsValue({
			payment_status: payment.payment_status,
		});
		setShowStatusModal(true);
	};

	const handleStatusSubmit = async (values) => {
		try {
			await axios.put(`/admin/payments/${updatingPayment.id}`, values);
			message.success('Payment status updated successfully!');
			setShowStatusModal(false);
			setUpdatingPayment(null);
			statusForm.resetFields();
			loadPayments(pagination.current);
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to update payment status');
			if (err.response?.data?.errors) {
				const errors = err.response.data.errors;
				Object.keys(errors).forEach((key) => {
					statusForm.setFields([
						{
							name: key,
							errors: errors[key],
						},
					]);
				});
			}
		}
	};

	const handleRefund = (payment) => {
		setUpdatingPayment(payment);
		refundForm.setFieldsValue({
			transaction_id: payment.transaction_id,
			amount: payment.amount,
			reason: '',
		});
		setShowRefundModal(true);
	};

	const handleRefundSubmit = async (values) => {
		try {
			const response = await axios.post(`/admin/payments/${updatingPayment.id}/refund`, values);
			if (response.data.status === 'success') {
				message.success('Refund initiated successfully!');
				setShowRefundModal(false);
				setUpdatingPayment(null);
				refundForm.resetFields();
				loadPayments(pagination.current);
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to process refund');
			if (err.response?.data?.errors) {
				const errors = err.response.data.errors;
				Object.keys(errors).forEach((key) => {
					refundForm.setFields([
						{
							name: key,
							errors: errors[key],
						},
					]);
				});
			}
		}
	};

	const filteredPayments = payments.filter((payment) => {
		if (!searchTerm && !statusFilter) return true;
		const search = searchTerm.toLowerCase();
		const matchesSearch =
			!searchTerm ||
			(payment.transaction_id && payment.transaction_id.toLowerCase().includes(search)) ||
			(payment.user_name && payment.user_name.toLowerCase().includes(search)) ||
			(payment.user_email && payment.user_email.toLowerCase().includes(search)) ||
			(payment.booking_sku && payment.booking_sku.toLowerCase().includes(search)) ||
			(payment.ground_name && payment.ground_name.toLowerCase().includes(search));
		const matchesStatus = !statusFilter || payment.payment_status === statusFilter;
		return matchesSearch && matchesStatus;
	});

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
			title: 'Transaction ID',
			dataIndex: 'transaction_id',
			key: 'transaction_id',
			width: 180,
			render: (text) => text || 'N/A',
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
			title: 'Booking SKU',
			dataIndex: 'booking_sku',
			key: 'booking_sku',
		},
		{
			title: 'Ground',
			dataIndex: 'ground_name',
			key: 'ground_name',
		},
		{
			title: 'Date',
			dataIndex: 'date_formatted',
			key: 'date',
		},
		{
			title: 'Amount',
			dataIndex: 'amount_formatted',
			key: 'amount',
			align: 'right',
		},
		{
			title: 'Payment Method',
			dataIndex: 'payment_method',
			key: 'payment_method',
			render: (method) => method ? method.toUpperCase() : 'N/A',
		},
		{
			title: 'Status',
			dataIndex: 'payment_status',
			key: 'payment_status',
			render: (status) => (
				<Tag color={getPaymentStatusColor(status)}>{status?.toUpperCase() || 'N/A'}</Tag>
			),
		},
		{
			title: 'Actions',
			key: 'actions',
			width: 200,
			fixed: 'right',
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
						icon={<ReloadOutlined />}
						onClick={() => handleStatusUpdate(record)}
						title="Update Status"
					/>
					{record.payment_status === 'completed' && (
						<Button
							type="link"
							danger
							onClick={() => handleRefund(record)}
							title="Refund"
						>
							Refund
						</Button>
					)}
				</Space>
			),
		},
	];

	if (id && !viewingPayment) {
		return (
			<AntDesignAdminLayout>
				<Spin spinning={loading} tip="Loading payment details...">
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
			<Title level={2} style={{ marginBottom: 24 }}>Payments</Title>

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

			<Card title="Payment Management">
				<Space direction="vertical" style={{ width: '100%' }} size="large">
					<Row gutter={16}>
						<Col xs={24} sm={12} md={12}>
							<Search
								placeholder="Search by transaction ID, user, booking SKU, or ground..."
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
								<Option value="initiated">Initiated</Option>
								<Option value="processing">Processing</Option>
								<Option value="completed">Completed</Option>
								<Option value="failed">Failed</Option>
								<Option value="cancelled">Cancelled</Option>
								<Option value="refunded">Refunded</Option>
							</Select>
						</Col>
					</Row>

					<Table
						columns={columns}
						dataSource={filteredPayments}
						rowKey="id"
						loading={loading}
						pagination={{
							...pagination,
							showSizeChanger: !isMobile,
							showTotal: (total, range) =>
								`${range[0]}-${range[1]} of ${total} payments`,
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
				title="Payment Details"
				open={showViewModal}
				onCancel={() => {
					setShowViewModal(false);
					setViewingPayment(null);
					if (id) navigate('/admin/payments');
				}}
				footer={[
					<Button key="close" onClick={() => {
						setShowViewModal(false);
						setViewingPayment(null);
						if (id) navigate('/admin/payments');
					}}>
						Close
					</Button>,
				]}
				width={isMobile ? '95%' : 800}
			>
				{viewingPayment ? (
					<Descriptions bordered column={1}>
						<Descriptions.Item label="Transaction ID">
							{viewingPayment.transaction_id || 'N/A'}
						</Descriptions.Item>
						<Descriptions.Item label="User">
							{viewingPayment.user?.name || 'N/A'} ({viewingPayment.user?.email || 'N/A'})
							{viewingPayment.user?.phone && (
								<div style={{ fontSize: '12px', color: '#999', marginTop: 4 }}>
									Phone: {viewingPayment.user.phone}
								</div>
							)}
						</Descriptions.Item>
						{viewingPayment.booking && (
							<>
								<Descriptions.Item label="Booking SKU">
									{viewingPayment.booking.booking_sku || 'N/A'}
								</Descriptions.Item>
								<Descriptions.Item label="Booking Date">
									{viewingPayment.booking.booking_date_formatted || 'N/A'}
								</Descriptions.Item>
								{viewingPayment.booking.details && viewingPayment.booking.details.length > 0 && (
									<>
										<Descriptions.Item label="Ground">
											{viewingPayment.booking.details[0]?.ground?.name || 'N/A'}
											{viewingPayment.booking.details[0]?.ground?.location && (
												<div style={{ fontSize: '12px', color: '#999', marginTop: 4 }}>
													{viewingPayment.booking.details[0].ground.location}
												</div>
											)}
										</Descriptions.Item>
										<Descriptions.Item label="Slots">
											{viewingPayment.booking.details.map((detail, idx) => (
												<div key={idx}>
													{detail.slot?.slot_name || 'N/A'}
													{idx < viewingPayment.booking.details.length - 1 && ', '}
												</div>
											))}
										</Descriptions.Item>
									</>
								)}
							</>
						)}
						<Descriptions.Item label="Payment Date">
							{viewingPayment.date_formatted || 'N/A'}
						</Descriptions.Item>
						<Descriptions.Item label="Amount">
							{viewingPayment.amount_formatted || 'N/A'}
						</Descriptions.Item>
						<Descriptions.Item label="Payment Method">
							{viewingPayment.payment_method ? viewingPayment.payment_method.toUpperCase() : 'N/A'}
						</Descriptions.Item>
						<Descriptions.Item label="Payment Type">
							{viewingPayment.payment_type || 'N/A'}
						</Descriptions.Item>
						<Descriptions.Item label="Payment Status">
							<Tag color={getPaymentStatusColor(viewingPayment.payment_status)}>
								{viewingPayment.payment_status?.toUpperCase() || 'N/A'}
							</Tag>
						</Descriptions.Item>
						{viewingPayment.payment_response_data_json && (
							<Descriptions.Item label="Refund Details">
								<div>
									{viewingPayment.payment_response_data_json.refund_id && (
										<div>Refund ID: {viewingPayment.payment_response_data_json.refund_id}</div>
									)}
									{viewingPayment.payment_response_data_json.refund_amount && (
										<div>Refund Amount: ₹{Number(viewingPayment.payment_response_data_json.refund_amount).toFixed(2)}</div>
									)}
									{viewingPayment.payment_response_data_json.refund_reason && (
										<div>Reason: {viewingPayment.payment_response_data_json.refund_reason}</div>
									)}
									{viewingPayment.payment_response_data_json.refund_date && (
										<div>Refund Date: {viewingPayment.payment_response_data_json.refund_date}</div>
									)}
								</div>
							</Descriptions.Item>
						)}
						{viewingPayment.payment_response_message && (
							<Descriptions.Item label="Response Message">
								{viewingPayment.payment_response_message}
							</Descriptions.Item>
						)}
						<Descriptions.Item label="Created At">
							{viewingPayment.created_at || 'N/A'}
						</Descriptions.Item>
						<Descriptions.Item label="Updated At">
							{viewingPayment.updated_at || 'N/A'}
						</Descriptions.Item>
					</Descriptions>
				) : (
					<Spin spinning={true} tip="Loading payment details..." />
				)}
			</Modal>

			{/* Status Update Modal */}
			<Modal
				title="Update Payment Status"
				open={showStatusModal}
				onCancel={() => {
					setShowStatusModal(false);
					setUpdatingPayment(null);
					statusForm.resetFields();
				}}
				footer={null}
				width={isMobile ? '95%' : 500}
			>
				<Form
					form={statusForm}
					layout="vertical"
					onFinish={handleStatusSubmit}
				>
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

					<Form.Item>
						<Space>
							<Button type="primary" htmlType="submit">
								Update Status
							</Button>
							<Button
								onClick={() => {
									setShowStatusModal(false);
									setUpdatingPayment(null);
									statusForm.resetFields();
								}}
							>
								Cancel
							</Button>
						</Space>
					</Form.Item>
				</Form>
			</Modal>

			{/* Refund Modal */}
			<Modal
				title="Process Refund"
				open={showRefundModal}
				onCancel={() => {
					setShowRefundModal(false);
					setUpdatingPayment(null);
					refundForm.resetFields();
				}}
				footer={null}
				width={isMobile ? '95%' : 600}
			>
				<Form
					form={refundForm}
					layout="vertical"
					onFinish={handleRefundSubmit}
				>
					<Form.Item
						name="transaction_id"
						label="Transaction ID"
						rules={[{ required: true, message: 'Transaction ID is required' }]}
					>
						<Input disabled />
					</Form.Item>

					<Form.Item
						name="amount"
						label="Refund Amount (₹)"
						rules={[
							{ required: true, message: 'Please enter refund amount' },
							{ type: 'number', min: 1, message: 'Amount must be at least ₹1' },
						]}
					>
						<InputNumber
							min={1}
							max={updatingPayment?.amount || 999999}
							step={0.01}
							style={{ width: '100%' }}
							formatter={(value) => `₹ ${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
							parser={(value) => value.replace(/₹\s?|(,*)/g, '')}
						/>
					</Form.Item>

					<Form.Item
						name="reason"
						label="Refund Reason"
						rules={[{ required: true, message: 'Please select a refund reason' }]}
					>
						<Select placeholder="Select refund reason">
							<Option value="duplicate">Duplicate Payment</Option>
							<Option value="fraudulent">Fraudulent Transaction</Option>
							<Option value="requested_by_customer">Requested by Customer</Option>
							<Option value="defective_product">Defective Product/Service</Option>
							<Option value="not_received">Product/Service Not Received</Option>
							<Option value="cancelled">Order Cancelled</Option>
							<Option value="other">Other</Option>
						</Select>
					</Form.Item>

					<Form.Item
						noStyle
						shouldUpdate={(prevValues, currentValues) => prevValues.reason !== currentValues.reason}
					>
						{({ getFieldValue }) =>
							getFieldValue('reason') === 'other' ? (
								<Form.Item
									name="other_reason"
									label="Other Reason"
									rules={[{ required: true, message: 'Please specify the reason' }]}
								>
									<TextArea rows={3} placeholder="Please specify the refund reason..." />
								</Form.Item>
							) : null
						}
					</Form.Item>

					<Alert
						message="Note"
						description="Refunds are processed through Razorpay and may take 5-7 business days to reflect in the customer's account."
						type="info"
						showIcon
						style={{ marginBottom: 16 }}
					/>

					<Form.Item>
						<Space>
							<Button type="primary" danger htmlType="submit">
								Process Refund
							</Button>
							<Button
								onClick={() => {
									setShowRefundModal(false);
									setUpdatingPayment(null);
									refundForm.resetFields();
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
