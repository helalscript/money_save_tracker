<?php

use App\Http\Controllers\API\V1\Admin\StoreDetailsController;
use App\Http\Controllers\API\V1\Admin\System\DynamicPageController;
use App\Http\Controllers\API\V1\Admin\SystemSettingController;
use App\Http\Controllers\API\V1\Admin\TimePackageController;
use App\Http\Controllers\API\V1\Auth\LoginController;
use App\Http\Controllers\API\V1\Auth\LogoutController;
use App\Http\Controllers\API\V1\Auth\RegisterController;
use App\Http\Controllers\API\V1\Auth\ResetPasswordController;
use App\Http\Controllers\API\V1\Auth\SocialLoginController;
use App\Http\Controllers\API\V1\Auth\UserController;
use App\Http\Controllers\API\V1\Public\GeneralSettingController;
use App\Http\Controllers\API\V1\User\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth');

Route::group(['middleware' => ['guest:api', 'throttle:2,1']], function ($router) {
    // register
    // Route::post('register', [RegisterController::class, 'register']);
    // Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']);
    // Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']);
    // login
    Route::post('login', [LoginController::class, 'login']);
    // forgot password
    Route::post('/forget-password', [ResetPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
    Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
    // social login
    // Route::post('/social-login', [SocialLoginController::class, 'SocialLogin']);
});

Route::group(['middleware' => 'auth:api'], function ($router) {
    Route::get('/refresh-token', [LoginController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/update-password', [UserController::class, 'changePassword']);
});

// this is for user
Route::group(['middleware' => ['auth:api', 'is_user']], function ($router) {
    Route::apiResource('customers', CustomerController::class);
});

// this is for admin
Route::group(['middleware' => ['auth:api', 'is_admin'], 'prefix' => 'admin'], function ($router) {
    Route::apiResource('store-details', StoreDetailsController::class);
    Route::apiResource('time-packages', TimePackageController::class);
    Route::apiResource('dynamic-pages', DynamicPageController::class);
    Route::group(['prefix' => 'system'], function ($router) {
        Route::get('settings', [SystemSettingController::class, 'index']);
        Route::post('settings', [SystemSettingController::class, 'update']);
        Route::get('mail-settings', [SystemSettingController::class, 'getMailSetting']);
        Route::post('mail-settings', [SystemSettingController::class, 'updateMailSetting']);
        Route::get('clear-cache', [SystemSettingController::class, 'clearCache']);
    });
});

// this is for public
Route::get('system-info', [GeneralSettingController::class, 'getSystemInfo']);
Route::get('dynamic-pages', [GeneralSettingController::class, 'getDynamicPages']);
Route::get('dynamic-pages/{page_slug}', [GeneralSettingController::class, 'showDaynamicPage']);

//  require __DIR__ . '/api_v1.php';
