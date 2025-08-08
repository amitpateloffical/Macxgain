<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Ticket;
use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\Conversation;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $ticketQuery = Ticket::leftJoin('ticket_types', 'ticket_types.id', '=', 'tickets.ticket_type')
            ->leftJoin('customers as requester', 'requester.id', '=', 'tickets.requester_client_id')
            ->leftJoin('customers as assignee', 'assignee.id', '=', 'tickets.assignee_id')
            ->leftJoin('priorities', 'priorities.id', '=', 'tickets.priority')
            ->select('tickets.*', 
                     'ticket_types.title as ticket_type', 
                     'requester.name as requester', 
                     'priorities.title as priority', 
                     'assignee.name as assignee');
        if ($request->filled('ticket_name')) {
            $ticketQuery->where('tickets.ticket_name', 'like', '%' . $request->ticket_name . '%');
        }
        if ($request->filled('ticket_type')) {
            $ticketQuery->where('tickets.ticket_type', $request->ticket_type);
        }
        if ($request->filled('ticket_id')) {
            $ticketQuery->where('tickets.ticket_id', 'like', '%' .  $request->ticket_id . '%');
            // ->where('tickets.ticket_id', $request->ticket_id);
        }
        if ($request->filled('priority')) {
            $ticketQuery->where('tickets.priority', $request->priority);
        }
        if ($request->filled('customer')) {
        $customer = base64_decode($request->customer);
            $ticketQuery->where('tickets.requester_client_id', $customer);
        }
        if ($request->sortBy) {
            if ($request->sortBy === 'ticket_type') {
                $ticketQuery->orderBy('ticket_types.title', $request->sortDesc === 'true' ? 'DESC' : 'ASC');
            } elseif ($request->sortBy === 'priority') {
                $ticketQuery->orderBy('priorities.title', $request->sortDesc === 'true' ? 'DESC' : 'ASC');
            } elseif ($request->sortBy === 'requester') {
                $ticketQuery->orderBy('assignee.name', $request->sortDesc === 'true' ? 'DESC' : 'ASC');
            } else {
                $ticketQuery->orderBy('tickets.' . $request->sortBy, $request->sortDesc === 'true' ? 'DESC' : 'ASC');
            }
        }
         else {
            $ticketQuery->orderBy('tickets.id', 'DESC'); 
        }
        $data = $ticketQuery->paginate($perPage, ['*'], 'page', $page);
        $ticketCount = Ticket::count();
     
        return response(['data' => $data,'ticketCount' => $ticketCount, 'status' => 'success'], 200);
    }
    

    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_name' => 'required', 
            'priority' => 'required', 
            'ticket_type' => 'required', 
            'tags' => 'required', 
            'requester' => 'required', 
            'assignee' => 'required', 
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'code' => 422,
            ], 422);
        }
        DB::beginTransaction();
        try{

            $ticket = new Ticket();
            $ticket->ticket_name = $request->ticket_name;
            $ticket->priority = $request->priority;
            $ticket->ticket_type = $request->ticket_type;
            $ticket->requester_client_id = $request->requester;
            $ticket->assignee_id = $request->assignee;
            $ticket->message_from = $request->message_from;
            $ticket->message = $request->message;
            $ticket->tags = json_encode($request->tags);
            $ticket->followers = json_encode($request->followers);
            // $ticketCount = Ticket::count() + 1;
            $ticketCount = Ticket::withTrashed()->count() + 1;
         // $ticketCount = Ticket::orderBy('id', 'desc')->limit(1)->value(DB::raw('id + 1'));
            $ticket->ticket_id = '#TC' . $ticketCount;
            $ticket->save();
            $id = $ticket->id;
            $ticketData = Ticket::getTicketInfo($id);
   
            $this->logActivity($ticketData,$id);
            DB::commit();
            return response(['message' => 'Ticket  created successfully!', 'status' => 'success'], 200);
        }
        catch(\Exception $e){
            DB::rollback();
            dd($e);
            return response(['message' => 'something went wrong !', 'status' => false],500);
        }
    }
    public function show($id) {
        $id = base64_decode($id);
        $tickettype = Ticket::leftJoin('priorities', 'priorities.id', 'tickets.priority')
            ->leftJoin('ticket_types', 'ticket_types.id', 'tickets.ticket_type')
            ->leftJoin('users as assignee', 'assignee.id', 'tickets.assignee_id')
            // ->leftJoin('users as r_c', 'r_c.id', 'tickets.requester_client_id')
            ->leftJoin('customers', 'customers.id', 'tickets.requester_client_id')

            ->leftJoin('tags', function($join) {
                $join->whereRaw("JSON_CONTAINS(tickets.tags, CAST(tags.id AS JSON))");
            })
            ->leftJoin('users as followers', function($join) {
                $join->whereRaw("JSON_CONTAINS(tickets.followers, CAST(followers.id AS JSON))");
            })
            ->select(
                'tickets.*',
                'priorities.title as priority',
                'assignee.name as assignee_name',
                // 'r_c.name as request_client_name',
                'customers.name as request_client_name',

                'ticket_types.title as ticket_type',
                DB::raw('GROUP_CONCAT(DISTINCT tags.title SEPARATOR ", ") as tags'),
                DB::raw('GROUP_CONCAT(DISTINCT followers.name SEPARATOR ", ") as follower_names')
            )
            ->where('tickets.id', $id)
            ->groupBy('tickets.id') 
            ->first();
  
        return response(['data' => $tickettype], 200);
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $id = base64_decode($id);
        $ticket = Ticket::select('tickets.*','tickets.requester_client_id as requester','tickets.assignee_id as assignee')
        ->where('id',$id)->first();
        $ticket->followers = json_decode($ticket->followers);
        $ticket->tags = json_decode($ticket->tags);
        return response(['data' => $ticket, 'status' => 'success'], 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'ticket_name' => 'required', 
            'priority' => 'required', 
            'ticket_type' => 'required', 
            'tags' => 'required', 
            'requester' => 'required', 
            'assignee' => 'required', 
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
        }
        DB::beginTransaction();
        try{
      
            $oldData = Ticket::getTicketInfo($id);
            $ticket = Ticket::find($id);
            $ticket->ticket_name = $request->ticket_name;
            $ticket->priority = $request->priority;
            $ticket->ticket_type = $request->ticket_type;
            $ticket->requester_client_id = $request->requester;
            $ticket->message_from = $request->message_from;
            $ticket->message = $request->message;
            $ticket->assignee_id = $request->assignee;
            $ticket->tags = json_encode($request->tags);
            $ticket->followers = json_encode($request->followers);
            $ticket->save();
     
            $ticketid = $ticket->id;
         
            $newData = Ticket::getTicketInfo($ticketid);
 
            $this->logUpdated($oldData, $newData,  $ticketid);
            DB::commit();
            return response(['message' => 'Ticket  Updated successfully!', 'status' => 'success'], 200);
        }
        catch(\Exception $e){
            DB::rollback();
            return response(['message' => 'something went wrong !', 'status' => false],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ticket = Ticket::find(base64_decode($id));
        $ticket->delete();
        if (!$ticket) {
            return response()->json(['error' => true,'message'=>'Something went wrong'], 500);
        }
        return response(['message' => 'Deleted successfully','status' => true], 200);
    }

    public function getTicketActivity($id)
    {
        $ticketId = base64_decode($id); 
    
        $ticket = Ticket::leftJoin('users', 'users.id', '=', 'tickets.assignee_id')
            ->select('tickets.*', 'users.name as created_by_name')
            ->find($ticketId); 
        if (!$ticket) {
            return response(['message' => 'Ticket not found.', 'status' => 'error'], 404);
        }
        $ticketTasks = Task::leftJoin('users', 'users.id', '=', 'tasks.assignee')
            ->select('tasks.*', 'users.name as created_by')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($task) {
                return array_merge($task->toArray(), ['type' => 'task']);
            });
        $ticktesNotes = Note::leftJoin('users', 'users.id', '=', 'notes.added_by')
            ->select('notes.*', 'users.name as created_by')
            ->where('added_for_id', $ticketId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($note) {
                return array_merge($note->toArray(), ['type' => 'note']);
            });

        $ticktesConversations = Conversation::leftJoin('users', 'users.id', '=', 'conversations.added_by')
        ->select('conversations.*', 'users.name as created_by')
        ->where('added_for_id', $ticketId)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($conversation) {
            return array_merge($conversation->toArray(), ['type' => 'conversation']);
        });

        $ticketActivities = collect([
            [
                'activity' => 'Ticket Created',
                'id' => $ticket->id,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
                'created_by' => $ticket->created_by_name,
                'ticket_name' => $ticket->ticket_name,
                'type' => 'ticket_created',
                'event' => 'created',

            ]
        ]);
        $activities = collect()
            ->merge($ticketTasks)
            ->merge($ticktesNotes)
            ->merge($ticktesConversations)
            ->merge($ticketActivities)
            ->sortByDesc('created_at')
            ->values()
            ->all();      
        $additionalActivities =  ActivityLog::leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
        ->leftJoin('tickets', 'tickets.id', '=', 'activity_log.subject_id')
        ->select('tickets.*', 'activity_log.*', 'users.name as updated_by_name')
        ->where('activity_log.subject_id', $ticketId)
        ->where('activity_log.description', 'Updated Ticket')
        ->orderBy('activity_log.created_at', 'asc')
        ->get()
        ->map(function($log) {
            return [
                'activity' => 'Ticket Updated',
                'created_at' => $log->created_at,
                'created_by' => $log->updated_by_name,
                'ticket_name' => $log->ticket_name,
                'type' => 'ticket_updated',
                'event' => 'updated',
                'id' => $log->id,
            ];
        });
        $activities = collect($activities)->merge($additionalActivities)
            ->sortByDesc('created_at')
            ->values()
            ->all();
        if (collect($activities)->isEmpty()) {
            return response(['message' => 'No activities found for this ticket.', 'status' => 'success'], 200);
        }
    
        return response(['data' => $activities, 'status' => 'success'], 200);
    }
    public function getTicketOldNewActivity($activityId)
    {
        $ticketId = base64_decode($activityId); 
        $dataQuery = ActivityLog::leftJoin('users', 'users.id', 'activity_log.causer_id')
            ->where('activity_log.subject_id', $ticketId) 
            ->where('activity_log.subject_type', 'App\Models\Ticket')
            ->select('activity_log.*', 'users.name as user_name', 'users.email as user_email')
            ->get();
        return response(['data' => $dataQuery, 'status' => 'success'], 200);
    }

    private function logActivity($systemData,$id) {
        $userId =  Auth::guard('api')->user()->id;
        $logEntry = new ActivityLog();
        $logEntry->log_name = 'Ticket';
        $logEntry->description = 'Created Ticket';
        $logEntry->subject_type = Ticket::class;
        $logEntry->subject_id = $id;
        $logEntry->causer_type = 'App\Models\User';
        $logEntry->causer_id = $userId;
        $logEntry->event = 'created';
        $logEntry->properties = json_encode(['attributes' => $systemData]);
        $logEntry->created_at =  Carbon::now();
        $logEntry->updated_at =  Carbon::now();
        $logEntry->save();
    }

    private function logUpdated($oldData, $newData, $systemId)
    {
        $subjectId = $systemId;
        $keyOld = "old";
        $oldDataWithKey = [
        $keyOld => $oldData,
        ];
        $keyNew = "attributes";
        $newDataWithKey = [
        $keyNew => $newData,
        ];
        $bothDataWithKey = $newDataWithKey + $oldDataWithKey;
        $jsonData = json_encode($bothDataWithKey);
        $userId = Auth::guard('api')->user()->id;
        $system = new ActivityLog();
        $system->log_name = 'Ticket';
        $system->description = 'Updated Ticket';
        $system->subject_type = 'App\Models\Ticket';
        $system->subject_id = $subjectId;
        $system->causer_type = 'App\Models\User';
        $system->properties = $jsonData;
        $system->causer_id = $userId;
        $system->event = 'Updated';
        $system->created_at =  Carbon::now();
        $system->updated_at =  Carbon::now();
        $system->save();
    }
    
    
}
