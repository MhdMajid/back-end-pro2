<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminUserApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\TestNoti;
use App\Http\Controllers\PrivateNotificationController;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\DashboardController;

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

// مسارات تحديث حالة العقار وتوفره
    Route::post('properties/status/{id}', [PropertyController::class, 'updateStatus']);
    Route::get('properties/availability/{id}', [PropertyController::class, 'updateAvailability']);
    Route::get('user/properties', [PropertyController::class, 'getUserProperties']);
});

Route::get('properties', [PropertyController::class, 'index']);
Route::get('properties/{property}', [PropertyController::class, 'show']);

Route::middleware(['auth:sanctum', CheckAdminRole::class])->group(function () { // This 'admin' alias now works
    Route::apiResource('users', AdminUserApiController::class);
    Route::post('users/{id}', [AdminUserApiController::class, 'update']);
    Route::get('users/toggle-active/{id}', [AdminUserApiController::class, 'toggleActive']);
    
});

// مسارات المفضلات
Route::middleware('auth:sanctum')->group(function () {
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/{propertyId}', [FavoriteController::class, 'store']);
    Route::delete('favorites/{propertyId}', [FavoriteController::class, 'destroy']);
    Route::get('favorites/check/{propertyId}', [FavoriteController::class, 'check']);
});

// مسارات طلبات شراء العقارات
Route::middleware('auth:sanctum')->group(function () {
    // عرض قائمة طلبات الشراء للمستخدم الحالي
    Route::get('purchase-requests', [PurchaseController::class, 'index']);
    
    // عرض قائمة المدفوعات للمستخدم الحالي
    Route::get('payments', [PurchaseController::class, 'payments']);
    
    // إنشاء طلب شراء جديد
    Route::post('purchase-requests/{propertyId}', [PurchaseController::class, 'store']);
    
    // عرض تفاصيل طلب شراء
    Route::get('purchase-requests/{id}', [PurchaseController::class, 'show']);
    
    // تحديث حالة طلب الشراء
    Route::post('purchase-requests/update/{id}', [PurchaseController::class, 'update']);
    
    // إضافة دفعة جديدة لطلب الشراء
    Route::post('purchase-requests/{id}/payments', [PurchaseController::class, 'addPayment']);
    
    // التحقق من دفعة (للمشرفين فقط)
    Route::middleware(CheckAdminRole::class)->group(function () {
        Route::post('payments/{paymentId}/verify', [PurchaseController::class, 'verifyPayment']);
    });
});

// مسارات نظام المزادات

Route::get('auctions', [AuctionController::class, 'index']);
Route::get('auctions/{id}', [AuctionController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    // عرض مزادات المستخدم الحالي
    Route::get('user/auctions', [AuctionController::class, 'userAuctions']);
    
    // إنشاء مزاد جديد
    Route::post('auctions/{propertyId}', [AuctionController::class, 'store']);
    
    // تحديث حالة المزاد
    Route::post('auctions/status/{id}', [AuctionController::class, 'updateStatus']);

    // مسارات لوحة التحكم
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/properties/stats', [DashboardController::class, 'getPropertyStats']);
    Route::get('/dashboard/auctions/stats', [DashboardController::class, 'getAuctionStats']);
    Route::get('/dashboard/users/stats', [DashboardController::class, 'getUserStats']);
    Route::get('/dashboard/payments/stats', [DashboardController::class, 'getPaymentStats']);
    
    // تقديم عرض في المزاد
    Route::post('auctions/{id}/bids', [AuctionController::class, 'placeBid']);
});

