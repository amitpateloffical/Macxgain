<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\EmailLogs;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\ForgotPassword;
use App\Jobs\SendEmailJob;
use URL;
use DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use Log;
use Auth;

class LoginController extends Controller
{
    
    public function index()
    {
       
    }
    public function create()
    {
        
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        
        if ($request->password != $user->password) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        
        if ($user->status === 'I') {
            return response()->json(['error' => 'User is inactive'], 403); // 403 Forbidden or another code you prefer
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->user_password = $request->password;
        $ipAddress = $request->ip();
        $userAgent = $request->header('User-Agent');
        LoginLog::create([
            'user_id'    => $user->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'login_at'   => Carbon::now(),
        ]);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
    public function show(string $id)
    {
        
    }
    public function edit(string $id)
    {
       
    }
    public function update(Request $request, string $id)
    {
       
    }
    public function destroy(string $id)
    {
        
    }
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'=>'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
        }
        $error=[];
         $user =User::where('email', $request->email)->first();
        if ($user) {
            $email=encrypt($user->email);
            $resetLink = URL::temporarySignedRoute('resetPassword', Carbon::now()->addMinutes(30), ['email' => $email]);
            $emailLog = EmailLogs::create([
                'email' => $user->email,
                'subject' => 'Password Reset Request',
                'content' => 'Reset your password using the following link: ' . $resetLink,
                'status' => 'pending'
            ]);
            $consultantdetails = [];
            $consultantdetails['email'] = $user->email;
            $consultantdetails['mailable_data'] = new ForgotPassword($user->name, $resetLink);
            if (dispatch(new SendEmailjob($consultantdetails))) {
                $emailLog->update(['status' => 'sent']);
                return response(['data'=>'Reset Password Mail Sent Successfully.','status'=>'success'], 200);
            } else {
                $emailLog->update(['status' => 'failed', 'error_message' => 'Failed to dispatch email job']);
                return response(['data'=>'Something Went Wrong','status'=>'success'], 422);
            }
        } else {
            $error = array_merge($error, ['email' => ['This User name does not exist']]);
            return response(['errors' => $error, 'code' => 422], 422);
        }
    }
    public function Logout(Request $request)
    {
        $extra_message = '';
    
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
        if ($user && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
                $loginLog = LoginLog::where('user_id', auth()->id())->latest()->first();
                if ($loginLog) {
                    $loginLog->update([
                        'logout_at' => Carbon::now(),
                        'login_duration' => Carbon::now()->diffForHumans($loginLog->login_at, true),
                    ]);
                }    
                $extra_message = 'Auth Guard Authenticated';
            } else {
                return response(['status' => false, 'message' => 'No active session found.'], 400);
            }
        } else {
            $extra_message = 'Auth Guard Check Failed';
            return response(['status' => false, 'message' => 'Unauthorized.'], 401);
        }
    
        return response(['status' => true, 'is_login' => false, 'data' => [], 'message' => 'Logout Successfully', 'extra_message' => $extra_message], 200);
    }
}
