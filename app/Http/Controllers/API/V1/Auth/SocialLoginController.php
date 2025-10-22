<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Notifications\UserRegistrationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;


class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the OAuth authorization page for the given provider.
     *
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function RedirectToProvider($provider)
    {
        // if ($provider === 'instagram') {
        //     return Socialite::driver($provider)->scopes(['user_profile', 'user_media'])->redirect();
        // }
        return Socialite::driver($provider)->redirect();
    }


    public function HandleProviderCallback($provider)
    {
        // try {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        dd($socialUser);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'Unable to authenticate with ' . ucfirst($provider),
        //         'error'   => $e->getMessage()
        //     ], 500);
        // }
    }

    /**
     * Handles social login request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function SocialLogin(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'provider' => 'required|in:google,facebook,apple,instagram',
        ]);

        try {
            $provider = $request->provider;
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->token);

            if ($socialUser) {
                $user = User::where('email', $socialUser->email)->first();

                if (!$user) {
                    $password = Str::random(16);
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'password' => bcrypt($password),
                        'avatar' => $socialUser->getAvatar(),
                        'role' => 'user',
                        'email_verified_at' => now(),
                    ]);
                }

                Auth::login($user);
                $token = auth('api')->login($user);

                return response()->json([
                    'status' => true,
                    'message' => 'User logged in successfully.',
                    'code' => 200,
                    'token_type' => 'bearer',
                    'token' => $token,
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'data' => $user
                ], 200);
            } else {
                return Helper::jsonResponse(false, 'Unauthorized', 401);
            }
        } catch (Exception $e) {
            Log::error('SocialLoginController::SocialLogin' . $e->getMessage());
            return Helper::jsonResponse(false, 'Something went wrong', 500, ['error' => $e->getMessage()]);
        }
    }
}
