@extends('layouts.user')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --primary-color: #6366f1;
        --primary-dark: #4f46e5;
        --bg-primary: #f8f9fa;
        --bg-card: #ffffff;
        --text-primary: #1f2937;
        --text-secondary: #4b5563;
        --border-color: #e5e7eb;
        --input-bg: #f9fafb;
        --shadow-color: rgba(0, 0, 0, 0.05);
        --card-shadow: 0 10px 30px var(--shadow-color);
        --hover-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
    }

    [data-theme="dark"] {
        --primary-gradient: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
        --primary-color: #818cf8;
        --primary-dark: #6366f1;
        --bg-primary: #111827;
        --bg-card: #1f2937;
        --text-primary: #f9fafb;
        --text-secondary: #d1d5db;
        --border-color: #374151;
        --input-bg: #374151;
        --shadow-color: rgba(0, 0, 0, 0.2);
        --card-shadow: 0 10px 30px var(--shadow-color);
        --hover-shadow: 0 6px 15px rgba(99, 102, 241, 0.4);
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
        grid-template-columns: 350px 1fr;
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
</style>
@endsection

@section('content')
<div class="profile-container">
    <div class="container">
        <div class="profile-header">
            <div class="profile-stats">
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Total Bookings</h3>
                    <p>{{ $bookings }}</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Pending Payments</h3>
                    <p>{{ $pendingPayments }}</p>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-sidebar">
                <div class="profile-photo">
                    <img src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('assets/user/images/default-avatar.png') }}"
                         alt="Profile Photo" id="profile-preview">
                    <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="photo-upload">
                        @csrf
                        <input type="file" name="profile_photo" id="profile-photo-input" accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('profile-photo-input').click()">
                            <i class="fas fa-camera"></i> Change Photo
                        </button>
                    </form>
                </div>
            </div>

            <div class="profile-details">
                <h2>Profile Information</h2>
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
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

                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                        @error('current_password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                        @error('new_password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.getElementById('profile-photo-input').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
        this.form.submit();
    }
});
</script>
@endsection

@endsection
