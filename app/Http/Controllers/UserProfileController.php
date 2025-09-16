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
                    'bank_name'=>$user->bank_name,
                    'account_no'=>$user->account_no,
                    'ifsc_code'=>$user->ifsc_code,
                    'aadhar_number'=>$user->aadhar_number,
                    'pan_number'=>$user->pan_number,
                    'address'=>$user->address,
                    'aadhar_front_image'=>$user->aadhar_front_image,
                    'aadhar_back_image'=>$user->aadhar_back_image,
                    'pan_card_image'=>$user->pan_card_image,
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

    $validator = Validator::make($request->all(), [
        'name'         => 'required|string|max:255',
        'email'        => 'required|string|email|max:255',
        'phone'        => 'required|numeric|digits:10',
        'profile_image'=> 'nullable|image|mimes:jpg,png,gif|max:800',
        'bank_name'    => 'required|string|max:255',
        'account_no' => 'required|integer',
        'ifsc_code'    => 'required|string|max:20',
        'aadhar_number'=> 'nullable|string|size:12',
        'pan_number'   => 'nullable|string|size:10',
        'address'      => 'nullable|string|max:500',
        'aadhar_front_image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        'aadhar_back_image'  => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        'pan_card_image'     => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
    ]);

    if ($validator->fails()) {
        return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
    }

    $user->name = $request->name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->bank_name = $request->bank_name;
    $user->account_no = $request->account_no;
    $user->ifsc_code = $request->ifsc_code;
    $user->aadhar_number = $request->aadhar_number;
    $user->pan_number = $request->pan_number;
    $user->address = $request->address;

    if ($request->hasFile('profile_image')) {
        if ($user->profile_image) {
            Storage::delete($user->profile_image);
        }
        $path = $request->file('profile_image')->store('profile_images', 'public');
        $user->profile_image = $path;
    }

    // Handle Aadhar Front Image
    if ($request->hasFile('aadhar_front_image')) {
        if ($user->aadhar_front_image) {
            Storage::delete($user->aadhar_front_image);
        }
        $path = $request->file('aadhar_front_image')->store('kyc_documents', 'public');
        $user->aadhar_front_image = $path;
    }

    // Handle Aadhar Back Image
    if ($request->hasFile('aadhar_back_image')) {
        if ($user->aadhar_back_image) {
            Storage::delete($user->aadhar_back_image);
        }
        $path = $request->file('aadhar_back_image')->store('kyc_documents', 'public');
        $user->aadhar_back_image = $path;
    }

    // Handle PAN Card Image
    if ($request->hasFile('pan_card_image')) {
        if ($user->pan_card_image) {
            Storage::delete($user->pan_card_image);
        }
        $path = $request->file('pan_card_image')->store('kyc_documents', 'public');
        $user->pan_card_image = $path;
    }

    $user->save();

    return response()->json(['message' => 'Profile updated successfully', 'data' => $user]);
}


    public function register(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:20|unique:users,phone',
                'password' => 'required|string|min:6'
            ]);

            // Check if user already exists
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User with this email already exists'
                ], 422);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile_code = '91';
            $user->status = 'I';
            $user->phone = $request->phone;
            $user->password = $request->password;
            $user->is_admin = false;
            $user->total_balance = 0;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'data' => $user
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
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

    // Check if current password matches (handle both plain text and hashed passwords)
    $currentPassword = $request->get('current_password');
    $storedPassword = $user->password;
    
    // Check if stored password is hashed (starts with $2y$)
    $isStoredPasswordHashed = str_starts_with($storedPassword, '$2y$');
    
    $passwordMatches = false;
    
    if ($isStoredPasswordHashed) {
        // If stored password is hashed, use Hash::check
        $passwordMatches = Hash::check($currentPassword, $storedPassword);
    } else {
        // If stored password is plain text, do direct comparison
        $passwordMatches = ($currentPassword === $storedPassword);
    }
    
    if (!$passwordMatches) {
        return response()->json([
            'errors' => ['Your Current Password does not match with the password you provided.']
        ], 422);
    }

    if (strcmp($request->get('current_password'), $request->get('new_password')) == 0) {
        return response()->json([
            'errors' => ['New Password cannot be the same as your Current Password.']
        ], 422);
    }
    // Store password in plain text (not hashed)
    $user->password = $request->get('new_password');
    $user->save();
    return response()->json([
        'data' => [],
        'status' => 'success',
        'message' => 'Password changed successfully!'
    ], 200);
}

    
}
