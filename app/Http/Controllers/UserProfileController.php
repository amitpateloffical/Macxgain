<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Auth;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    public function getUserProfile()
    {
        $userId = Auth::guard('api')->user()->id;
        $user = DB::table('users')
            ->select(
                'users.*',
            )
            ->where('users.id', $userId)
            ->first();

        if ($user) {
            return response()->json([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_image' => $user->profile_image,
                ]
            ]);
        }

        return response()->json(['error' => 'User not found'], 404);
    }

    public function updateUserProfile(Request $request, $encodedId)
    {
        $id = base64_decode($encodedId);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|string|email|max:255',
        //     'profile_image' => 'nullable|image|mimes:jpg,png,gif|max:800',
        //     'phone' => 'required|numeric|digits:10',

        // ]);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255',
             'phone' => 'required|numeric|digits:10',
             'profile_image' => 'nullable|image|mimes:jpg,png,gif|max:800',
         ]);
         if ($validator->fails()) {
             return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
         }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::delete($user->profile_image);
            }
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $path;
        }
        $user->save();
        return response()->json(['message' => 'Profile updated successfully', 'data' => $user]);
    }

    public function register(Request $request)
    {
        $user = new User ();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile_code = '91';
        $user->status = 'I';
        $user->phone = $request->phone;
        $user->password = $request->password;
        $user->save();
        return response()->json(['message' => 'Profile updated successfully', 'data' => $user]);
    }
    public function changePassword(Request $request)
{
    $user = Auth::guard('api')->user();

    $validator = Validator::make($request->all(), [
        "current_password" => "required",
        'new_password' => [
            'required',
            'string',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
        ],
        'new_password_confirmation' => 'required|same:new_password',
    ], [
        'current_password.required' => 'The Current Password field is required.',
        'new_password.required' => 'The New Password field is required.',
        'new_password.string' => 'The New Password must be a string.',
        'new_password.min' => 'The New Password must be at least 8 characters.',
        'new_password_confirmation.required' => 'The Confirm Password field is required.',
        'new_password_confirmation.same' => 'The Confirm Password and New Password must match.',
    ]);

    if ($validator->fails()) {
        return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
    }

    if (!(Hash::check($request->get('current_password'), $user->password))) {
        return response()->json([
            'errors' => ['Your Current Password does not match with the password you provided.']
        ], 422);
    }

    if (strcmp($request->get('current_password'), $request->get('new_password')) == 0) {
        return response()->json([
            'errors' => ['New Password cannot be the same as your Current Password.']
        ], 422);
    }
    $user->password = bcrypt($request->get('new_password'));
    $user->save();
    return response()->json([
        'data' => [],
        'status' => 'success',
        'message' => 'Password changed successfully!'
    ], 200);
}

    
}
