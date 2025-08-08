<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      
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
            ]);
            if ($validator->fails()) {
                return response(['errors' => $validator->errors()->messages(), 'code' => 422], 422);
            }
            $note = new Note();
            $note->note = $request->message;
            $note->added_by = $request->author;
            $note->added_for = $request->note_for;
            $note->added_for_id = $request->note_for_id;
            $note->added_at = Carbon::now();;
            $note->save();
            DB::commit();
            return response(['data' => $note, 'message' => 'success'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response(['errors' => 'An error occurred while creating the task.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {   
        $id = base64_decode($id);
        $note = Note::leftjoin('customers', 'customers.id', 'notes.added_by')
        ->leftJoin('tickets', 'tickets.id', 'notes.added_for_id')
        // ->leftJoin('customers', 'customers.id', 'notes.added_for_id')
        ->select('notes.*','customers.name as author',
            DB::raw("(CASE
            WHEN notes.added_for = 'tickets' THEN tickets.ticket_name
            WHEN notes.added_for = 'customers' THEN customers.name
            END) as related"),
            DB::raw("CONCAT(UPPER(LEFT(SUBSTRING_INDEX(customers.name, ' ', 1), 1)), 
            UPPER(LEFT(SUBSTRING_INDEX(customers.name, ' ', -1), 1))) as initials")
        )
        ->where('notes.added_for_id',$id)
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
}
