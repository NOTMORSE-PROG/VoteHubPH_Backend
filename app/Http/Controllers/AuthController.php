<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Send OTP to user's email
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if email already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            // If user exists with Google OAuth, tell them to sign in with Google
            if ($existingUser->provider === 'google') {
                return response()->json([
                    'error' => 'This email is already registered with Google. Please sign in using Google OAuth instead.'
                ], 400);
            } else {
                // User already has credentials account
                return response()->json([
                    'error' => 'Email already registered. Please use login instead.'
                ], 400);
            }
        }

        // Clean up expired OTPs
        Otp::cleanupExpired();

        // Rate limiting: Check cooldown period based on number of attempts
        $otpAttemptsKey = "otp_attempts_{$request->email}";
        $otpLastSentKey = "otp_last_sent_{$request->email}";
        
        $attempts = (int) cache()->get($otpAttemptsKey, 0);
        $lastSent = cache()->get($otpLastSentKey);
        
        if ($lastSent) {
            // Determine cooldown: 1 minute for first 2 resends, 3 minutes after 3+ attempts
            $cooldownMinutes = $attempts >= 3 ? 3 : 1;
            $cooldownEnd = (clone $lastSent)->addMinutes($cooldownMinutes);
            
            if (now()->isBefore($cooldownEnd)) {
                $secondsRemaining = now()->diffInSeconds($cooldownEnd);
                $minutesRemaining = ceil($secondsRemaining / 60);
                
                return response()->json([
                    'error' => "Please wait {$minutesRemaining} minute(s) before requesting a new OTP.",
                    'cooldown_seconds' => $secondsRemaining,
                    'cooldown_minutes' => $minutesRemaining,
                ], 429); // 429 Too Many Requests
            }
        }

        // Generate 6-digit OTP
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing OTP for this email
        Otp::where('email', $request->email)->delete();
        
        // Increment attempt counter and update last sent time
        $attempts++;
        cache()->put($otpAttemptsKey, $attempts, now()->addHours(24)); // Store attempts for 24 hours
        cache()->put($otpLastSentKey, now(), now()->addHours(24)); // Store last sent time for 24 hours

        // Create new OTP (expires in 5 minutes)
        Otp::create([
            'email' => $request->email,
            'code' => $otpCode,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Store name and password in session/cache for verification step (5 minutes to match OTP expiration)
        cache()->put("otp_data_{$request->email}", [
            'name' => $request->name,
            'password' => $request->password,
        ], now()->addMinutes(5));

        // Send OTP email
        try {
            // Check if mail is configured
            $mailHost = config('mail.mailers.smtp.host');
            $mailUsername = config('mail.mailers.smtp.username');
            
            if (empty($mailHost) || empty($mailUsername)) {
                // Mail not configured - in development, return OTP in response
                if (config('app.debug')) {
                    \Log::warning('Mail not configured. Returning OTP in response for development.');
                    return response()->json([
                        'message' => 'OTP generated (mail not configured)',
                        'success' => true,
                        'otp' => $otpCode, // Only in development
                        'warning' => 'Mail configuration is missing. Please configure SMTP settings in .env file.',
                    ]);
                } else {
                    return response()->json([
                        'error' => 'Email service is not configured. Please contact support.'
                    ], 500);
                }
            }
            
            Mail::to($request->email)->send(new OtpMail($otpCode));
            
            \Log::info('OTP email sent successfully to: ' . $request->email);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP email: ' . $e->getMessage());
            \Log::error('Email error details: ' . $e->getTraceAsString());
            \Log::error('Mail config - Host: ' . config('mail.mailers.smtp.host') . ', Username: ' . config('mail.mailers.smtp.username'));
            
            // Always return OTP in response if email fails (for debugging and user experience)
            // This allows users to complete registration even if email service is down
            \Log::warning('OTP email failed, returning OTP in response for: ' . $request->email);
            return response()->json([
                'message' => 'OTP generated. Check your email. If you did not receive it, use the code below.',
                'success' => true,
                'otp' => $otpCode, // Return OTP in response as fallback
                'warning' => 'Email sending failed: ' . $e->getMessage(),
                'email_sent' => false,
            ]);
        }

        return response()->json([
            'message' => 'OTP sent successfully to your email',
            'success' => true,
        ]);
    }

    /**
     * Verify OTP and register user
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Find OTP record
        $otp = Otp::where('email', $request->email)
            ->where('code', $request->otp)
            ->first();

        if (!$otp) {
            return response()->json([
                'error' => 'Invalid OTP code'
            ], 400);
        }

        if ($otp->isExpired()) {
            $otp->delete();
            return response()->json([
                'error' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        // Get stored registration data
        $registrationData = cache()->get("otp_data_{$request->email}");
        if (!$registrationData) {
            return response()->json([
                'error' => 'Registration data expired. Please start over.'
            ], 400);
        }

        // Check if user already exists (should not happen if sendOtp check worked, but double-check)
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser) {
            // User already exists - this shouldn't happen if sendOtp check worked
            $otp->delete();
            cache()->forget("otp_data_{$request->email}");
            
            if ($existingUser->provider === 'google') {
                return response()->json([
                    'error' => 'This email is already registered with Google. Please sign in using Google OAuth instead.'
                ], 400);
            } else {
                return response()->json([
                    'error' => 'Email already registered. Please use login instead.'
                ], 400);
            }
        } else {
            // Create new user
            // Note: User table uses camelCase column names
            $userId = $this->generateCuid();
            
            // Use DB::table() directly to handle camelCase column names
            DB::table('User')->insert([
                'id' => $userId,
                'name' => $registrationData['name'],
                'email' => $request->email,
                'password' => Hash::make($registrationData['password']),
                'provider' => 'credentials',
                'language' => $request->language ?? 'en',
                'profileCompleted' => true, // No longer require profile completion
                'emailVerified' => now(),
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);
            
            // Fetch the created user using the model
            $user = User::find($userId);
        }

        // Clean up
        $otp->delete();
        cache()->forget("otp_data_{$request->email}");
        
        // Reset OTP rate limiting counters on successful verification
        cache()->forget("otp_attempts_{$request->email}");
        cache()->forget("otp_last_sent_{$request->email}");

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified and account created successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Register a new user (deprecated - use sendOtp/verifyOtp instead)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'id' => $this->generateCuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'provider' => 'credentials',
            'language' => $request->language ?? 'en',
            'profile_completed' => false,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user has a password set
        if (!$user->password) {
            return response()->json([
                'message' => 'This account was created with Google. Please sign in with Google or set a password first.'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Update last login
        $user->update(['lastLoginAt' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Admin login (separate endpoint for admin dashboard)
     */
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user is admin
        if (!$user->is_admin) {
            return response()->json([
                'message' => 'Admin access required'
            ], 403);
        }

        // Check if user has a password set
        if (!$user->password) {
            return response()->json([
                'message' => 'Password not set for this admin account'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Update last login
        $user->update(['lastLoginAt' => now()]);

        // Return success (admin dashboard will use session-based auth)
        return response()->json([
            'message' => 'Admin login successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'is_admin' => true,
            ],
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Handle Google OAuth callback
     */
    public function googleCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string',
            'google_id' => 'required|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user exists with this email
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // User exists, update last login
            $user->update([
                'last_login_at' => now(),
                'image' => $request->image ?? $user->image,
            ]);

            // Check if Google account is linked
            $account = Account::where('user_id', $user->id)
                ->where('provider', 'google')
                ->first();

            if (!$account) {
                // Link Google account
                Account::create([
                    'id' => $this->generateCuid(),
                    'user_id' => $user->id,
                    'type' => 'oauth',
                    'provider' => 'google',
                    'provider_account_id' => $request->google_id,
                ]);
            }
        } else {
            // Create new user
            // Note: User table uses camelCase column names
            $userId = $this->generateCuid();
            
            // Use DB::table() directly to handle camelCase column names
            DB::table('User')->insert([
                'id' => $userId,
                'email' => $request->email,
                'name' => $request->name,
                'image' => $request->image,
                'provider' => 'google',
                'providerId' => $request->google_id,
                'language' => 'en',
                'profileCompleted' => true, // No longer require profile completion
                'emailVerified' => now(),
                'lastLoginAt' => now(),
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);
            
            // Fetch the created user using the model
            $user = User::find($userId);

            // Create Google account entry
            Account::create([
                'id' => $this->generateCuid(),
                'user_id' => $user->id,
                'type' => 'oauth',
                'provider' => 'google',
                'provider_account_id' => $request->google_id,
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Google authentication successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Generate a CUID (compatible with Prisma)
     * Simple implementation - for production, use a proper CUID library
     */
    private function generateCuid()
    {
        return 'c' . Str::random(24);
    }
}
