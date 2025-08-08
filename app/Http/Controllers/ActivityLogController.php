<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Role;
use App\Models\System;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Log;
use Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $dataQuery = ActivityLog::leftJoin('users', 'users.id', 'activity_log.causer_id')
            ->when($request->name, function ($q) use ($request) {
                $q->where('activity_log.causer_id', $request->name);
            })
            ->when($request->description, function ($q) use ($request) {
                $q->where('activity_log.properties', 'LIKE', "%{$request->description}%")
                    ->orWhere('activity_log.description', 'LIKE', '%' . $request->description . '%');
            })
            ->when($request->log_name, function ($q) use ($request) {
                $q->where('activity_log.log_name', $request->log_name);
            })
            ->select([
                'activity_log.id',
                'activity_log.log_name',
                'activity_log.description',
                'activity_log.subject_type',
                'activity_log.event',
                'activity_log.subject_id',
                'activity_log.causer_type',
                'activity_log.causer_id',
                'activity_log.properties',
                'activity_log.batch_uuid',
                'activity_log.created_at as created',
                'activity_log.updated_at',
                'users.name',
                'users.email',
                'users.profile_image',
                DB::raw("CONCAT('" . config('global.userPath') . "', users.id, '/profileimages/original/', users.profile_image) AS profile_image")
            ]);
        if ($request->sortBy) {
            $dataQuery->orderBy($request->sortBy, $request->sortDesc === 'true' ? 'DESC' : 'ASC');
        } else {
            $dataQuery->orderBy('activity_log.created_at', 'DESC');
        }
        $totalCount = $dataQuery->count();
        $data = $dataQuery->paginate($perPage, ['*'], 'page', $page);
        return response(['data' => $data, 'total' => $totalCount, 'status' => 'success'], 200);
    }
    public function getuser(Request $request) {
        $alluser = User::select('id as value','name as label'
        )->get(); 
        return response()->json([
            'alluser' => $alluser
        ]);
    }
    public function getlogname(Request $request) {
        $logname = ActivityLog::select('log_name')->distinct()->orderBy('log_name')->get();
        return response()->json([
            'logname' => $logname,
            'status'  =>true
        ]);
    }
}
