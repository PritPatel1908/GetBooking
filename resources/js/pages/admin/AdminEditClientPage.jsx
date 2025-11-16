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

export default function AdminEditClientPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [form] = Form.useForm();
	const [loading, setLoading] = useState(true);
	const [submitting, setSubmitting] = useState(false);
	const [isMobile, setIsMobile] = useState(false);

	useEffect(() => {
		loadClient();
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id]);

	const loadClient = async () => {
		setLoading(true);
		try {
			const response = await axios.get(`/admin/clients/${id}/edit`);
			if (response.data.status === 'success') {
				const client = response.data.client;
				form.setFieldsValue({
					first_name: client.first_name || '',
					middle_name: client.middle_name || '',
					last_name: client.last_name || '',
					email: client.email || '',
					phone: client.phone || '',
					gender: client.gender || 'male',
					full_address: client.full_address || '',
					area: client.area || '',
					city: client.city || '',
					pincode: client.pincode || '',
					state: client.state || '',
					country: client.country || 'India',
					status: client.status || 'active',
				});
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load client');
			navigate('/admin/clients');
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
				delete submitData.password_confirmation;
			}

			const response = await axios.put(`/admin/clients/${id}`, submitData);
			if (response.data.status === 'success') {
				message.success(response.data.message || 'Client updated successfully!');
				navigate('/admin/clients');
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
				message.error(err.response?.data?.message || 'Failed to update client');
			}
		} finally {
			setSubmitting(false);
		}
	};

	if (loading) {
		return (
			<AntDesignAdminLayout>
				<Spin size="large" tip="Loading client details..." style={{ 
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
						onClick={() => navigate('/admin/clients')}
					>
						Back to Clients
					</Button>
					<Title level={2} style={{ margin: 0 }}>Edit Client</Title>
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
									name="gender"
									label="Gender"
									rules={[{ required: true, message: 'Please select gender' }]}
								>
									<Select placeholder="Select gender">
										<Select.Option value="male">Male</Select.Option>
										<Select.Option value="female">Female</Select.Option>
										<Select.Option value="other">Other</Select.Option>
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
									name="password_confirmation"
									label="Confirm New Password"
									dependencies={['password']}
									rules={[
										({ getFieldValue }) => ({
											validator(_, value) {
												if (!getFieldValue('password') || !value || getFieldValue('password') === value) {
													return Promise.resolve();
												}
												return Promise.reject(new Error('Passwords do not match!'));
											},
										}),
									]}
								>
									<Input.Password placeholder="Confirm new password" />
								</Form.Item>
							</Col>
						</Row>

						<Form.Item
							name="full_address"
							label="Full Address"
						>
							<TextArea rows={3} placeholder="Enter full address" />
						</Form.Item>

						<Row gutter={16}>
							<Col xs={24} sm={8}>
								<Form.Item
									name="area"
									label="Area"
								>
									<Input placeholder="Enter area" />
								</Form.Item>
							</Col>
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
									name="pincode"
									label="Pincode"
								>
									<Input placeholder="Enter pincode" />
								</Form.Item>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="state"
									label="State"
								>
									<Input placeholder="Enter state" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="status"
									label="Status"
									rules={[{ required: true, message: 'Please select status' }]}
								>
									<Select placeholder="Select status">
										<Select.Option value="active">Active</Select.Option>
										<Select.Option value="inactive">Inactive</Select.Option>
									</Select>
								</Form.Item>
							</Col>
						</Row>

						<Form.Item>
							<Space>
								<Button onClick={() => navigate('/admin/clients')}>
									Cancel
								</Button>
								<Button type="primary" htmlType="submit" loading={submitting}>
									Update Client
								</Button>
							</Space>
						</Form.Item>
					</Form>
				</Card>
			</Space>
		</AntDesignAdminLayout>
	);
}
