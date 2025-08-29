<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SystemController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\EmailLogsController;
use App\Http\Controllers\ActivityLogController;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterRequestController;
use App\Http\Controllers\WithdrawalRequestController;
use App\Http\Controllers\UpstoxController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AdminPaymentCollectorController;






/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/auth/token', [AuthController::class, 'displayAccessToken']);


Route::post('/login', [LoginController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function() {
        Route::post('/logout', [LoginController::class, 'Logout']);
    });
Route::post('/admin/forgot-password', [LoginController::class, 'forgotPassword']);
Route::get('/getactivity', [ActivityLogController::class, 'index']);
Route::get('/alluser', [ActivityLogController::class, 'getuser']);
Route::get('/getlogname', [ActivityLogController::class, 'getlogname']);

Route::post('/resetPassword', [ResetPasswordController::class, 'reset'])->name('resetPassword');
Route::get('/userprofile', [UserProfileController::class, 'getUserProfile']);
Route::post('/update-profile/{id}', [UserProfileController::class, 'updateUserProfile']);
Route::post('/change-password', [UserProfileController::class, 'changePassword']);
Route::post('/register', [UserProfileController::class, 'register']);
Route::get('/email-logs', [EmailLogsController::class, 'index']);
Route::get('/login-logs', [EmailLogsController::class, 'getLoginLogs']);


//customer module




// new chalu 

    Route::post('/money-requests', [MoneyRequestController::class, 'store']);
    Route::get('/money-requests', [MoneyRequestController::class, 'index']);
    Route::put('/money-requests/{id}', [MoneyRequestController::class, 'updateStatus']);
    Route::match(['patch', 'put'], '/money-requests/{id}/status', [MoneyRequestController::class, 'updateStatus']);
    Route::get('/money-requests/{id}', [MoneyRequestController::class, 'show']);

    Route::post('/withdrawal-request', [WithdrawalRequestController::class, 'store']);
    Route::get('/withdrawal-request', [WithdrawalRequestController::class, 'index']);
    Route::put('/withdrawal-request/{id}', [WithdrawalRequestController::class, 'updateStatus']);
    Route::match(['patch', 'put'], '/withdrawal-request/{id}/status', [WithdrawalRequestController::class, 'updateStatus']);
    Route::get('/withdrawal-request/{id}', [WithdrawalRequestController::class, 'show']);
    Route::get('/checkBankInfo', [WithdrawalRequestController::class, 'checkBankInfo']);


    Route::middleware('auth:api')->get('/user-info', [UserController::class, 'getUserInfo']);

// User Management API Routes
Route::middleware('auth:api')->group(function() {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);

    Route::post('/total_b', [UserController::class, 'total_b']);
    
    // Register Requests API Routes
    Route::get('/register-requests', [RegisterRequestController::class, 'index']);
    Route::patch('/register-requests/{id}/approve', [RegisterRequestController::class, 'approve']);
    Route::patch('/register-requests/{id}/reject', [RegisterRequestController::class, 'reject']);
});

// Upstox API Routes
Route::middleware('auth:sanctum')->group(function() {
    Route::get('/upstox/test', [UpstoxController::class, 'testConnection']);
    Route::get('/upstox/dashboard', [UpstoxController::class, 'getDashboardData']);
    Route::get('/upstox/market-quotes', [UpstoxController::class, 'getMarketQuotes']);
    Route::get('/upstox/market-status', [UpstoxController::class, 'getMarketStatus']);
    Route::get('/upstox/historical-data', [UpstoxController::class, 'getHistoricalData']);
    Route::get('/upstox/search-instruments', [UpstoxController::class, 'searchInstruments']);
    Route::get('/upstox/top-gainers', [UpstoxController::class, 'getTopGainers']);
    Route::get('/upstox/top-losers', [UpstoxController::class, 'getTopLosers']);
    Route::get('/upstox/market-indices', [UpstoxController::class, 'getMarketIndices']);
    Route::get('/upstox/live-stock-data', [UpstoxController::class, 'getLiveStockData']);
    
    // Analytics API Routes
    Route::get('/analytics', [AnalyticsController::class, 'getAnalyticsData']);
    
    // Admin Payment Collector Routes
    Route::apiResource('admin-payment-collectors', AdminPaymentCollectorController::class);
    Route::patch('/admin-payment-collectors/{id}/primary', [AdminPaymentCollectorController::class, 'markAsPrimary']);
    Route::patch('/admin-payment-collectors/{id}/toggle-status', [AdminPaymentCollectorController::class, 'toggleStatus']);
});

// Public Payment Collector Routes (for users to see payment details)
Route::get('/payment-collector/primary', [AdminPaymentCollectorController::class, 'getPrimary']);






