import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import {
	Card,
	Form,
	Input,
	InputNumber,
	Select,
	Button,
	Row,
	Col,
	Typography,
	message,
	Space,
	Switch,
	TimePicker,
	Upload,
	Image,
	Spin,
} from 'antd';
import {
	ArrowLeftOutlined,
	PlusOutlined,
	DeleteOutlined,
	UploadOutlined,
} from '@ant-design/icons';
import AntDesignAdminLayout from '../../components/admin/AntDesignAdminLayout';
import dayjs from 'dayjs';

const { Title } = Typography;
const { TextArea } = Input;

export default function AdminEditGroundPage() {
	const { id } = useParams();
	const navigate = useNavigate();
	const [form] = Form.useForm();
	const [clients, setClients] = useState([]);
	const [loading, setLoading] = useState(true);
	const [submitting, setSubmitting] = useState(false);
	const [imageList, setImageList] = useState([]);
	const [existingImages, setExistingImages] = useState([]);
	const [deleteImages, setDeleteImages] = useState([]);
	const [isMobile, setIsMobile] = useState(false);

	useEffect(() => {
		loadClients();
		loadGround();
		const checkMobile = () => setIsMobile(window.innerWidth < 768);
		checkMobile();
		window.addEventListener('resize', checkMobile);
		return () => window.removeEventListener('resize', checkMobile);
	}, [id]);

	const loadClients = async () => {
		try {
			const response = await axios.get('/admin/api/clients');
			setClients(response.data);
		} catch (err) {
			console.error('Error loading clients:', err);
		}
	};

	const loadGround = async () => {
		setLoading(true);
		try {
			const response = await axios.get(`/admin/grounds/${id}/edit`);
			if (response.data.status === 'success') {
				const ground = response.data.ground;
				
				// Set existing images
				if (ground.images && ground.images.length > 0) {
					setExistingImages(ground.images);
				}

				// Prepare form values
				const formValues = {
					name: ground.name || '',
					location: ground.location || '',
					capacity: ground.capacity || 0,
					ground_type: ground.ground_type || '',
					ground_category: ground.ground_category || '',
					description: ground.description || '',
					rules: ground.rules || '',
					opening_time: ground.opening_time ? dayjs(ground.opening_time, 'HH:mm') : null,
					closing_time: ground.closing_time ? dayjs(ground.closing_time, 'HH:mm') : null,
					phone: ground.phone || '',
					email: ground.email || '',
					status: ground.status || 'active',
					client_id: ground.client_id || '',
					is_new: ground.is_new || false,
					is_featured: ground.is_featured || false,
					features: ground.features && ground.features.length > 0
						? ground.features.map(f => ({
							feature_name: f.feature_name || '',
							feature_type: f.feature_type || 'facility',
							feature_status: f.feature_status || 'active',
						}))
						: [{ feature_name: '', feature_type: 'facility', feature_status: 'active' }],
					slots: ground.slots && ground.slots.length > 0
						? ground.slots.map(slot => ({
							slot_name: slot.slot_name || '',
							start_time: slot.start_time ? dayjs(slot.start_time, 'HH:mm') : null,
							end_time: slot.end_time ? dayjs(slot.end_time, 'HH:mm') : null,
							slot_type: slot.slot_type || 'morning',
							day_of_week: slot.day_of_week || '',
							slot_status: slot.slot_status || 'active',
							price_per_slot: slot.price_per_slot || 0,
						}))
						: [{ slot_name: '', slot_type: 'morning', slot_status: 'active', price_per_slot: 0 }],
				};

				// Reset form with new values to clear any validation errors
				form.resetFields();
				form.setFieldsValue(formValues);
			}
		} catch (err) {
			message.error(err.response?.data?.message || 'Failed to load ground');
			navigate('/admin/grounds');
		} finally {
			setLoading(false);
		}
	};

	const handleSubmit = async (values) => {
		setSubmitting(true);
		try {
			const submitData = new FormData();
			
			// Basic fields - String types
			submitData.append('name', values.name || '');
			submitData.append('location', values.location || '');
			submitData.append('ground_type', values.ground_type || '');
			submitData.append('ground_category', values.ground_category || '');
			submitData.append('description', values.description || '');
			submitData.append('rules', values.rules || '');
			submitData.append('phone', values.phone || '');
			submitData.append('email', values.email || '');
			submitData.append('status', values.status || 'active');
			
			// Integer type
			submitData.append('capacity', values.capacity || 0);
			
			// Integer type (client_id)
			submitData.append('client_id', values.client_id || '');
			
			// Boolean types
			submitData.append('is_new', values.is_new ? 1 : 0);
			submitData.append('is_featured', values.is_featured ? 1 : 0);
			
			// Time types (format as HH:mm)
			submitData.append('opening_time', values.opening_time ? dayjs(values.opening_time).format('HH:mm') : '');
			submitData.append('closing_time', values.closing_time ? dayjs(values.closing_time).format('HH:mm') : '');

			// Add delete images
			if (deleteImages.length > 0) {
				deleteImages.forEach((imgId) => {
					submitData.append('delete_images[]', imgId);
				});
			}

			// Add new images
			if (imageList.length > 0) {
				imageList.forEach((file) => {
					if (file.originFileObj) {
						submitData.append('ground_images[]', file.originFileObj);
					}
				});
			}

			// Add features
			if (values.features) {
				values.features.forEach((feature, index) => {
					if (feature?.feature_name) {
						submitData.append(`feature_name[${index}]`, feature.feature_name);
						submitData.append(`feature_type[${index}]`, feature.feature_type || 'facility');
						submitData.append(`feature_status[${index}]`, feature.feature_status || 'active');
					}
				});
			}

			// Add slots
			if (values.slots) {
				values.slots.forEach((slot, index) => {
					if (slot?.slot_name || (slot?.start_time && slot?.end_time)) {
						submitData.append(`slot_name[${index}]`, slot.slot_name || '');
						submitData.append(`start_time[${index}]`, slot.start_time ? dayjs(slot.start_time).format('HH:mm') : '');
						submitData.append(`end_time[${index}]`, slot.end_time ? dayjs(slot.end_time).format('HH:mm') : '');
						submitData.append(`slot_type[${index}]`, slot.slot_type || 'morning');
						submitData.append(`day_of_week[${index}]`, slot.day_of_week || '');
						submitData.append(`slot_status[${index}]`, slot.slot_status || 'active');
						// Decimal type with 2 decimal places
						submitData.append(`price_per_slot[${index}]`, slot.price_per_slot || '0.00');
					}
				});
			}

			// Laravel does not parse multipart on true PUT reliably; spoof method over POST
			submitData.append('_method', 'PUT');
			const response = await axios.post(`/admin/grounds/${id}`, submitData);

			if (response.data.status === 'success') {
				message.success(response.data.message || 'Ground updated successfully!');
				navigate('/admin/grounds');
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
				message.error(err.response?.data?.message || 'Failed to update ground');
			}
		} finally {
			setSubmitting(false);
		}
	};

	const handleImageChange = ({ fileList }) => {
		setImageList(fileList);
	};

	const handleRemoveExistingImage = (imageId) => {
		setExistingImages(existingImages.filter(img => img.id !== imageId));
		setDeleteImages([...deleteImages, imageId]);
	};

	if (loading) {
		return (
			<AntDesignAdminLayout>
				<Spin size="large" tip="Loading ground details..." style={{ 
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
						onClick={() => navigate('/admin/grounds')}
					>
						Back to Grounds
					</Button>
					<Title level={2} style={{ margin: 0 }}>Edit Ground</Title>
				</Space>

				<Form
					form={form}
					layout="vertical"
					onFinish={handleSubmit}
					validateTrigger="onSubmit"
					preserve={false}
					size={isMobile ? 'small' : 'middle'}
				>
					{/* Basic Information */}
					<Card title="Basic Information" style={{ marginBottom: 24 }}>
						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="name"
									label="Ground Name"
									rules={[{ required: true, message: 'Please enter ground name' }]}
									validateTrigger="onSubmit"
								>
									<Input placeholder="Enter ground name" maxLength={255} />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="location"
									label="Location"
									rules={[{ required: true, message: 'Please enter location' }]}
									validateTrigger="onSubmit"
								>
									<Input placeholder="Enter location" maxLength={255} />
								</Form.Item>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="client_id"
									label="Client"
									rules={[{ required: true, message: 'Please select client' }]}
									validateTrigger="onSubmit"
								>
									<Select placeholder="Select Client" showSearch optionFilterProp="children">
										{clients.map((client) => (
											<Select.Option key={client.id} value={client.id}>
												{client.name}
											</Select.Option>
										))}
									</Select>
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="capacity"
									label="Capacity"
									rules={[
										{ required: true, message: 'Please enter capacity' },
										{ type: 'number', min: 1, message: 'Capacity must be at least 1' },
									]}
									validateTrigger="onSubmit"
								>
									<InputNumber
										placeholder="Enter capacity"
										min={1}
										max={999999}
										style={{ width: '100%' }}
									/>
								</Form.Item>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="ground_type"
									label="Ground Type"
								>
									<Input placeholder="e.g., Football, Cricket, Basketball" maxLength={255} />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="ground_category"
									label="Ground Category"
								>
									<Input placeholder="e.g., Indoor, Outdoor" maxLength={255} />
								</Form.Item>
							</Col>
						</Row>

						<Form.Item
							name="description"
							label="Description"
						>
							<TextArea 
								rows={4} 
								placeholder="Enter description" 
								maxLength={5000}
								showCount
							/>
						</Form.Item>

						<Form.Item
							name="rules"
							label="Rules"
						>
							<TextArea 
								rows={4} 
								placeholder="Enter rules" 
								maxLength={5000}
								showCount
							/>
						</Form.Item>
					</Card>

					{/* Contact Information */}
					<Card title="Contact Information" style={{ marginBottom: 24 }}>
						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="phone"
									label="Phone"
									rules={[
										{ required: true, message: 'Please enter phone number' },
										{ max: 20, message: 'Phone number must be less than 20 characters' },
									]}
									validateTrigger="onSubmit"
								>
									<Input placeholder="Enter phone number" maxLength={20} />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="email"
									label="Email"
									rules={[
										{ required: true, message: 'Please enter email' },
										{ type: 'email', message: 'Please enter a valid email' },
										{ max: 255, message: 'Email must be less than 255 characters' },
									]}
									validateTrigger="onSubmit"
								>
									<Input placeholder="Enter email address" maxLength={255} />
								</Form.Item>
							</Col>
						</Row>

						<Row gutter={16}>
							<Col xs={24} sm={12}>
								<Form.Item
									name="opening_time"
									label="Opening Time"
								>
									<TimePicker format="HH:mm" style={{ width: '100%' }} placeholder="Select opening time" />
								</Form.Item>
							</Col>
							<Col xs={24} sm={12}>
								<Form.Item
									name="closing_time"
									label="Closing Time"
								>
									<TimePicker format="HH:mm" style={{ width: '100%' }} placeholder="Select closing time" />
								</Form.Item>
							</Col>
						</Row>
					</Card>

					{/* Status & Flags */}
					<Card title="Status & Flags" style={{ marginBottom: 24 }}>
						<Row gutter={16}>
							<Col xs={24} sm={8}>
								<Form.Item
									name="status"
									label="Status"
									rules={[{ required: true, message: 'Please select status' }]}
									validateTrigger="onSubmit"
								>
									<Select placeholder="Select status">
										<Select.Option value="active">Active</Select.Option>
										<Select.Option value="inactive">Inactive</Select.Option>
									</Select>
								</Form.Item>
							</Col>
							<Col xs={24} sm={8}>
								<Form.Item
									name="is_new"
									label="Mark as New"
									valuePropName="checked"
								>
									<Switch />
								</Form.Item>
							</Col>
							<Col xs={24} sm={8}>
								<Form.Item
									name="is_featured"
									label="Mark as Featured"
									valuePropName="checked"
								>
									<Switch />
								</Form.Item>
							</Col>
						</Row>
					</Card>

					{/* Images */}
					<Card title="Images" style={{ marginBottom: 24 }}>
						{/* Existing Images */}
						{existingImages.length > 0 && (
							<div style={{ marginBottom: 24 }}>
								<Title level={5}>Existing Images</Title>
								<Row gutter={[16, 16]}>
									{existingImages.map((img) => (
										<Col key={img.id} xs={12} sm={8} md={6}>
											<div style={{ position: 'relative' }}>
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
												<Button
													type="primary"
													danger
													size="small"
													icon={<DeleteOutlined />}
													onClick={() => handleRemoveExistingImage(img.id)}
													style={{
														position: 'absolute',
														top: 8,
														right: 8,
													}}
												>
													Remove
												</Button>
											</div>
										</Col>
									))}
								</Row>
							</div>
						)}

						{/* New Images */}
						<div>
							<Title level={5}>Add New Images</Title>
							<Upload
								listType="picture-card"
								fileList={imageList}
								onChange={handleImageChange}
								beforeUpload={() => false}
								multiple
								accept="image/*"
							>
								{(imageList.length + existingImages.length) >= 10 ? null : (
									<div>
										<UploadOutlined />
										<div style={{ marginTop: 8 }}>Upload</div>
									</div>
								)}
							</Upload>
						</div>
					</Card>

					{/* Features */}
					<Card 
						title="Features" 
						extra={
							<Button
								type="dashed"
								onClick={() => {
									const features = form.getFieldValue('features') || [];
									form.setFieldsValue({
										features: [...features, { feature_name: '', feature_type: 'facility', feature_status: 'active' }],
									});
								}}
								icon={<PlusOutlined />}
							>
								Add Feature
							</Button>
						}
						style={{ marginBottom: 24 }}
					>
						<Form.List name="features">
							{(fields, { add, remove }) => (
								<>
									{fields.map(({ key, name, ...restField }) => (
										<Row key={key} gutter={16} style={{ marginBottom: 16 }}>
											<Col xs={24} sm={8}>
												<Form.Item
													{...restField}
													name={[name, 'feature_name']}
													label="Feature Name"
													rules={[{ required: true, message: 'Please enter feature name' }]}
												>
													<Input placeholder="Feature name" maxLength={255} />
												</Form.Item>
											</Col>
											<Col xs={24} sm={6}>
												<Form.Item
													{...restField}
													name={[name, 'feature_type']}
													label="Feature Type"
												>
													<Select placeholder="Feature type">
														<Select.Option value="facility">Facility</Select.Option>
														<Select.Option value="equipment">Equipment</Select.Option>
														<Select.Option value="service">Service</Select.Option>
													</Select>
												</Form.Item>
											</Col>
											<Col xs={24} sm={6}>
												<Form.Item
													{...restField}
													name={[name, 'feature_status']}
													label="Status"
												>
													<Select placeholder="Status">
														<Select.Option value="active">Active</Select.Option>
														<Select.Option value="inactive">Inactive</Select.Option>
													</Select>
												</Form.Item>
											</Col>
											<Col xs={24} sm={4}>
												<Form.Item label=" " style={{ marginTop: fields.length === 1 ? 0 : 32 }}>
													<Button
														type="text"
														danger
														icon={<DeleteOutlined />}
														onClick={() => remove(name)}
														disabled={fields.length === 1}
														block
													>
														Remove
													</Button>
												</Form.Item>
											</Col>
										</Row>
									))}
								</>
							)}
						</Form.List>
					</Card>

					{/* Slots */}
					<Card 
						title="Time Slots" 
						extra={
							<Button
								type="dashed"
								onClick={() => {
									const slots = form.getFieldValue('slots') || [];
									form.setFieldsValue({
										slots: [...slots, { slot_name: '', slot_type: 'morning', slot_status: 'active', price_per_slot: 0 }],
									});
								}}
								icon={<PlusOutlined />}
							>
								Add Slot
							</Button>
						}
						style={{ marginBottom: 24 }}
					>
						<Form.List name="slots">
							{(fields, { add, remove }) => (
								<>
									{fields.map(({ key, name, ...restField }) => (
										<Card
											key={key}
											type="inner"
											style={{ marginBottom: 16 }}
											extra={
												<Button
													type="text"
													danger
													icon={<DeleteOutlined />}
													onClick={() => remove(name)}
													disabled={fields.length === 1}
												>
													Remove
												</Button>
											}
										>
											<Row gutter={16}>
												<Col xs={24} sm={6}>
													<Form.Item
														{...restField}
														name={[name, 'slot_name']}
														label="Slot Name"
													>
														<Input placeholder="Slot name" maxLength={255} />
													</Form.Item>
												</Col>
												<Col xs={24} sm={6}>
													<Form.Item
														{...restField}
														name={[name, 'start_time']}
														label="Start Time"
													>
														<TimePicker format="HH:mm" style={{ width: '100%' }} placeholder="Start time" />
													</Form.Item>
												</Col>
												<Col xs={24} sm={6}>
													<Form.Item
														{...restField}
														name={[name, 'end_time']}
														label="End Time"
													>
														<TimePicker format="HH:mm" style={{ width: '100%' }} placeholder="End time" />
													</Form.Item>
												</Col>
												<Col xs={24} sm={6}>
													<Form.Item
														{...restField}
														name={[name, 'slot_type']}
														label="Slot Type"
													>
														<Select placeholder="Slot type">
															<Select.Option value="morning">Morning</Select.Option>
															<Select.Option value="afternoon">Afternoon</Select.Option>
															<Select.Option value="evening">Evening</Select.Option>
															<Select.Option value="night">Night</Select.Option>
														</Select>
													</Form.Item>
												</Col>
												<Col xs={24} sm={6}>
													<Form.Item
														{...restField}
														name={[name, 'day_of_week']}
														label="Day of Week"
													>
														<Select placeholder="Select day" allowClear>
															<Select.Option value="monday">Monday</Select.Option>
															<Select.Option value="tuesday">Tuesday</Select.Option>
															<Select.Option value="wednesday">Wednesday</Select.Option>
															<Select.Option value="thursday">Thursday</Select.Option>
															<Select.Option value="friday">Friday</Select.Option>
															<Select.Option value="saturday">Saturday</Select.Option>
															<Select.Option value="sunday">Sunday</Select.Option>
														</Select>
													</Form.Item>
												</Col>
												<Col xs={24} sm={6}>
													<Form.Item
														{...restField}
														name={[name, 'slot_status']}
														label="Slot Status"
													>
														<Select placeholder="Status">
															<Select.Option value="active">Active</Select.Option>
															<Select.Option value="inactive">Inactive</Select.Option>
														</Select>
													</Form.Item>
												</Col>
												<Col xs={24} sm={6}>
													<Form.Item
														{...restField}
														name={[name, 'price_per_slot']}
														label="Price per Slot"
													>
														<InputNumber
															placeholder="Price"
															min={0}
															step={0.01}
															precision={2}
															style={{ width: '100%' }}
															formatter={(value) => `₹ ${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
															parser={(value) => value.replace(/₹\s?|(,*)/g, '')}
														/>
													</Form.Item>
												</Col>
											</Row>
										</Card>
									))}
								</>
							)}
						</Form.List>
					</Card>

					<Form.Item>
						<Space>
							<Button onClick={() => navigate('/admin/grounds')}>
								Cancel
							</Button>
							<Button type="primary" htmlType="submit" loading={submitting} size="large">
								Update Ground
							</Button>
						</Space>
					</Form.Item>
				</Form>
			</Space>
		</AntDesignAdminLayout>
	);
}
