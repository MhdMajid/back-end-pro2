<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminUserApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\TestNoti;
use App\Http\Controllers\PrivateNotificationController;
use App\Http\Middleware\CheckAdminRole;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// مسارات المصادقة
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// مسارات تتطلب المصادقة
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

// مسارات العقارات
Route::middleware('auth:sanctum')->group(function () {
Route::apiResource('properties', PropertyController::class);
Route::post('properties/{id}', [PropertyController::class, 'update']);

});

Route::get('properties', [PropertyController::class, 'index']);
Route::get('properties/{property}', [PropertyController::class, 'show']);

Route::middleware(['auth:sanctum', CheckAdminRole::class])->group(function () { // This 'admin' alias now works
    Route::apiResource('users', AdminUserApiController::class);
    Route::post('users/{id}', [AdminUserApiController::class, 'update']);
    Route::get('users/toggle-active/{id}', [AdminUserApiController::class, 'toggleActive']);
    
});
