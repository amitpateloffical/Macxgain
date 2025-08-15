<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\EmailLogsController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\AdminSide\CategoryController;
use App\Http\Controllers\AdminSide\ProductController;
use App\Http\Controllers\MoneyRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterRequestController;





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
Route::get('/outlook/authenticate', [GraphController::class, 'authenticate']);
Route::get('/outlook/get-access-token', [GraphController::class, 'acquireAccessToken']);
Route::get('/outlook/getInbox', [GraphController::class, 'getInbox']);


Route::post('/login', [LoginController::class, 'store']);
Route::resource('/ticket-type', TicketTypeController::class);
Route::resource('/tag', TagController::class);
    Route::middleware('auth:sanctum')->group(function() {
        Route::post('/logout', [LoginController::class, 'Logout']);
    });
Route::post('/admin/forgot-password', [LoginController::class, 'forgotPassword']);
Route::get('/getactivity', [ActivityLogController::class, 'index']);
Route::get('/alluser', [ActivityLogController::class, 'getuser']);
Route::get('/getlogname', [ActivityLogController::class, 'getlogname']);
Route::get('/get-ticket-type', [TicketTypeController::class, 'getTicketType']);
Route::get('/get-ticket-teg', [TagController::class, 'getTicketTeg']);
Route::resource('/tickets', TicketController::class);
Route::get('/ticket-activities/{id}', [TicketController::class, 'getTicketActivity']);
Route::get('/getTicketOldNewActivity/{id}', [TicketController::class, 'getTicketOldNewActivity']);
Route::resource('/tasks', TaskController::class);
Route::get('/gettask/{id}', [TaskController::class, 'gettask']);
Route::get('/getactivity/{id}', [TicketController::class, 'getactivity']);

Route::get('/editticket-type/{id}', [TicketTypeController::class, 'edit']);
Route::get('/editag/{id}', [TagController::class, 'edit']);
Route::post('/resetPassword', [ResetPasswordController::class, 'reset'])->name('resetPassword');
Route::get('/userprofile', [UserProfileController::class, 'getUserProfile']);
Route::post('/update-profile/{id}', [UserProfileController::class, 'updateUserProfile']);
Route::post('/change-password', [UserProfileController::class, 'changePassword']);
Route::post('/register', [UserProfileController::class, 'register']);
Route::get('/email-logs', [EmailLogsController::class, 'index']);
Route::get('/login-logs', [EmailLogsController::class, 'getLoginLogs']);
Route::get('/getData', [ChartController::class, 'getData']);

//customer module
Route::post('/store', [CustomerController::class, 'store']);

Route::post('/upload-customer-excel', [CustomerController::class, 'uploadCustomers']);
Route::resource('/customer', CustomerController::class);
Route::get('/getcustomertask/{id}', [CustomerController::class, 'getcustomertask']);
Route::get('/getactivetickets/{id}', [CustomerController::class, 'getactivetickets']);
Route::get('/getCustomer', [CustomerController::class, 'getCustomer']);

Route::post('/addCustomers', [CustomerController::class, 'addCustomers']);

Route::get('/customer-activities/{id}', [CustomerController::class, 'getCustomerActivity']);
Route::get('/getCustomerOldNewActivity/{id}', [CustomerController::class, 'getCustomerOldNewActivity']);

Route::get('/getpriority', [TaskController::class, 'getpriority']);
Route::get('/getassignee', [TaskController::class, 'getassignee']);
Route::get('/getticketype', [TaskController::class, 'gettickettype']);

Route::resource('/notes', NoteController::class);
Route::get('/getconversationTypes', [ConversationController::class, 'getconversationTypes']);
Route::resource('/conversations', ConversationController::class);
Route::get('/getallcustomer', [CustomerController::class, 'getallcustomer']);
Route::get('/getAssignee', [CustomerController::class, 'getAssignee']);
Route::get('/edit/{id}', [CustomerController::class, 'edit']);
Route::post('/update/{id}', [CustomerController::class, 'update']);


//Macxgain
Route::get('/getCategoryOption', [CategoryController::class, 'getCategoryOption']);
Route::resource('/products', ProductController::class);


// new chalu 

    Route::post('/money-requests', [MoneyRequestController::class, 'store']);
    Route::get('/money-requests', [MoneyRequestController::class, 'index']);
    Route::put('/money-requests/{id}', [MoneyRequestController::class, 'updateStatus']);
    Route::match(['patch', 'put'], '/money-requests/{id}/status', [MoneyRequestController::class, 'updateStatus']);
    Route::get('/money-requests/{id}', [MoneyRequestController::class, 'show']);
    Route::middleware('auth:api')->get('/user-info', [UserController::class, 'getUserInfo']);

// User Management API Routes
Route::middleware('auth:api')->group(function() {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus']);
    
    // Register Requests API Routes
    Route::get('/register-requests', [RegisterRequestController::class, 'index']);
    Route::patch('/register-requests/{id}/approve', [RegisterRequestController::class, 'approve']);
    Route::patch('/register-requests/{id}/reject', [RegisterRequestController::class, 'reject']);
});






