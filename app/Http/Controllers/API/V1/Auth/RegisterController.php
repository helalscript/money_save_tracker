<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\Helper;
use App\Mail\OtpMail;
use App\Models\Contractor;
use App\Models\License;
use App\Notifications\UserRegistrationNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Register a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|numeric|max_digits:20|unique:users,phone,NULL,id,deleted_at,NULL',
            'email' => 'required|string|email|max:150|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $otp = 1234; // For testing purposes, you can use a fixed OTP. In production, generate a random OTP.
            // $otp = rand(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(60); // 1 hour

            // Check for soft-deleted user
            $existingUser = User::withTrashed()
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->input('email'));
                    if ($request->filled('phone')) {
                        $query->orWhere('phone', $request->input('phone'));
                    }
                })
                ->first();

            if ($existingUser && $existingUser->trashed()) {
                // Restore the soft-deleted user
                $existingUser->restore();
                $existingUser->update([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone') ?? null,
                    'password' => Hash::make($request->input('password')),
                    'role' => 'user', // Default role
                    'otp' => $otp,
                    'otp_expires_at' => $otpExpiresAt,
                ]);
                $user = $existingUser;
            } else {
                $userData = [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone') ?? null,
                    'password' => Hash::make($request->input('password')),
                    'role' => 'user', // Default role
                    'otp' => $otp,
                    'otp_expires_at' => $otpExpiresAt,
                ];
            }

            DB::beginTransaction();

            if (!isset($user)) {
                $user = User::create($userData);
            }

            // Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address')); // For testing purposes, you can comment this line. In production, uncomment it.

            DB::commit();
            return Helper::jsonResponse(true, 'A verification email has been successfully sent to your email address. Please check your inbox to complete the verification.', 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RegisterController::register' . $e->getMessage());
            return Helper::jsonErrorResponse('Failed to send verification email. Please try again later.', 500);
        }
    }

    /**
     * Verify the user's email address.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function VerifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            //! Check if email has already been verified
            if ($user->email_verified_at !== null) {
                return Helper::jsonResponse(false, 'Your email has already been verified. Please login to continue.', 409);
            }

            if ($user->otp !== $request->otp) {
                return Helper::jsonResponse(false, 'Invalid OTP. Please try again.', 422);
            }

            //* Check if OTP has expired
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonResponse(false, 'OTP has expired. Please request a new OTP.', 422);
            }

            //* Verify the email
            $user->email_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // user get a welcome notification
            $user->notify(new WelcomeNotification());

            // Notify admins about the new registration
            // $admins = User::where('role', 'admin')->get();
            // foreach ($admins as $admin) {
            //     $admin->notify(new UserRegistrationNotification($user, "{$user->name} has joined the platform. Please review their details."));
            // }

            // Generate an access token for the user
            $token = auth('api')->login($user);

            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully.',
                'code' => 200,
                'token_type' => 'bearer',
                'token' => $token,
                'data' => $user,
            ], 200);

        } catch (Exception $e) {
            Log::error('RegisterController::VerifyEmail' . $e->getMessage());
            return Helper::jsonErrorResponse('An error occurred during email verification.', 403);
        }
    }

    /**
     * Resend OTP to the user's email address.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ResendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        try {
            $user = User::where('email', $request->input('email'))->first();
            if (!$user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            if ($user->email_verified_at) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

            // $newOtp = rand(1000, 9999);
            $newOtp = 1234; // For testing purposes, you can comment this line. In production, uncomment it.
            $otpExpiresAt = Carbon::now()->addMinutes(60);
            $user->otp = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            //* Send the new OTP to the user's email

            try {
                // Mail::to($user->email)->send(new OtpMail($newOtp, $user, 'Verify Your Email Address')); // For testing purposes, you can comment this line. In production, uncomment it.
                return Helper::jsonResponse(true, 'A new OTP has been sent to your email.', 200);
            } catch (Exception $e) {
                // If email sending fails, delete the created user and return an error message
                // $user->delete();
                return Helper::jsonErrorResponse('Failed to send new OTP', 500);
            }

        } catch (Exception $e) {
            Log::error('RegisterController::ResendOtp' . $e->getMessage());
            return Helper::jsonErrorResponse('Something worng', 403);
        }
    }
}
