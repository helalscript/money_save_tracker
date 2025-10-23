<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Logs in the user.
     *
     * @bodyParam email string required The email of the user.
     * @bodyParam password string required The password of the user.
     * @bodyParam role string required The role of the user. Can be either customer or contractor.
     */
    public function Login(Request $request)
    {

        // return $next($request);
        $validateData = $request->validate([
            'email' => 'required_without:user_name|string',
            'password' => 'required|string',
            // 'user_name' => 'required_without:email|string',
            // 'role' => 'required|in:customer,contractor',
        ]);
        try {
            if (filter_var(isset($validateData['email']) ? $validateData['email'] : null, FILTER_VALIDATE_EMAIL) !== false) {
                $user = User::where('email', $validateData['email'])->first();
                if (empty($user)) {
                    return Helper::jsonErrorResponse('No account found with this email.', 422, ['email' => 'No account found with this email.']);
                }
            }
            if (isset($validateData['user_name'])) {
                $user = User::where('user_name', $validateData['user_name'])->first();
                if (empty($user)) {
                    return Helper::jsonErrorResponse('No account found with this user name.', 422, ['user_name' => 'No account found with this user name.']);
                }
            }
            if ($user && empty($user->email_verified_at)) {
                return Helper::jsonErrorResponse('Your email address has not been verified yet. Please check your inbox for the verification email and verify your account.', 403);
            }
            // if(($user->role==='admin')){
            //     return Helper::jsonErrorResponse('Admin cannot login in user panel', 422, ['login' => 'Admin cannot login in user panel.']);
            // }

            // ! Check the password
            if (! Hash::check($request->password, $user->password)) {
                return Helper::jsonErrorResponse('Invalid password', 401);
            }
            // * Generate token if email is verified
            $token = auth('api')->login($user);

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully.',
                'code' => 200,
                'token_type' => 'bearer',
                'token' => $token,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'data' => auth('api')->user(),
            ], 200);
        } catch (Exception $e) {
            Log::error('LoginController::Login'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Refreshes the JWT access token for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception If an error occurs during token refresh.
     */
    public function refreshToken()
    {
        try {
            $refreshToken = auth('api')->refresh();

            return response()->json([
                'status' => true,
                'message' => 'Access token refreshed successfully.',
                'code' => 200,
                'token_type' => 'bearer',
                'token' => $refreshToken,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'data' => auth('api')->user(),
            ]);
        } catch (Exception $e) {
            Log::error('LoginController::refreshToken'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
