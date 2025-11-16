<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login-signup');
    }

    /**
     * Attempt to log in the user
     */
    public function login(Request $request)
    {
        // ====== START: COMPREHENSIVE LOGGING ======
        Log::info('========== LOGIN REQUEST STARTED ==========');
        Log::info('Request Method: ' . $request->method());
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Request Headers:', [
            'Content-Type' => $request->header('Content-Type'),
            'Accept' => $request->header('Accept'),
            'X-CSRF-TOKEN' => $request->header('X-CSRF-TOKEN') ? 'PRESENT' : 'MISSING',
            'X-Requested-With' => $request->header('X-Requested-With'),
            'User-Agent' => substr($request->header('User-Agent'), 0, 50),
        ]);

        // Log ALL request data
        $allInput = $request->all();
        Log::info('Request All Input:', $allInput);
        Log::info('Request Email: ' . ($request->input('email') ?? 'NOT PROVIDED'));
        Log::info('Request Password: ' . ($request->has('password') ? 'PROVIDED (length: ' . strlen($request->input('password')) . ')' : 'NOT PROVIDED'));
        Log::info('Request Remember: ' . ($request->has('remember') ? ($request->input('remember') ? 'TRUE' : 'FALSE') : 'NOT PROVIDED'));
        Log::info('Request JSON: ' . ($request->isJson() ? 'YES' : 'NO'));
        Log::info('Request Wants JSON: ' . ($request->wantsJson() ? 'YES' : 'NO'));
        Log::info('Request AJAX: ' . ($request->ajax() ? 'YES' : 'NO'));
        Log::info('Current Auth Status: ' . (Auth::check() ? 'AUTHENTICATED (User ID: ' . Auth::id() . ')' : 'NOT AUTHENTICATED'));
        // ====== END: REQUEST LOGGING ======

        // CRITICAL: For POST requests, ALWAYS return JSON - no exceptions
        // Check method FIRST before any other processing
        if (!$request->isMethod('post')) {
            Log::warning('Login failed - Invalid method', ['method' => $request->method()]);
            return response()->json([
                'success' => false,
                'message' => 'Only POST method is allowed for login API',
            ], 405)->header('Content-Type', 'application/json');
        }

        // REMOVED: Early return for already authenticated users
        // This was causing password bypass - we need to verify credentials every time
        // Even if user appears authenticated, verify password before allowing login
        //
        // If user is already authenticated, they can just navigate directly without logging in again
        // But if they're trying to login, we must verify their password for security

        // Force JSON response - set Accept header
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');


        try {
            Log::info('----- STEP 1: Starting Validation -----');

            // Validate request
            $validationRules = [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ];

            Log::info('Validation Rules:', $validationRules);
            Log::info('Email to validate: ' . ($request->input('email') ?? 'NULL'));
            Log::info('Password to validate: ' . ($request->has('password') ? 'PRESENT (length: ' . strlen($request->input('password')) . ')' : 'NULL'));

            $validated = $request->validate($validationRules);

            Log::info('Validation PASSED');
            Log::info('Validated Data:', $validated);

            // ====== STEP 2: Find User ======
            Log::info('----- STEP 2: Finding User -----');
            Log::info('Searching for user with email: ' . $request->email);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning('Login failed - user not found', [
                    'email' => $request->email,
                    'query_executed' => 'User::where("email", "' . $request->email . '")->first()',
                    'result' => 'NULL',
                ]);
                Log::info('========== LOGIN REQUEST FAILED - USER NOT FOUND ==========');
                $response = response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401)->header('Content-Type', 'application/json');
                Log::info('Response: ', ['status' => 401, 'message' => 'Invalid email or password.']);
                return $response;
            }

            Log::info('User FOUND:', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'user_type' => $user->user_type,
                'has_password' => !empty($user->password),
                'password_length' => strlen($user->password ?? ''),
            ]);

            // ====== STEP 3: Password Validation ======
            Log::info('----- STEP 3: Password Validation -----');

            $passwordProvided = !empty($request->password) && strlen(trim($request->password)) > 0;
            $passwordInDb = !empty($user->password) && strlen(trim($user->password)) > 0;

            Log::info('Password Check Details:', [
                'email' => $request->email,
                'user_id' => $user->id,
                'password_provided' => $passwordProvided,
                'password_provided_length' => strlen($request->password ?? ''),
                'password_in_db' => $passwordInDb,
                'db_password_length' => strlen($user->password ?? ''),
                'password_value_check' => $request->has('password') ? 'HAS KEY' : 'NO KEY',
                'password_empty_check' => empty($request->password) ? 'EMPTY' : 'NOT EMPTY',
            ]);

            // CRITICAL: If password is missing in DB, immediately reject
            // This prevents login with empty/null passwords
            if (!$passwordInDb) {
                Log::error('Login failed - password missing in database', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'user_password_field' => $user->password ?? 'NULL',
                    'user_password_empty' => empty($user->password),
                    'user_password_length' => strlen($user->password ?? ''),
                ]);
                Log::info('========== LOGIN REQUEST FAILED - NO PASSWORD IN DB ==========');
                $response = response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401)->header('Content-Type', 'application/json');
                Log::info('Response: ', ['status' => 401, 'message' => 'Invalid email or password.']);
                return $response;
            }

            // CRITICAL: If password is not provided, immediately reject
            if (!$passwordProvided) {
                Log::error('Login failed - password not provided', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'request_has_password' => $request->has('password'),
                    'request_password_empty' => empty($request->password),
                    'request_password_value' => $request->input('password') ?? 'NULL',
                ]);
                Log::info('========== LOGIN REQUEST FAILED - NO PASSWORD PROVIDED ==========');
                $response = response()->json([
                    'success' => false,
                    'message' => 'Password is required.',
                ], 401)->header('Content-Type', 'application/json');
                Log::info('Response: ', ['status' => 401, 'message' => 'Password is required.']);
                return $response;
            }

            Log::info('Password validation PASSED - both provided and in DB exist');

            // ====== STEP 4: Password Hash Verification ======
            Log::info('----- STEP 4: Password Hash Verification -----');

            // CRITICAL: Get password hash DIRECTLY from database to bypass model casts
            // The 'hashed' cast in User model interferes with password verification
            // We MUST get the raw hash from database, not from the model
            $storedPasswordHash = DB::table('users')->where('id', $user->id)->value('password');

            Log::info('Password Hash Extraction (Direct DB Query):', [
                'user_id' => $user->id,
                'hash_from_db' => $storedPasswordHash ? 'FOUND' : 'NOT FOUND',
                'hash_length' => $storedPasswordHash ? strlen($storedPasswordHash) : 0,
                'hash_prefix' => $storedPasswordHash ? substr($storedPasswordHash, 0, 20) . '...' : 'N/A',
            ]);

            // CRITICAL: If password hash is empty or null, reject immediately
            if (empty($storedPasswordHash)) {
                Log::error('Login failed - password hash is empty in database', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'db_password_value' => 'EMPTY or NULL',
                ]);
                Log::info('========== LOGIN REQUEST FAILED - NO PASSWORD HASH IN DB ==========');
                $response = response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401)->header('Content-Type', 'application/json');
                Log::info('Response: ', ['status' => 401, 'message' => 'Invalid email or password.']);
                return $response;
            }

            // CRITICAL: Now verify the password using Hash::check with the raw hash from DB
            $providedPassword = trim($request->password);

            Log::info('Before Hash::check() Call:', [
                'provided_password_length' => strlen($providedPassword),
                'provided_password_preview' => substr($providedPassword, 0, 3) . '...',
                'stored_hash_length' => strlen($storedPasswordHash),
                'stored_hash_prefix' => substr($storedPasswordHash, 0, 30) . '...',
                'stored_hash_starts_with' => substr($storedPasswordHash, 0, 7), // Should be $2y$10 for bcrypt
            ]);

            $passwordValid = false;

            try {
                // Explicitly verify using Hash::check - this is the ONLY way to verify
                // We use the raw hash directly from database (bypassing model casts)
                Log::info('Calling Hash::check(providedPassword, storedHash)');

                $passwordValid = Hash::check($providedPassword, $storedPasswordHash);

                Log::info('Hash::check() Result:', [
                    'result' => $passwordValid ? 'TRUE (MATCH)' : 'FALSE (NO MATCH)',
                    'password_matched' => $passwordValid,
                    'provided_password_length' => strlen($providedPassword),
                    'stored_hash_length' => strlen($storedPasswordHash),
                    'stored_hash_type' => substr($storedPasswordHash, 0, 7), // Should be $2y$10 for bcrypt
                ]);

                // EXTRA SAFETY: If Hash::check returned true, verify the hash format
                if ($passwordValid === true) {
                    Log::info('Password validation returned TRUE - verifying hash format');
                    // Check if hash starts with bcrypt identifier
                    if (!str_starts_with($storedPasswordHash, '$2y$') && !str_starts_with($storedPasswordHash, '$2a$')) {
                        Log::error('WARNING: Password hash does not look like a valid bcrypt hash!', [
                            'hash_prefix' => substr($storedPasswordHash, 0, 10),
                            'expected_prefix' => '$2y$10 or $2a$10',
                            'this_may_allow_wrong_passwords' => true,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Password hash check exception', [
                    'email' => $request->email,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $passwordValid = false;
            }

            Log::info('Password verification result:', [
                'email' => $request->email,
                'user_id' => $user->id,
                'password_valid' => $passwordValid,
                'password_valid_type' => gettype($passwordValid),
            ]);

            // CRITICAL: If password is NOT valid, immediately reject
            if ($passwordValid === false) {
                Log::error('Login failed - invalid password (Hash::check returned FALSE)', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'password_provided_preview' => substr($request->password ?? '', 0, 3) . '...',
                    'hash_used' => substr($storedPasswordHash ?? '', 0, 20) . '...',
                ]);
                Log::info('========== LOGIN REQUEST FAILED - INVALID PASSWORD ==========');
                $response = response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401)->header('Content-Type', 'application/json');
                Log::info('Response: ', ['status' => 401, 'message' => 'Invalid email or password.']);
                return $response;
            }

            // Extra safety check - verify password is actually valid
            if ($passwordValid !== true) {
                Log::error('Login failed - password validation returned unexpected value', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'password_valid_type' => gettype($passwordValid),
                    'password_valid_value' => var_export($passwordValid, true),
                ]);
                Log::info('========== LOGIN REQUEST FAILED - UNEXPECTED VALIDATION RESULT ==========');
                $response = response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401)->header('Content-Type', 'application/json');
                Log::info('Response: ', ['status' => 401, 'message' => 'Invalid email or password.']);
                return $response;
            }

            Log::info('Password hash verification PASSED');

            // Check remember me parameter
            $remember = $request->has('remember') && (
                $request->remember === '1' ||
                $request->remember === true ||
                $request->remember === 'true'
            );

            // ====== STEP 5: Authentication ======
            Log::info('----- STEP 5: Authenticating User -----');

            // Since password is already verified, log the user in directly
            // This ensures authentication happens correctly
            Log::info('Calling Auth::login()', [
                'user_id' => $user->id,
                'remember' => $remember,
            ]);

            Auth::login($user, $remember);

            Log::info('Auth::login() completed', [
                'Auth::check()' => Auth::check() ? 'TRUE' : 'FALSE',
                'Auth::id()' => Auth::id(),
            ]);

            // Regenerate session for security
            $oldSessionId = $request->session()->getId();
            $request->session()->regenerate();
            $newSessionId = $request->session()->getId();

            Log::info('Session regenerated', [
                'old_session_id' => $oldSessionId,
                'new_session_id' => $newSessionId,
            ]);

            // Verify authentication is actually working
            $authenticatedUser = Auth::user();

            Log::info('Auth::user() result:', [
                'user_returned' => $authenticatedUser ? 'YES' : 'NO',
                'user_id' => $authenticatedUser?->id,
                'expected_user_id' => $user->id,
                'ids_match' => ($authenticatedUser?->id === $user->id) ? 'YES' : 'NO',
            ]);

            if (!$authenticatedUser || $authenticatedUser->id !== $user->id) {
                Log::error('Login failed - authentication not persisted', [
                    'email' => $request->email,
                    'user_id' => $user->id,
                    'authenticated_user_id' => $authenticatedUser?->id,
                    'Auth::check()' => Auth::check() ? 'TRUE' : 'FALSE',
                ]);
                Auth::logout();
                Log::info('========== LOGIN REQUEST FAILED - AUTH NOT PERSISTED ==========');
                $response = response()->json([
                    'success' => false,
                    'message' => 'Authentication failed. Please try again.',
                ], 500)->header('Content-Type', 'application/json');
                Log::info('Response: ', ['status' => 500, 'message' => 'Authentication failed.']);
                return $response;
            }

            // Set longer session lifetime
            config(['session.lifetime' => 10080]); // 7 days

            // Prepare user data
            $userData = [
                'id' => $authenticatedUser->id,
                'name' => $authenticatedUser->name,
                'email' => $authenticatedUser->email,
                'user_type' => $authenticatedUser->user_type,
                'profile_photo_path' => $authenticatedUser->profile_photo_path ? asset('storage/' . $authenticatedUser->profile_photo_path) : null,
            ];

            // Determine redirect route based on user type
            $redirectRoute = route('user.home');
            if ($authenticatedUser->user_type == 'admin') {
                $redirectRoute = route('admin.dashboard');
            } else if ($authenticatedUser->user_type == 'client') {
                $redirectRoute = route('client.dashboard');
            }

            Log::info('Login completed successfully', [
                'user_id' => $authenticatedUser->id,
                'email' => $authenticatedUser->email,
                'redirect' => $redirectRoute,
                'session_id' => $request->session()->getId(),
                'authenticated' => Auth::check(),
            ]);

            // ====== STEP 6: Preparing Response ======
            Log::info('----- STEP 6: Preparing Response -----');

            $responseData = [
                'success' => true,
                'message' => 'Login successful.',
                'user' => $userData,
                'redirect' => $redirectRoute,
            ];

            Log::info('Response Data:', $responseData);

            // ALWAYS return JSON for POST requests
            $response = response()->json($responseData, 200)
                ->header('Content-Type', 'application/json')
                ->header('Accept', 'application/json');

            Log::info('Response prepared:', [
                'status_code' => 200,
                'content_type' => 'application/json',
            ]);

            Log::info('========== LOGIN REQUEST SUCCESSFUL ==========');

            return $response;
        } catch (ValidationException $e) {
            // Validation errors - return JSON
            Log::error('========== LOGIN REQUEST FAILED - VALIDATION ERROR ==========');
            Log::error('Validation Exception:', [
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'email' => $request->email ?? 'not provided',
            ]);

            $response = response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422)->header('Content-Type', 'application/json');

            Log::info('Response: ', ['status' => 422, 'message' => 'Validation failed.']);

            return $response;
        } catch (\Exception $e) {
            // Catch any other exceptions and return JSON
            Log::error('========== LOGIN REQUEST FAILED - EXCEPTION ==========');
            Log::error('Exception Details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? 'not provided',
            ]);

            $response = response()->json([
                'success' => false,
                'message' => 'An error occurred during login. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500)->header('Content-Type', 'application/json');

            Log::info('Response: ', ['status' => 500, 'message' => 'An error occurred.']);

            return $response;
        }
    }

    // Register a new user
    public function register(Request $request)
    {
        // Force JSON response for POST requests
        if ($request->isMethod('post')) {
            $request->headers->set('Accept', 'application/json');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required' => 'Name is required.',
                'name.max' => 'Name cannot exceed 255 characters.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.unique' => 'This email is already registered.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            // Check if the user already exists (extra check)
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered.',
                ], 409);
            }

            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'user',
            ]);

            // Automatically log in the user after registration
            Auth::login($user);
            $request->session()->regenerate();

            // Determine redirect route based on user type
            $redirectRoute = route('user.home');
            if ($user->user_type == 'admin') {
                $redirectRoute = route('admin.dashboard');
            } else if ($user->user_type == 'client') {
                $redirectRoute = route('client.dashboard');
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! You have been logged in.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                ],
                'redirect' => $redirectRoute,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to home (React app will handle landing page)
        return redirect()->route('user.home')->with('clearSuccessMessage', true);
    }

    /**
     * API Login for mobile apps (Flutter) - returns token
     */
    public function apiLogin(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401);
            }

            // Create token for mobile app
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Build profile photo URL if exists
            $profilePhotoUrl = null;
            if (!empty($user->profile_photo_path)) {
                $profilePhotoUrl = url('storage/' . $user->profile_photo_path);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'token' => $token,
                'user' => [
                    'id' => (int) $user->id,
                    'name' => (string) ($user->name ?? ''),
                    'email' => (string) ($user->email ?? ''),
                    'user_type' => (string) ($user->user_type ?? 'user'),
                    'profile_photo_path' => $profilePhotoUrl,
                    'phone' => $user->phone ? (string) $user->phone : null,
                    'address' => $user->address ? (string) $user->address : null,
                    'city' => $user->city ? (string) $user->city : null,
                    'state' => $user->state ? (string) $user->state : null,
                    'country' => $user->country ? (string) $user->country : null,
                    'postal_code' => $user->postal_code ? (string) $user->postal_code : null,
                    'client_id' => $user->client_id ? (int) $user->client_id : null,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * API Register for mobile apps (Flutter) - returns token
     */
    public function apiRegister(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'name.required' => 'Name is required.',
                'name.max' => 'Name cannot exceed 255 characters.',
                'email.required' => 'Email is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.unique' => 'This email is already registered.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            // Check if the user already exists
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered.',
                ], 409);
            }

            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'user',
            ]);

            // Create token for mobile app
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Build profile photo URL if exists
            $profilePhotoUrl = null;
            if (!empty($user->profile_photo_path)) {
                $profilePhotoUrl = url('storage/' . $user->profile_photo_path);
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'token' => $token,
                'user' => [
                    'id' => (int) $user->id,
                    'name' => (string) ($user->name ?? ''),
                    'email' => (string) ($user->email ?? ''),
                    'user_type' => (string) ($user->user_type ?? 'user'),
                    'profile_photo_path' => $profilePhotoUrl,
                    'phone' => $user->phone ? (string) $user->phone : null,
                    'address' => $user->address ? (string) $user->address : null,
                    'city' => $user->city ? (string) $user->city : null,
                    'state' => $user->state ? (string) $user->state : null,
                    'country' => $user->country ? (string) $user->country : null,
                    'postal_code' => $user->postal_code ? (string) $user->postal_code : null,
                    'client_id' => $user->client_id ? (int) $user->client_id : null,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * API Logout for mobile apps (Flutter) - revokes token
     */
    public function apiLogout(Request $request)
    {
        try {
            // Revoke the current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('API Logout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
