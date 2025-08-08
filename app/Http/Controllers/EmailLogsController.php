<?php

namespace App\Http\Controllers;

use App\Models\EmailLogs;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use DB;

class EmailLogsController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $query = EmailLogs::query()
            ->when($request->email, function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->email . '%');
            })
            ->when($request->status, function ($query) use ($request) {
                if (is_array($request->status)) {
                    $query->whereIn('status', $request->status);
                } else {
                    $query->where('status', 'like', '%' . $request->status . '%');
                }
            });
            if ($request->sortBy) {
                $query->orderBy($request->sortBy, $request->sortDesc === 'true' ? 'DESC' : 'ASC');
            } else {
                $query->orderBy('email_logs.id', 'DESC');
            }
            $totalCount = $query->count();
            $data = $query->paginate($perPage, ['*'], 'page', $page);
            return response(['data' => $data->items(),'total_row_count' => $totalCount, 'status' => 'success'], 200);
    }
    public function getLoginLogs(Request $request)
    {
        $month = (int) $request->month;
        $sortField = $request->sortField ?? 'login_logs.id';
        $sortDirection = $request->sortDirection === 'asc' ? 'asc' : 'desc';

        $sortableFields = ['login_logs.id', 'login_logs.created_at', 'login_logs.user_id','login_logs.ip_address'];
        if (!in_array($sortField, $sortableFields)) {
            $sortField = 'login_logs.id';
        }
        $loginLogsQuery = LoginLog::leftJoin('users', 'users.id', '=', 'login_logs.user_id')
            ->select('login_logs.*', 'users.name as user_name');
        if (!empty($month)) {
            $loginLogsQuery->whereMonth('login_logs.created_at', $month);
        } else {
            $currentMonth = now()->month;
            $loginLogsQuery->whereMonth('login_logs.created_at', $currentMonth);
        }
        $totalCount = $loginLogsQuery->count();
        $loginLogs = $loginLogsQuery->orderBy($sortField, $sortDirection)
            ->paginate($request->perPage ?? 10);
        $response = [
            'data' => $loginLogs->items(), 
            'total_count' => $totalCount,
            'current_page' => $loginLogs->currentPage(),
            'last_page' => $loginLogs->lastPage(),
            'per_page' => $loginLogs->perPage(),
        ];
        return response()->json($response);
    }

}
