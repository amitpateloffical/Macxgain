<?php

namespace App\Http\Controllers;
use App\Models\Tag;
use App\Models\ActivityLog;
use App\Models\LastloginReport;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TagController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $sortBy = $request->get('sortBy');
        $direction = $request->get('sortDesc') === 'true' ? 'DESC' : 'ASC';
        $validSortColumns = ['title','created_at', 'updated_at'];
        $query = Tag::query();
        if (!empty($request->title)) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        $query->whereNull('deleted_at');
        if ($sortBy) {
            $query->orderBy($sortBy, $direction);
        }else{
            $query->orderBy('id', 'DESC');
        }
        $tags = $query->paginate($perPage, ['*'], 'page', $page);
        return response([
            'data' => $tags->items(), 
            'total' => $tags->total(),
            'status' => 'success'
        ], 200);
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
                $tag = new Tag();
                $tag->title = $request->title;
                $tag->slug = $request->title;
                $tag->save();
                DB::commit();
                return response(['message' => 'Tag created successfully!', 'status' => 'success'], 200);
            }
            catch(\Exception $e){
                DB::rollback();
                return response(['message' => 'Tag created successfully!', 'status' => 'success'], 200);
            }
    }
    public function show($id){
        $id=base64_decode($id);
        $tag = Tag::where('id', $id)->first();
        return response(['data' => $tag], 200);
       
    }
    public function edit($id) {
        $id=base64_decode($id);
        $tag = Tag::findOrFail($id);
        return response(['data' => $tag, 200]);
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
            $tag = Tag::findOrFail($id);
            $tag->title = $request->title;
            $tag->slug = $request->title;
            $tag->save();
            DB::commit();
            return response(['status' => 'success', 'message' => 'Tag Updated Successfully!!'], 200);
        }
        catch(\Exception $e){
            DB::rollback();
            return response(['data'=>'Something Went Wrong','status'=>'error'],500);
        }
    }
    public function destroy($id) {
        $id=base64_decode($id);
        try {
            $tag=Tag::where('id', $id)->first();
            $tag->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
            $tag->save();
            return response()->json(['message' => 'Tag Deleted Successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something Went Wrong', 'status' => 'error'], 500);
        }
    }
    
    public function getTicketTeg(){
        $teg = Tag::where('status','A')->get();
        return response(['data' => $teg, 'status' => 'success'], 200);
 

    }
  
}
