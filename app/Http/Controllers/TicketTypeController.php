<?php

namespace App\Http\Controllers;
use App\Models\TicketType;
use App\Models\ActivityLog;
use App\Models\LastloginReport;
use App\Models\Priority;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TicketTypeController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $sortBy = $request->get('sortBy'); 
        $direction = $request->get('sortDesc') === 'true' ? 'DESC' : 'ASC'; 
         $query = TicketType::query();
        if (!empty($request->title)) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($sortBy) {
            $query->orderBy($sortBy, $direction);
        }else{
            $query->orderBy('id', 'DESC');
        }
        $tickettype = $query->whereNull('deleted_at')->paginate($perPage, ['*'], 'page', $page);
       return response(['data' => $tickettype, 'status' => 'success'], 200);
    }
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required', 
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
        }
            DB::beginTransaction();
            try{
                $tickettype = new TicketType();
                $tickettype->title = $request->title;
                $tickettype->slug = $request->title;
                $tickettype->save();
                DB::commit();
                return response(['message' => 'Ticket Type created successfully!', 'status' => 'success'], 200);
            }
            catch(\Exception $e){
                DB::rollback();
                return response(['message' => 'Ticket Type created successfully!', 'status' => 'success'], 200);
            }
    }
    public function show($id){
        $id=base64_decode($id);
        $tickettype = TicketType::where('id', $id)->first();
        return response(['data' => $tickettype], 200);
       
    }
    public function edit($id) {
        $id=base64_decode($id);
        $tickettype = TicketType::findOrFail($id);
        return response(['data' => $tickettype, 200]);
    }
    public function update($id, Request $request) {
        $id=base64_decode($id);
        $validator = Validator::make($request->all(), [
            'title' => 'required', 
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
        }
        DB::beginTransaction();
        try{
            $tickettype = TicketType::findOrFail($id);
            $tickettype->title = $request->title;
            $tickettype->slug = $request->title;
            $tickettype->save();
            DB::commit();
            return response(['status' => 'success', 'message' => 'Ticket Type Updated Successfully!!'], 200);

        }          
        catch(\Exception $e){
            DB::rollback();
            return response(['data'=>'Something Went Wrong','status'=>'error'],500);
        }
    }
    public function destroy($id) {
        $id=base64_decode($id);
        try {
            $tickettype=TicketType::where('id', $id)->first();
            $tickettype->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
            $tickettype->save();
            return response()->json(['message' => 'Ticket Type Deleted Successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something Went Wrong', 'status' => 'error'], 500);
        }
    }
    public function getTicketType(){
        $TicketType = TicketType::where('status','A')->get();
        $priorityOptions = Priority::select('title as label','id as value')->get();
        $priorityOptions = $priorityOptions->map(function ($priorityOption) {
            if ($priorityOption->label == 'High') {
                $priorityOption->variant = 'danger';
            } elseif ($priorityOption->label == 'Medium') {
                $priorityOption->variant = 'warning';
            } elseif ($priorityOption->label == 'Low') {
                $priorityOption->variant = 'success';
            }
            return $priorityOption;
        });
        return response(['data' => $TicketType, 'priorityOptions'=>$priorityOptions ,'status' => 'success'], 200);
 

    }
}
