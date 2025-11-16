import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
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
} from 'antd';
import { ArrowLeftOutlined } from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';

const { Title } = Typography;
const { TextArea } = Input;

export default function AdminCreateUserPage() {
	const navigate = useNavigate();
	const [form] = Form.useForm();
	const [submitting, setSubmitting] = useState(false);
	const [isMobile, setIsMobile] = useState(false);
	const [clients, setClients] = useState([]);

	useEffect(() => {
		loadClients();
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, []);

	const loadClients = async () => {
		try {
			const response = await axios.get('/admin/api/clients');
			setClients(response.data || []);
		} catch (err) {
			console.error('Error loading clients:', err);
		}
	};

	const handleSubmit = async (values) => {
		setSubmitting(true);
		try {
			const response = await axios.post('/admin/users', values);
			if (response.data.status === 'success') {
				message.success(response.data.message || 'User created successfully!');
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
				message.error(err.response?.data?.message || 'Failed to create user');
			}
		} finally {
			setSubmitting(false);
		}
	};

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
					<Title level={2} style={{ margin: 0 }}>Create New User</Title>
				</Space>

				<Card>
					<Form
						form={form}
						layout="vertical"
						onFinish={handleSubmit}
						initialValues={{
							user_type: 'user',
						}}
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
									<Input placeholder="Enter email address" />
								</Form.Item>
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
									label="Password"
									rules={[
										{ required: true, message: 'Please enter password' },
										{ min: 8, message: 'Password must be at least 8 characters' },
									]}
								>
									<Input.Password placeholder="Enter password" />
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
									Create User
								</Button>
							</Space>
						</Form.Item>
					</Form>
				</Card>
			</Space>
		</AntDesignAdminLayout>
	);
}

