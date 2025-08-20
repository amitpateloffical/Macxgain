<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get current authenticated user info
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo()
    {
        $user = Auth::guard('api')->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
        ]);
    }

    /**
     * Display a listing of users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = User::select([
                'id', 'name', 'email', 'phone', 'is_admin', 'status',
                'created_at', 'last_login_at', 'bank_name', 'account_no', 'ifsc_code'
            ])->get();

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Users retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:6|confirmed',
                'is_admin' => 'boolean',
                'status' => 'required|in:A,I,D',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_admin' => $request->is_admin ? 1 : 0,
                'status' => 'I', // Set new users as inactive by default
                'total_balance' => 0
            ];

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/profile_images', $imageName);
                $userData['profile_image'] = 'storage/profile_images/' . $imageName;
            }

            $user = User::create($userData);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Update the specified user
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
                'phone' => 'nullable|string|max:20',
                'is_admin' => 'boolean',
                'status' => 'required|in:A,I,D', // Add status validation
                'password' => 'nullable|string|min:6|confirmed'
            ]);

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'is_admin' => $request->is_admin ? 1 : 0,
                'status' => $request->status // Add status to update data
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive,suspended'
            ]);

            $user = User::findOrFail($id);
            $user->update(['status' => $request->status]);
            
            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function total_b(Request $request)
    {
        $id = $request->input('id');
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'total_balance' => $user->total_balance
        ]);
    }


}