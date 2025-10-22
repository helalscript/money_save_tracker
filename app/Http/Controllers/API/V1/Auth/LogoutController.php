<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Exception;
use Illuminate\Support\Facades\Log;

class LogoutController extends Controller
{
    /**
     * Revoke the token for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            if (Auth::check('api')) {
                Auth::logout('api');
                return Helper::jsonResponse(true, 'Logged out successfully. Token revoked.', 200);
            } else {
                return Helper::jsonErrorResponse('User not authenticated', 401);
            }
        } catch (Exception $e) {
            Log::error('LogoutController::logout' . $e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
