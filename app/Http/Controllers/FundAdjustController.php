<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FundAdjustController extends Controller
{
    /**
     * Search users for fund adjustment
     */
    public function searchUsers(Request $request)
    {
        try {
            $query = $request->get('q');
            
            if (empty($query)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required'
                ], 400);
            }

            $users = User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('id', 'like', "%{$query}%")
                ->select(['id', 'name', 'email', 'phone', 'profile_image', 'total_balance'])
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Users found successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply fund adjustment to user
     */
    public function adjustFund(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'type' => 'required|in:add,subtract,set',
                'amount' => 'required|numeric|min:0.01',
                'reason' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($request->user_id);
            $currentBalance = $user->total_balance ?? 0;
            $adjustmentAmount = $request->amount;
            $adjustmentType = $request->type;
            $reason = $request->reason ?? 'Balance adjustment by admin';

            // Calculate new balance based on adjustment type
            $newBalance = $currentBalance;
            switch ($adjustmentType) {
                case 'add':
                    $newBalance = $currentBalance + $adjustmentAmount;
                    break;
                case 'subtract':
                    $newBalance = max(0, $currentBalance - $adjustmentAmount);
                    break;
                case 'set':
                    $newBalance = $adjustmentAmount;
                    break;
            }

            // Create wallet transaction record
            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'transaction_code' => 'ADJ_' . time() . '_' . $user->id,
                'type' => 'admin_adjustment',
                'amount' => $adjustmentAmount,
                'running_balance' => $newBalance,
                'description' => "Admin Fund Adjustment - {$adjustmentType}: {$reason}",
                'status' => 'completed',
                'admin_id' => Auth::id(),
                'adjustment_type' => $adjustmentType,
                'adjustment_reason' => $reason
            ]);

            // Update user's total balance
            $user->update(['total_balance' => $newBalance]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'previous_balance' => $currentBalance,
                    'new_balance' => $newBalance,
                    'adjustment_amount' => $adjustmentAmount,
                    'adjustment_type' => $adjustmentType,
                    'transaction_id' => $transaction->id
                ],
                'message' => 'Fund adjustment applied successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply fund adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent fund adjustments
     */
    public function getRecentAdjustments(Request $request)
    {
        try {
            $adjustments = WalletTransaction::where('type', 'admin_adjustment')
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'user_id' => $transaction->user_id,
                        'user_name' => $transaction->user->name ?? 'Unknown User',
                        'user' => $transaction->user ? [
                            'id' => $transaction->user->id,
                            'name' => $transaction->user->name,
                            'profile_photo' => null // No profile_photo column exists
                        ] : null,
                        'adjustment_type' => $transaction->adjustment_type,
                        'amount' => $transaction->amount,
                        'running_balance' => $transaction->running_balance,
                        'description' => $transaction->description,
                        'adjustment_reason' => $transaction->adjustment_reason,
                        'created_at' => $transaction->created_at,
                        'admin_id' => $transaction->admin_id
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $adjustments,
                'message' => 'Recent adjustments retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent adjustments: ' . $e->getMessage()
            ], 500);
        }
    }
}
