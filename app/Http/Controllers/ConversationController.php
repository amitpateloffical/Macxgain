<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationType;
use App\Models\ConversationViaType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ActivityLog;
use Auth;
class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required',
                'author' => 'required',
                'conversation_type' => 'required',
                'via' => 'required',
            ]);
            if ($validator->fails()) {
                return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
            }
            $note = new Conversation();
            $note->message = $request->message;
            $note->added_by = $request->author;
            $note->added_for = $request->added_for;
            $note->added_for_id = $request->added_for_id;
            $note->conversation_type_id = $request->conversation_type;
            $note->conversation_via_id = $request->via;
            $note->added_at = Carbon::now();;
            $note->save();

            $id = $note->id;
            $conversationData = Conversation::getConversationInfo($id);
         
            $this->logActivity($conversationData,$id);

            DB::commit();
            return response(['data' => $note, 'message' => 'success'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response(['errors' => 'An error occurred while creating the task.'], 500);
        }
    }
    private function logActivity($systemData,$id) {
        $userId =  Auth::guard('api')->user()->id;
        $logEntry = new ActivityLog();
        $logEntry->log_name = 'Conversation';
        $logEntry->description = 'Created Conversation';
        $logEntry->subject_type = Conversation::class;
        $logEntry->subject_id = $id;
        $logEntry->causer_type = 'App\Models\User';
        $logEntry->causer_id = $userId;
        $logEntry->event = 'created';
        $logEntry->properties = json_encode(['attributes' => $systemData]);
        $logEntry->created_at =  Carbon::now();
        $logEntry->updated_at =  Carbon::now();
        $logEntry->save();
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $id = base64_decode($id);
        $note = Conversation::leftjoin('users', 'users.id', 'conversations.added_by')
        ->leftJoin('tickets', 'tickets.id', 'conversations.added_for_id')
        ->leftJoin('conversation_types', 'conversation_types.id', 'conversations.conversation_type_id')
        ->leftJoin('conversation_via_types', 'conversation_via_types.id', 'conversations.conversation_via_id')
        ->select('conversations.*','users.name as author','conversation_types.title as conversation_type','conversation_via_types.title as via',
            DB::raw("(CASE
            WHEN conversations.added_for = 'tickets' THEN tickets.ticket_name
            END) as related"),
            DB::raw("CONCAT(UPPER(LEFT(SUBSTRING_INDEX(users.name, ' ', 1), 1)), 
            UPPER(LEFT(SUBSTRING_INDEX(users.name, ' ', -1), 1))) as initials")
        )
        ->where('conversations.added_for_id',$id)
        ->orderBy('id', 'DESC')->get();
        return response(['data' => $note, 'status' => 'success'], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getconversationTypes(){

        $conversationtyps = ConversationType::select('title as label','id as value')->get();
        $viaOptions=ConversationViaType::select('title as label','id as value')->get();
        return response(['viaOptions'=>$viaOptions,'data' => $conversationtyps, 'status' => 'success'], 200);

    }
}
