<?php

use App\Http\Controllers\API\V1\Admin\System\DynamicPageController;
use App\Http\Controllers\API\V1\Admin\SystemSettingController;
use App\Http\Controllers\API\V1\Auth\LoginController;
use App\Http\Controllers\API\V1\Auth\LogoutController;
use App\Http\Controllers\API\V1\Auth\RegisterController;
use App\Http\Controllers\API\V1\Auth\ResetPasswordController;
use App\Http\Controllers\API\V1\Auth\UserController;
use App\Http\Controllers\API\V1\Public\GeneralSettingController;
use App\Http\Controllers\API\V1\Public\PackageController;
use App\Http\Controllers\API\V1\Public\StepperPageController as PublicStepperPageController;
use App\Http\Controllers\API\V1\User\CheckInController;
use App\Http\Controllers\API\V1\User\GoalController;
use App\Http\Controllers\API\V1\User\NotificationController;
use App\Http\Controllers\API\V1\User\PaymentController;
use App\Http\Controllers\API\V1\User\TipsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth');

Route::group(['middleware' => ['guest:api']], function ($router) {
// Route::group(['middleware' => ['guest:api', 'throttle:2,1']], function ($router) {
    // register
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']);
    Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']);
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
    Route::apiResource('goals', GoalController::class);
    Route::get('active-goal', [GoalController::class, 'getActiveGoal']);
    Route::get('weekly-streak', [GoalController::class, 'getWeeklyStreakData']);
    Route::apiResource('check-ins', CheckInController::class);
    Route::apiResource('payments', PaymentController::class);
    
    // tips
    Route::get('tips/today', [TipsController::class, 'getTodayTips']);
    Route::post('tips/refresh', [TipsController::class, 'refreshTips']);
    Route::post('tips/{id}/mark-done', [TipsController::class, 'markAsDone']);

    // notifications
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show']);
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/delete-all', [NotificationController::class, 'deleteAll']);
    
});

// this is for admin
// Route::group(['middleware' => ['auth:api', 'is_admin'], 'prefix' => 'admin'], function ($router) {
//     Route::apiResource('dynamic-pages', DynamicPageController::class);
//     Route::group(['prefix' => 'system'], function ($router) {
//         Route::get('settings', [SystemSettingController::class, 'index']);
//         Route::post('settings', [SystemSettingController::class, 'update']);
//         Route::get('mail-settings', [SystemSettingController::class, 'getMailSetting']);
//         Route::post('mail-settings', [SystemSettingController::class, 'updateMailSetting']);
//         Route::get('clear-cache', [SystemSettingController::class, 'clearCache']);
//     });
// });

// this is for public
Route::get('system-info', [GeneralSettingController::class, 'getSystemInfo']);
Route::get('dynamic-pages', [GeneralSettingController::class, 'getDynamicPages']);
Route::get('dynamic-pages/{page_slug}', [GeneralSettingController::class, 'showDaynamicPage']);
Route::get('faqs', [GeneralSettingController::class, 'getFaqs']);
Route::apiResource('packages', PackageController::class)->only(['index', 'show']);
Route::apiResource('stepper-pages', PublicStepperPageController::class)->only(['index', 'show']);

//  require __DIR__ . '/api_v1.php';
