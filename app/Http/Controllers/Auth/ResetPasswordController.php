<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Log;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $email = decrypt($request->email);
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
            'cPassword' => [
                'required',
                'same:password',
            ],
        ]);

        if (strtotime(now()) >= $request->expires) {
            return response(['data' => 'Your Reset Password link has expired', 'status' => 'success', 'code' => 422], 422);
        }

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
        }

        DB::beginTransaction();
        try {
            $password = Hash::make($request->password);
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response(['data' => 'User not found', 'status' => 'error', 'code' => 404], 404);
            }
            $user->password = $password;
            $user->save();
        } catch (\Exception $e) {
            report($e);
            DB::rollback();
            return response(['data' => 'Something Went Wrong', 'status' => 'error'], 500);
        }

        DB::commit();
        return response(['status' => "success"]);
    }
}
