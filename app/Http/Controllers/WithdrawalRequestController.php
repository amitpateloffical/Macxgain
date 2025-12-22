<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;


class WithdrawalRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['requester', 'recipient', 'approver']);

        if (!Auth::guard('api')->user()->is_admin) {
            $userId = Auth::guard('api')->user()->id;
            $query->where(function ($q) use ($userId) {
                $q->where('request_by', $userId)
                    ->orWhere('request_create_for', $userId);
            });
        }

        // if ($request->has('transaction_id')) {
        //     $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        // }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sortBy', 'created_at');
        $sortDesc = $request->get('sortDesc', true) ? 'desc' : 'asc';
        $query->orderBy($sortBy ?? 'id', $sortDesc);

        $perPage = $request->get('perPage', 10);
        $requests = $query->paginate($perPage);

        return response()->json([
            'data' => $requests->items(),
            'total' => $requests->total(),
            'message' => 'success'
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            // 'transaction_id' => 'required|string|unique:withdrawal_requests',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            // 'image' => 'required|image|max:2048',
        ]);

        // $imagePath = $request->file('image')->store('withdrawal_requests', 'public');

        $withdrawal = WithdrawalRequest::create([
            // 'transaction_id' => $request->transaction_id,
            'amount' => $request->amount,
            'description' => $request->description,
            // 'image_path' => $imagePath,
            'request_by' => Auth::guard('api')->user()->id,
            'request_create_for' => 1, // fixed recipient, can change if needed
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Withdrawal request submitted.', 'data' => $withdrawal], 201);
    }

    public function show($id)
    {
        $withdrawal = WithdrawalRequest::with(['requester', 'recipient', 'approver'])->findOrFail($id);
        $user = Auth::guard('api')->user();

        if (!$user->is_admin && $withdrawal->request_by != $user->id && $withdrawal->request_create_for != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $withdrawal]);
    }

    public function update(Request $request, $id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if (Auth::guard('api')->user()->id != $withdrawal->request_by) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            // 'transaction_id' => 'required|string|unique:withdrawal_requests,transaction_id,' . $id,
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            // 'image' => 'sometimes|image|max:2048',
        ]);

        $data = $request->only(['amount', 'description']);

        // if ($request->hasFile('image')) {
        //     Storage::disk('public')->delete($withdrawal->image_path);
        //     $data['image_path'] = $request->file('image')->store('withdrawal_requests', 'public');
        // }

        $withdrawal->update($data);

        return response()->json(['message' => 'Withdrawal updated.', 'data' => $withdrawal]);
    }

    public function destroy($id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if (Auth::guard('api')->user()->id != $withdrawal->request_by) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Storage::disk('public')->delete($withdrawal->image_path);
        $withdrawal->delete();

        return response()->json(['message' => 'Withdrawal deleted.']);
    }
    public function updateStatus(Request $request, $id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);

        if (!Auth::guard('api')->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reject_reason' => 'required_if:status,rejected|string|max:255',
        ]);

        // ✅ Check available balance first (excluding blocked trading amounts)
        $totalBalance = WalletTransaction::where('user_id', $withdrawal->request_by)
            ->orderBy('id', 'desc')
            ->value('running_balance') ?? 0;

        // Calculate blocked amount from active trades
        $blockedAmount = DB::table('ai_trading_orders')
            ->where('user_id', $withdrawal->request_by)
            ->where('status', 'COMPLETED') // Active trades
            ->sum('total_amount') ?? 0;

        // Available balance = Total balance - Blocked amount
        $availableBalance = $totalBalance - $blockedAmount;

        if ($request->status === 'approved' && $withdrawal->amount > $availableBalance) {
            return response()->json([
                'message' => 'Insufficient available balance. Available: ₹' . number_format($availableBalance, 2) . ' (Total: ₹' . number_format($totalBalance, 2) . ', Blocked: ₹' . number_format($blockedAmount, 2) . ')',
                'error' => true
            ], 400);
        }

        $withdrawal->status = $request->status;
        $withdrawal->approve_by = Auth::guard('api')->user()->id;
        $withdrawal->approve_date = now();

        if ($request->status === 'rejected') {
            $withdrawal->reject_reason = $request->reject_reason;
        } else {
            $withdrawal->reject_reason = null;
        }

        $withdrawal->save();

        if ($request->status === 'approved') {
            WalletTransaction::create([
                'user_id' => $withdrawal->request_by,
                'transaction_code' => strtoupper(Str::random(12)),
                'type' => 'debit',
                'amount' => $withdrawal->amount,
                'running_balance' => $totalBalance - $withdrawal->amount,
                'remark' => 'Withdrawal approved - ' . $withdrawal->id,
            ]);
        }

        return response()->json(['message' => 'Status updated successfully.']);
    }

    /**
     * Get user's withdrawal balance information (always returns balance data)
     */
    public function getWithdrawalBalance(Request $request)
    {
        $login = Auth::guard('api')->user()->id;
        $user = User::find($login);

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        // Get total balance from wallet transactions
        $totalBalance = DB::table('wallet_transactions')
            ->where('user_id', $login)
            ->orderBy('created_at', 'desc')
            ->value('running_balance') ?? 0;

        // Calculate blocked amount from active trades
        $blockedAmount = DB::table('ai_trading_orders')
            ->where('user_id', $login)
            ->where('status', 'COMPLETED') // Active trades
            ->sum('total_amount') ?? 0;

        // Available balance = Total balance - Blocked amount
        $availableBalance = $totalBalance - $blockedAmount;

        // Check bank details status
        $hasBankDetails = !empty($user->bank_name) && !empty($user->account_no) && !empty($user->ifsc_code);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_balance' => $totalBalance,
                'blocked_amount' => $blockedAmount,
                'available_balance' => $availableBalance,
                'has_bank_details' => $hasBankDetails,
                'min_withdraw_amount' => 100,
            ]
        ], 200);
    }

    public function checkBankInfo(Request $request)
    {
        $login = Auth::guard('api')->user()->id;
        $user = User::find($login);

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if (empty($user->bank_name) || empty($user->account_no) || empty($user->ifsc_code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bank details are incomplete. Please update your bank information.'
            ], 200);
        }

        // Get total balance from wallet transactions
        $totalBalance = \DB::table('wallet_transactions')
            ->where('user_id', $login)
            ->orderBy('created_at', 'desc')
            ->value('running_balance') ?? 0;

        // Calculate blocked amount from active trades
        $blockedAmount = \DB::table('ai_trading_orders')
            ->where('user_id', $login)
            ->where('status', 'COMPLETED') // Active trades
            ->sum('total_amount') ?? 0;

        // Available balance = Total balance - Blocked amount
        $availableBalance = $totalBalance - $blockedAmount;

        if ($availableBalance < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient available balance. Minimum balance of ₹1 is required for withdrawal.',
                'data' => [
                    'total_balance' => $totalBalance,
                    'blocked_amount' => $blockedAmount,
                    'available_balance' => $availableBalance,
                    'min_withdraw_amount' => 1,
                ]
            ], 200);
        }

        // ✅ All good - ready to withdraw
        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal is available.',
            'data' => [
                'user' => $user,
                'total_balance' => $totalBalance,
                'blocked_amount' => $blockedAmount,
                'available_balance' => $availableBalance,
                'balance' => $availableBalance, // For backward compatibility
                'min_withdraw_amount' => 100,
            ]
        ], 200);
    }
}
