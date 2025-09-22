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
use App\Http\Controllers\TrueDataController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AdminPaymentCollectorController;
use App\Http\Controllers\AITradingController;
use App\Http\Controllers\FundAdjustController;
use App\Http\Controllers\AlphaVantageController;
use App\Http\Controllers\BackupController;






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
    
    // Fund Adjustment API Routes
    Route::get('/users/search', [FundAdjustController::class, 'searchUsers']);
    Route::post('/admin/fund-adjust', [FundAdjustController::class, 'adjustFund']);
    Route::get('/admin/fund-adjustments', [FundAdjustController::class, 'getRecentAdjustments']);
});

// TrueData API Routes (temporarily without auth for testing)
Route::get('/truedata/test', [TrueDataController::class, 'testConnection']);
Route::get('/truedata/dashboard', [TrueDataController::class, 'getDashboardData']);
Route::get('/truedata/market-quotes', [TrueDataController::class, 'getMarketQuotes']);
Route::get('/truedata/market-status', [TrueDataController::class, 'getMarketStatus']);
Route::get('/truedata/historical-data', [TrueDataController::class, 'getHistoricalData']);
Route::get('/truedata/search-instruments', [TrueDataController::class, 'searchInstruments']);
Route::post('/truedata/search-stock', [TrueDataController::class, 'searchStock']);
Route::get('/truedata/top-gainers', [TrueDataController::class, 'getTopGainers']);
Route::get('/truedata/top-losers', [TrueDataController::class, 'getTopLosers']);
Route::get('/truedata/market-indices', [TrueDataController::class, 'getMarketIndices']);
Route::get('/truedata/live-stock-data', [TrueDataController::class, 'getLiveStockData']);
Route::post('/truedata/subscribe-symbols', [TrueDataController::class, 'subscribeToSymbols']);

// New Python-based live data routes (temporarily without auth for testing)
Route::get('/truedata/live-data', [TrueDataController::class, 'getLiveDataFromPython']);
Route::post('/truedata/trigger-fetch', [TrueDataController::class, 'triggerDataFetch']);

// Options API Routes
Route::get('/truedata/options/valid-symbols', [TrueDataController::class, 'getValidOptionSymbols']);
Route::get('/truedata/options/chain/{symbol}', [TrueDataController::class, 'getOptionChain']);
Route::get('/truedata/options/expiries/{symbol}', [TrueDataController::class, 'getOptionExpiries']);
// Test route for debugging
Route::get('/truedata/test', function() {
    return response()->json(['success' => true, 'message' => 'Test route working']);
});
Route::get('/truedata/test-option/{symbol}', [TrueDataController::class, 'testOptionChain']);
Route::get('/truedata/simple-option/{symbol}', function($symbol) {
    try {
        // Simple direct implementation
        $trueDataUrl = "https://api.truedata.in/getOptionChain?user=Trial189&password=patel189&symbol={$symbol}&expiry=20250916";
        $rawResponse = file_get_contents($trueDataUrl);
        $trueDataResponse = json_decode($rawResponse, true);
        
        return response()->json([
            'success' => true,
            'symbol' => $symbol,
            'api_status' => $trueDataResponse['status'] ?? 'unknown',
            'total_records' => count($trueDataResponse['Records'] ?? []),
            'sample_record' => ($trueDataResponse['Records'][0] ?? null),
            'data_source' => 'TrueData API Direct'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
Route::get('/truedata/options/dashboard', [TrueDataController::class, 'getOptionsDashboard']);
Route::get('/truedata/options/popular', [TrueDataController::class, 'getPopularOptions']);
Route::get('/truedata/options/current-price', [TrueDataController::class, 'getCurrentOptionPrice']);

// Alpha Vantage API Routes
Route::prefix('alphavantage')->group(function() {
    // Stock Data Routes
    Route::get('/stocks/popular', [AlphaVantageController::class, 'getPopularStocks']);
    Route::get('/stocks/categories', [AlphaVantageController::class, 'getStockCategories']);
    Route::get('/stocks/by-category', [AlphaVantageController::class, 'getStocksByCategory']);
    Route::get('/stocks/search', [AlphaVantageController::class, 'searchSymbols']);
    Route::get('/stocks/{symbol}/quote', [AlphaVantageController::class, 'getQuote']);
    Route::get('/stocks/{symbol}/intraday', [AlphaVantageController::class, 'getIntradayData']);
    Route::get('/stocks/{symbol}/daily', [AlphaVantageController::class, 'getDailyData']);
    Route::get('/stocks/{symbol}/overview', [AlphaVantageController::class, 'getCompanyOverview']);
    Route::get('/stocks/{symbol}/data', [AlphaVantageController::class, 'getStockData']);
    Route::get('/stocks/{symbol}/dashboard', [AlphaVantageController::class, 'getStockDashboardData']);
    
    // Options Routes
    Route::get('/options/{symbol}', [AlphaVantageController::class, 'getOptionsData']);
    
    // Market Data Routes
    Route::get('/market/status', [AlphaVantageController::class, 'getMarketStatus']);
    Route::get('/market/top-gainers-losers', [AlphaVantageController::class, 'getTopGainersLosers']);
    
    // Technical Indicators Routes
    Route::get('/indicators/{symbol}/sma', [AlphaVantageController::class, 'getSMA']);
    Route::get('/indicators/{symbol}/rsi', [AlphaVantageController::class, 'getRSI']);
});

// Legacy Upstox routes (for backward compatibility)
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
    
    // Backup Routes
    Route::get('/backups', [BackupController::class, 'index']);
    Route::post('/backups/create', [BackupController::class, 'create']);
    Route::get('/backups/{backupId}/download', [BackupController::class, 'download']);
    Route::post('/backups/{backupId}/restore', [BackupController::class, 'restore']);
    Route::delete('/backups/{backupId}', [BackupController::class, 'delete']);
});

// Public Payment Collector Routes (for users to see payment details)
Route::get('/payment-collector/primary', [AdminPaymentCollectorController::class, 'getPrimary']);

// AI Trading API Routes
Route::middleware('auth:api')->group(function() {
    Route::post('/ai-trading/execute-trade', [AITradingController::class, 'executeTrade']);
    Route::post('/ai-trading/place-stock-order', [AITradingController::class, 'placeStockOrder']);
    Route::post('/ai-trading/add-initial-funds', [AITradingController::class, 'addInitialFunds']);
    Route::get('/ai-trading/debug-balance', [AITradingController::class, 'debugBalance']);
    Route::get('/ai-trading/user-orders/{userId}', [AITradingController::class, 'getUserOrders']);
    Route::get('/ai-trading/user-balance/{userId}', [AITradingController::class, 'getUserBalance']);
    Route::get('/ai-trading/orders', [AITradingController::class, 'getAllOrders']);
    Route::patch('/ai-trading/orders/{orderId}/cancel', [AITradingController::class, 'cancelOrder']);
    Route::post('/ai-trading/orders/{orderId}/exit', [AITradingController::class, 'exitTrade']);
    Route::get('/ai-trading/orders/{orderId}/details', [AITradingController::class, 'getTradeDetails']);
    Route::get('/ai-trading/users/{userId}/live-pnl', [AITradingController::class, 'getLivePnL']);
    Route::get('/ai-trading/market-status', [AITradingController::class, 'getMarketStatus']);
    Route::get('/ai-trading/stats', [AITradingController::class, 'getTradingStats']);
});






