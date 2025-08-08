<?php

namespace App\Http\Controllers;
use App\Jobs\SendEmailJob;
use App\Mail\AddTaskReminder;
use App\Models\Task;
use App\Models\User;
use App\Models\Priority;
use App\Models\TicketType;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
use Helper;
use Carbon\Carbon;
use App\Http\Controllers\Tenant\SendPushNotificationController;
use Illuminate\Pagination\Paginator;
use Log;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ActivityLog;
class TaskController extends Controller
{
    public function gettask($id){
        $id=base64_decode($id);
    $task = Task::leftjoin('users','users.id','tasks.assignee')
    ->select('tasks.*','users.name as assignee_name')->where('tasks.ticket_id',$id)->orderBy('tasks.id','DESC')->get();
    return response(['data'=>$task ,'message'=>'sucess',200]);   
    }
    
    public function getpriority(){
    $priority = Priority::select('id as value','title as label')->get();
    return response(['data'=>$priority ,'message'=>'sucess',200]);   
    }
    public function getassignee(){
    $getassignee = User::select('id as value','name as label')->get();
    return response(['data'=>$getassignee ,'message'=>'sucess',200]);   
    }
    public function gettickettype(){
        $tickettypes = TicketType::select('id as value','title as label')->get();
        return response(['data'=>$tickettypes ,'message'=>'sucess',200]);   
        
        }
        public function store(Request $request)
        {
            DB::beginTransaction();
            try {
                $validator = Validator::make($request->all(), [
                    'title' => 'required|string|max:255',
                    'date' => 'required',
                    'time' => 'required',
                    'priority' => 'required|max:50',
                    'assignee' => 'required',
                    'reporter' => 'required',
                    'description' => 'required|string|max:255',
                ], [
                    'date.required' => 'The start date field is required.',  
                ]);
                
                if ($validator->fails()) {
                    return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
                }
                $ticket_id=base64_decode($request->ticket_id);
                $tasks = $request->task;
                $task = new Task;
                $task->title = ucwords($request->title);
                $task->priorities =$request->priority;
                $task->date = $request->date;
                $task->time =$request->time;
                $task->reporter = $request->reporter;
                $task->assignee = $request->assignee;
                $task->description = $request->description;
                $task->ticket_id =$ticket_id;
                $task->status ='open';
                $task->save();
                DB::commit();
                return response(['data' => $task, 'message' => 'success'], 200);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response(['errors' => 'An error occurred while creating the task.'], 500);
            }
        }
}
