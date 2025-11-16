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

export default function AdminCreateClientPage() {
	const navigate = useNavigate();
	const [form] = Form.useForm();
	const [submitting, setSubmitting] = useState(false);
	const [isMobile, setIsMobile] = useState(false);

	useEffect(() => {
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, []);

	const handleSubmit = async (values) => {
		setSubmitting(true);
		try {
			const response = await axios.post('/admin/clients/create', values);
			if (response.data.status === 'success') {
				message.success(response.data.message || 'Client created successfully!');
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
				message.error(err.response?.data?.message || 'Failed to create client');
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
						onClick={() => navigate('/admin/clients')}
					>
						Back to Clients
					</Button>
					<Title level={2} style={{ margin: 0 }}>Create New Client</Title>
				</Space>

				<Card>
					<Form
						form={form}
						layout="vertical"
						onFinish={handleSubmit}
						initialValues={{
							gender: 'male',
							status: 'active',
							country: 'India',
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
									label="Password"
									rules={[
										{ required: true, message: 'Please enter password' },
										{ min: 6, message: 'Password must be at least 6 characters' },
									]}
								>
									<Input.Password placeholder="Enter password" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="password_confirmation"
									label="Confirm Password"
									dependencies={['password']}
									rules={[
										{ required: true, message: 'Please confirm password' },
										({ getFieldValue }) => ({
											validator(_, value) {
												if (!value || getFieldValue('password') === value) {
													return Promise.resolve();
												}
												return Promise.reject(new Error('Passwords do not match!'));
											},
										}),
									]}
								>
									<Input.Password placeholder="Confirm password" />
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
									Create Client
								</Button>
							</Space>
						</Form.Item>
					</Form>
				</Card>
			</Space>
		</AntDesignAdminLayout>
	);
}
