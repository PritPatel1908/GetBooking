@extends('layouts.user')

@section('styles')
<style>
    :root {
        --primary-color: #3490dc;
        --primary-dark: #2779bd;
        --secondary-color: #38c172;
        --accent-color: #f6993f;
        --bg-color: #f8fafc;
        --text-color: #2d3748;
        --card-bg: #ffffff;
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --card-hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --header-bg: #ffffff;
        --footer-bg: #2d3748;
        --footer-text: #f7fafc;
        --border-color: #e2e8f0;
        --input-bg: #edf2f7;
        --input-text: #4a5568;
        --primary-gradient: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        --bg-primary: var(--bg-color);
        --bg-card: var(--card-bg);
        --text-primary: var(--text-color);
        --text-secondary: #718096;
        --hover-shadow: var(--card-hover-shadow);
    }

    .dark {
        --primary-color: #4299e1;
        --primary-dark: #3182ce;
        --secondary-color: #48bb78;
        --accent-color: #f6ad55;
        --bg-color: #1a202c;
        --text-color: #f7fafc;
        --card-bg: #2d3748;
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        --card-hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        --header-bg: #2d3748;
        --footer-bg: #1a202c;
        --footer-text: #f7fafc;
        --border-color: #4a5568;
        --input-bg: #4a5568;
        --input-text: #e2e8f0;
        --primary-gradient: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        --bg-primary: var(--bg-color);
        --bg-card: var(--card-bg);
        --text-primary: var(--text-color);
        --text-secondary: #a0aec0;
        --hover-shadow: var(--card-hover-shadow);
    }

    .profile-container {
        padding: 3rem 0;
        background: var(--bg-primary);
        min-height: 100vh;
        transition: background-color 0.3s ease;
    }

    .profile-header {
        margin-bottom: 3rem;
    }

    .profile-stats {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .profile-stats .stat-card {
        flex: 1;
        min-width: 250px;
    }

    .profile-stats .stat-card.full-width {
        width: 100%;
        max-width: 100%;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    .stat-card {
        background: var(--primary-gradient);
        padding: 2rem;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        flex: 1;
        text-align: center;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        display: block;
        margin-bottom: 1rem;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        z-index: 1;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
        color: white;
    }

    .stat-card:active {
        transform: translateY(0);
    }

    .stat-card i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, 0.9);
        position: relative;
        z-index: 2;
    }

    .stat-card h3 {
        margin: 0.5rem 0;
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        position: relative;
        z-index: 2;
    }

    .stat-card p {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
        color: white;
        position: relative;
        z-index: 2;
    }

    .profile-content {
        display: grid;
        grid-template-columns: minmax(300px, 350px) 1fr;
        gap: 2.5rem;
    }

    .profile-sidebar {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        height: fit-content;
        position: sticky;
        top: 2rem;
        transition: all 0.3s ease;
    }

    .profile-photo {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-photo img {
        width: 250px;
        height: 250px;
        border-radius: 20px;
        object-fit: cover;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        border: 4px solid var(--bg-card);
        transition: all 0.3s ease;
    }

    .profile-details {
        background: var(--bg-card);
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
    }

    .profile-details h2 {
        color: var(--text-primary);
        font-size: 1.8rem;
        margin-bottom: 2rem;
        font-weight: 600;
        transition: color 0.3s ease;
        position: relative;
        padding-bottom: 0.75rem;
    }

    .profile-details h2::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 50px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .password-section {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .form-group {
        margin-bottom: 1.8rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.7rem;
        color: var(--text-secondary);
        font-weight: 500;
        transition: color 0.3s ease;
    }

    input {
        width: 100%;
        padding: 0.9rem 1.2rem;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: var(--input-bg);
        color: var(--text-primary);
    }

    input:focus {
        outline: none;
        border-color: var(--primary-color);
        background: var(--bg-card);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .error {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    .btn {
        padding: 0.9rem 2rem;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: var(--hover-shadow);
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    [data-theme="dark"] .alert-success {
        background-color: #064e3b;
        color: #a7f3d0;
        border: 1px solid #059669;
    }

    .photo-upload .btn {
        width: 100%;
        margin-top: 1rem;
    }

    @media (max-width: 1024px) {
        .profile-content {
            grid-template-columns: 1fr;
        }

        .profile-sidebar {
            position: static;
            margin-bottom: 2rem;
        }
    }

    @media (max-width: 768px) {
        .profile-stats {
            flex-direction: column;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .profile-photo img {
            width: 200px;
            height: 200px;
        }
    }

    /* Toast Notification Styles */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .toast {
        background: var(--bg-card);
        color: var(--text-primary);
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        transform: translateX(120%);
        transition: transform 0.3s ease-in-out;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.success {
        border-left: 4px solid var(--secondary-color);
    }

    .toast.error {
        border-left: 4px solid #ef4444;
    }

    .toast-message {
        flex-grow: 1;
        margin-right: 1rem;
    }

    .toast-close {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 1.2rem;
    }
</style>
@endsection

@section('content')
<div class="profile-container">
    <div class="container">
        <div class="profile-header">
            <div class="profile-stats">
                <a href="{{ route('user.my_bookings') }}" class="stat-card" style="text-decoration: none;">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Total Bookings</h3>
                    <p>{{ $bookings }}</p>
                </a>
                <a href="{{ route('user.pending-payments') }}" class="stat-card" style="text-decoration: none;">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Total Payments</h3>
                    <p>{{ $totalPayments }}</p>
                </a>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-sidebar">
                <div class="profile-photo">
                    <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('assets/user/images/default-avatar.png') }}"
                         alt="Profile Photo" id="profile-preview">
                    <div class="photo-upload">
                        @csrf
                        <input type="file" name="profile_photo" id="profile-photo-input" accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('profile-photo-input').click()">
                            <i class="fas fa-camera"></i> Change Photo
                        </button>
                    </div>
                </div>
            </div>

            <div class="profile-details">
                <h2>Profile Information</h2>
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="toast-container" id="toastContainer"></div>

                <!-- Profile Information Form -->
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Username</label>
                        <input type="text" id="name" value="{{ old('name', $user->name) }}" readonly>
                        <small class="text-gray-500">Username is automatically generated from your first and last name</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="{{ old('email', $user->email) }}" readonly>
                        <small class="text-gray-500">Email cannot be changed</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                            @error('middle_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}">
                        @error('address')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}">
                            @error('city')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" value="{{ old('state', $user->state) }}">
                            @error('state')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="{{ old('country', $user->country) }}">
                            @error('country')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
                            @error('postal_code')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>

                <!-- Password Change Form -->
                <div class="password-section">
                    <h2>Change Password</h2>
                    <form action="{{ route('user.password.update') }}" method="POST" id="password-form">
                        @csrf
                        <input type="email" name="email" id="fake-email" value="{{ old('email', $user->email) }}" autocomplete="username" style="display:none;">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                            @error('current_password')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
                            @error('new_password')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Update username in real-time when first name or last name changes
    function updateUsername() {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const username = (firstName + ' ' + lastName).trim();
        document.getElementById('name').value = username;
    }

    document.getElementById('first_name').addEventListener('input', updateUsername);
    document.getElementById('last_name').addEventListener('input', updateUsername);

    // Profile photo upload
    document.getElementById('profile-photo-input').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];

            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                showToast('File size should not exceed 2MB', 'error');
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                showToast('Please select a valid image file (JPEG, PNG, JPG, or GIF)', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('profile_photo', file);
            formData.append('_token', '{{ csrf_token() }}');

            // Show loading state
            const button = this.nextElementSibling;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            button.disabled = true;

            fetch('{{ route('user.profile.update') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Upload failed');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the preview image
                    const preview = document.getElementById('profile-preview');
                    preview.src = URL.createObjectURL(file);

                    // Update the user's name in the header if it exists
                    const userNameElement = document.querySelector('.user-name');
                    if (userNameElement && data.user.name) {
                        userNameElement.textContent = data.user.name;
                    }

                    showToast('Profile photo updated successfully!', 'success');
                } else {
                    showToast(data.message || 'Failed to update profile photo', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message || 'An error occurred while updating profile photo', 'error');
            })
            .finally(() => {
                // Reset button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    });

    // Profile form submission
    document.querySelector('.profile-details form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route('user.profile.update') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Profile updated successfully!', 'success');
                // Clear password fields
                document.getElementById('current_password').value = '';
                document.getElementById('new_password').value = '';
                document.getElementById('new_password_confirmation').value = '';

                // Update user name in the header if it exists
                const userNameElement = document.querySelector('.user-name');
                if (userNameElement && data.user.name) {
                    userNameElement.textContent = data.user.name;
                }
            } else {
                showToast(data.message || 'Failed to update profile', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating profile', 'error');
        });
    });

    // Toast notification function
    function showToast(message, type = 'success') {
        console.log('Showing toast:', message, type); // Debug log

        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            console.error('Toast container not found!');
            return;
        }

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        toast.innerHTML = `
            <div class="toast-message">${message}</div>
            <button class="toast-close">&times;</button>
        `;

        toastContainer.appendChild(toast);

        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);

        // Close button functionality
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    }

    // Debug: Log when the script loads
    console.log('Profile page JavaScript loaded');
</script>
@endsection

@endsection
