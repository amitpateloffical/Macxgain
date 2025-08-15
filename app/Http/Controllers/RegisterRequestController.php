<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegisterRequestController extends Controller
{
    /**
     * Display a listing of registration requests
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Get all users with their registration status, sorted by latest first
            $requests = User::select([
                'id', 'name', 'email', 'phone', 'profile_image', 'status',
                'created_at', 'updated_at'
            ])->orderBy('created_at', 'desc')->get();

            // Transform data to match frontend expectations
            $formattedRequests = $requests->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_image' => $user->profile_image,
                    'status' => $this->mapStatusToFrontend($user->status),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedRequests,
                'message' => 'Registration requests retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve registration requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a registration request
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Update user status to active
            $user->update([
                'status' => 'A', // Active
                'updated_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User registration approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a registration request
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Update user status to inactive (rejected)
            $user->update([
                'status' => 'I', // Inactive
                'updated_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User registration rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map database status to frontend status
     *
     * @param string $dbStatus
     * @return string
     */
    private function mapStatusToFrontend($dbStatus)
    {
        $statusMap = [
            'A' => 'approved',
            'I' => 'pending', // Inactive users are considered pending for approval
            'D' => 'rejected'
        ];
        
        return $statusMap[$dbStatus] ?? 'pending';
    }
}
