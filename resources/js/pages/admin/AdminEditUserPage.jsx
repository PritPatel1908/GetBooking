import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import {
	Card,
	Form,
	Input,
	Select,
	Button,
	Row,
	Col,
	Typography,
	message,
	Space,
	Spin,
	Alert,
} from 'antd';
import { ArrowLeftOutlined } from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';

const { Title } = Typography;
const { TextArea } = Input;

export default function AdminEditUserPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [form] = Form.useForm();
	const [loading, setLoading] = useState(true);
	const [submitting, setSubmitting] = useState(false);
	const [isMobile, setIsMobile] = useState(false);
	const [clients, setClients] = useState([]);

	useEffect(() => {
		loadUser();
		loadClients();
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id]);

	const loadClients = async () => {
		try {
			const response = await axios.get('/admin/api/clients');
			setClients(response.data || []);
		} catch (err) {
			console.error('Error loading clients:', err);
		}
	};

	const loadUser = async () => {
		setLoading(true);
		try {
			const response = await axios.get(`/admin/users/${id}/edit`);
			if (response.data.status === 'success') {
				const user = response.data.user;
				// Split name into first, middle, last
				const nameParts = (user.name || '').split(' ');
				const firstName = nameParts[0] || '';
				const lastName = nameParts[nameParts.length - 1] || '';
				const middleName = nameParts.slice(1, -1).join(' ') || '';

				form.setFieldsValue({
					first_name: firstName,
					middle_name: middleName,
					last_name: lastName,
					email: user.email || '',
					phone: user.phone || '',
					user_type: user.user_type || 'user',
					address: user.address || '',
					city: user.city || '',
					state: user.state || '',
					country: user.country || '',
					postal_code: user.postal_code || '',
					client_id: user.client_id || null,
					role: user.role || '',
				});
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load user');
			navigate('/admin/users');
		} finally {
			setLoading(false);
		}
	};

	const handleSubmit = async (values) => {
		setSubmitting(true);
		try {
			const submitData = { ...values };
			// Remove password fields if they're empty during edit
			if (!submitData.password) {
				delete submitData.password;
			}

			const response = await axios.put(`/admin/users/${id}`, submitData);
			if (response.data.status === 'success') {
				message.success(response.data.message || 'User updated successfully!');
				navigate('/admin/users');
			}
		} catch (err) {
			if (err.response?.status === 422) {
				const errors = err.response.data.errors || {};
				Object.keys(errors).forEach((key) => {
					form.setFields([
						{
							name: key,
							errors: errors[key],
						},
					]);
				});
			} else {
				message.error(err.response?.data?.message || 'Failed to update user');
			}
		} finally {
			setSubmitting(false);
		}
	};

	if (loading) {
		return (
			<AntDesignAdminLayout>
				<Spin size="large" tip="Loading user details..." style={{ 
					display: 'flex', 
					justifyContent: 'center', 
					alignItems: 'center', 
					height: '400px' 
				}} />
			</AntDesignAdminLayout>
		);
	}

	return (
		<AntDesignAdminLayout>
			<Space direction="vertical" size="large" style={{ width: '100%' }}>
				<Space>
					<Button
						icon={<ArrowLeftOutlined />}
						onClick={() => navigate('/admin/users')}
					>
						Back to Users
					</Button>
					<Title level={2} style={{ margin: 0 }}>Edit User</Title>
				</Space>

				<Card>
					<Form
						form={form}
						layout="vertical"
						onFinish={handleSubmit}
						size={isMobile ? 'small' : 'middle'}
					>
						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="first_name"
									label="First Name"
									rules={[{ required: true, message: 'Please enter first name' }]}
								>
									<Input placeholder="Enter first name" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="middle_name"
									label="Middle Name"
								>
									<Input placeholder="Enter middle name (optional)" />
								</Form.Item>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="last_name"
									label="Last Name"
									rules={[{ required: true, message: 'Please enter last name' }]}
								>
									<Input placeholder="Enter last name" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="email"
									label="Email"
									rules={[
										{ required: true, message: 'Please enter email' },
										{ type: 'email', message: 'Please enter a valid email' },
									]}
								>
									<Input placeholder="Enter email address" disabled />
								</Form.Item>
								<Alert
									message="Email cannot be changed"
									type="info"
									showIcon
									style={{ marginTop: -16, marginBottom: 16 }}
								/>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="phone"
									label="Phone"
									rules={[{ required: true, message: 'Please enter phone number' }]}
								>
									<Input placeholder="Enter phone number" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="user_type"
									label="User Type"
									rules={[{ required: true, message: 'Please select user type' }]}
								>
									<Select placeholder="Select user type">
										<Select.Option value="user">User</Select.Option>
										<Select.Option value="admin">Admin</Select.Option>
										<Select.Option value="client">Client</Select.Option>
									</Select>
								</Form.Item>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="password"
									label="New Password (leave blank to keep current)"
								>
									<Input.Password placeholder="Enter new password (optional)" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="client_id"
									label="Client (Optional)"
									tooltip="Associate this user with a client"
								>
									<Select
										placeholder="Select client (optional)"
										allowClear
									>
										{clients.map((client) => (
											<Select.Option key={client.id} value={client.id}>
												{client.name} ({client.email})
											</Select.Option>
										))}
									</Select>
								</Form.Item>
							</Col>
						</Row>

						<Form.Item
							name="address"
							label="Address"
						>
							<TextArea rows={3} placeholder="Enter address" />
						</Form.Item>

						<Row gutter={16}>
							<Col xs={24} sm={8}>
								<Form.Item
									name="city"
									label="City"
								>
									<Input placeholder="Enter city" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={8}>
								<Form.Item
									name="state"
									label="State"
								>
									<Input placeholder="Enter state" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={8}>
								<Form.Item
									name="postal_code"
									label="Postal Code"
								>
									<Input placeholder="Enter postal code" />
								</Form.Item>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="country"
									label="Country"
								>
									<Input placeholder="Enter country" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="role"
									label="Role (Optional)"
								>
									<Input placeholder="Enter role" />
								</Form.Item>
							</Col>
						</Row>

						<Form.Item>
							<Space>
								<Button onClick={() => navigate('/admin/users')}>
									Cancel
								</Button>
								<Button type="primary" htmlType="submit" loading={submitting}>
									Update User
								</Button>
							</Space>
						</Form.Item>
					</Form>
				</Card>
			</Space>
		</AntDesignAdminLayout>
	);
}

