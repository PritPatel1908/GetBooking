<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login / Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="{{ asset('assets/auth/css/style.css') }}">
    <script src="{{ asset('assets/auth/js/before_call.js')}}"></script>
</head>

<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="card-3d-wrapper" id="cardWrapper">
                <!-- Login Side (Front) -->
                <div class="card-front">
                    <div class="card-left">
                        <div class="floating-shape shape1"></div>
                        <div class="floating-shape shape2"></div>
                        <div class="floating-shape shape3"></div>

                        <div class="brand-logo">
                            <img src="{{ asset('Images/GetBooking.jpeg') }}" alt="GetBooking">
                        </div>

                        <div class="welcome-text">
                            <h3>Welcome Back!</h3>
                            <p>Sign in to continue access</p>
                        </div>
                    </div>

                    <div class="card-right">
                        <h2 class="form-title">Sign In</h2>

                        <form method="POST" action="{{ route('login.attempt') }}" id="loginForm">
                            @csrf
                            <div class="form-group">
                                <i class="input-icon fas fa-envelope"></i>
                                <input type="email" class="form-style @error('email') is-invalid @enderror"
                                    id="login-email" name="email" placeholder="Your Email" value="{{ old('email') }}"
                                    required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <i class="input-icon fas fa-lock"></i>
                                <input type="password" class="form-style @error('password') is-invalid @enderror"
                                    id="login-password" name="password" placeholder="Your Password" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" {{
                                    old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>

                            <button type="submit" class="submit-btn" id="loginBtn">
                                Sign In <i class="fas fa-arrow-right ms-2"></i>
                            </button>

                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                <i class="fas fa-unlock-alt me-1"></i> Forgot password?
                            </a>
                            @endif
                        </form>

                        <div class="separator">
                            <span>or sign in with</span>
                        </div>

                        {{-- <div class="social-login">
                            <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-btn google"><i class="fab fa-google"></i></a>
                            <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                        </div> --}}

                        <div class="toggle-form">
                            <p>Don't have an account? <button type="button" class="toggle-form-btn"
                                    id="showSignup">Create account</button></p>
                        </div>
                    </div>
                </div>

                <!-- Register Side (Back) -->
                <div class="card-back">
                    <div class="card-left">
                        <div class="floating-shape shape1"></div>
                        <div class="floating-shape shape2"></div>
                        <div class="floating-shape shape3"></div>

                        <div class="brand-logo">
                            <img src="{{ asset('Images/GetBooking.jpeg') }}" alt="GetBooking">
                        </div>

                        <div class="welcome-text">
                            <h3>Join Us Today!</h3>
                            <p>Create your account</p>
                        </div>
                    </div>

                    <div class="card-right">
                        <h2 class="form-title">Sign Up</h2>

                        <form method="POST" action="{{ route('register') }}" id="registerForm">
                            @csrf
                            <div class="form-group">
                                <i class="input-icon fas fa-user"></i>
                                <input type="text" class="form-style @error('name') is-invalid @enderror" id="name"
                                    name="name" placeholder="Your Full Name" value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <i class="input-icon fas fa-envelope"></i>
                                <input type="email" class="form-style @error('email') is-invalid @enderror"
                                    id="register-email" name="email" placeholder="Your Email" value="{{ old('email') }}"
                                    required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <i class="input-icon fas fa-lock"></i>
                                <input type="password" class="form-style @error('password') is-invalid @enderror"
                                    id="register-password" name="password" placeholder="Create Password" required>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <i class="input-icon fas fa-check-circle"></i>
                                <input type="password" class="form-style" id="password-confirm"
                                    name="password_confirmation" placeholder="Confirm Password" required>
                            </div>

                            <button type="submit" class="submit-btn" id="registerBtn">
                                Create Account <i class="fas fa-user-plus ms-2"></i>
                            </button>
                        </form>

                        <div class="separator">
                            <span>or sign up with</span>
                        </div>

                        {{-- <div class="social-login">
                            <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-btn google"><i class="fab fa-google"></i></a>
                            <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                        </div> --}}

                        <div class="toggle-form">
                            <p>Already have an account? <button type="button" class="toggle-form-btn"
                                    id="showLogin">Sign in</button></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/auth/js/main.js')}}"></script>
    <script src="{{ asset('assets/auth/js/ajax.js')}}"></script>
</body>

</html>
