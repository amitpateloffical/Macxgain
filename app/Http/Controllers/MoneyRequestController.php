<?php
namespace App\Http\Controllers;

use App\Models\MoneyRequest;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\WalletTransaction;



class MoneyRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'image' => 'required|image|max:2048',
            // 'request_create_for' => 'required|exists:users,id',
        ]);

        $imagePath = $request->file('image')->store('money_requests', 'public');

        $moneyRequest = MoneyRequest::create([
            'amount' => $request->amount,
            'image_path' => $imagePath,
            'request_by' => Auth::guard('api')->user()->id,
            'request_create_for' => 1,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Request submitted successfully.', 'data' => $moneyRequest], 201);
    }

    public function index(Request $request)
    {
        $query = MoneyRequest::with(['requester', 'recipient']);
        
        // If not admin, only show requests created by or for the current user
        if (!Auth::guard('api')->user()->is_admin) {
            $userId = Auth::guard('api')->user()->id;
            $query->where(function($q) use ($userId) {
                $q->where('request_by', $userId)
                  ->orWhere('request_create_for', $userId);
            });
        }



        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('recipient')) {
            $query->where('request_create_for', $request->recipient);
        }

        // if ($request->has('from_date')) {
        //     $query->whereDate('created_at', '>=', $request->from_date);
        // }

        // if ($request->has('to_date')) {
        //     $query->whereDate('created_at', '<=', $request->to_date);
        // }

        // Sorting
        $sortBy = $request->get('sortBy');
        $sortBy = (!empty($sortBy)) ? $sortBy : 'created_at';

        $sortDesc = $request->get('sortDesc', true) ? 'desc' : 'asc';
        $query->orderBy($sortBy??'id', $sortDesc);


        // Pagination
        $perPage = $request->get('perPage', 10);
        $requests = $query->paginate($perPage);

        return response([
            'data' => $requests->items(),
            'total' => $requests->total(),
            'message' => 'success'
        ], 200);
    }

    public function show($id)
    {
        $request = MoneyRequest::with(['requester', 'recipient'])->findOrFail($id);
        
        // Authorization - user can only see their own requests unless admin
        $user = Auth::guard('api')->user();
        if (!$user->is_admin && $request->request_by != $user->id && $request->request_create_for != $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $request]);
    }

    public function update(Request $request, $id)
    {
        $moneyRequest = MoneyRequest::find($id);
        
        // Only creator can update their own request
        if (Auth::guard('api')->user()->id != $moneyRequest->request_by) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'image' => 'sometimes|image|max:2048',
        ]);

        $data = $request->only(['amount']);
        
        if ($request->hasFile('image')) {
            // Delete old image
            Storage::disk('public')->delete($moneyRequest->image_path);
            $data['image_path'] = $request->file('image')->store('money_requests', 'public');
        }

        $moneyRequest->update($data);

        return response()->json(['message' => 'Request updated successfully.', 'data' => $moneyRequest]);
    }

    public function destroy($id)
    {
        $moneyRequest = MoneyRequest::findOrFail($id);
        
        // Only creator can delete their own request
        if (Auth::guard('api')->user()->id != $moneyRequest->request_by) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete associated image
        Storage::disk('public')->delete($moneyRequest->image_path);
        
        $moneyRequest->delete();

        return response()->json(['message' => 'Request deleted successfully.']);
    }

   public function updateStatus(Request $request, $id)
{
    $moneyRequest = MoneyRequest::find($id);

    // Only admin can update status
    if (!Auth::guard('api')->user()->is_admin) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate([
        'status' => 'required|in:approved,rejected',
        'reject_reason' => 'required_if:status,rejected|string|max:255',
    ]);

    $moneyRequest->status = $request->status;
    
    if ($request->status === 'rejected') {
        $moneyRequest->raject_reason = $request->reject_reason;
    } else {
        $moneyRequest->raject_reason = 'null';
    }

    $moneyRequest->save();

    // ✅ If approved, push into wallet_transactions
    if ($request->status === 'approved') {
        $lastBalance = WalletTransaction::where('user_id', $moneyRequest->request_by)
                        ->orderBy('id', 'desc')
                        ->value('running_balance') ?? 0;

        WalletTransaction::create([
            'user_id'          => $moneyRequest->request_by,
            'transaction_code' => strtoupper(Str::random(12)),
            'type'             => 'credit',
            'amount'           => $moneyRequest->amount,
            'running_balance'  => $lastBalance + $moneyRequest->amount,
            'remark'           => 'Money request approved - ID: ' . $moneyRequest->id,
        ]);
    }

    return response()->json(['message' => 'Status updated.']);
}

}