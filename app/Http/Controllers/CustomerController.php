<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; 
use App\Imports\CustomersImport; 
use App\Models\Customer;
use App\Models\CustomerAttachment;
use File;
use Str;
use Intervention\Image\Facades\Image;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\User;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\ActivityLog;
use App\Models\Note;
use DB;
use Log;
use Auth;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // public function uploadCustomers(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,xls',
    //     ]);
    //     $path = $request->file('file')->store('temp');
    //     try {
    //         // Excel::import(new CustomersImport, storage_path('app/' . $path));
    //         $rows = Excel::toArray(new CustomersImport(), storage_path('app/' . $path));
    //         dd($rows);
    //         Excel::import(new CustomersImport, storage_path('app/' . $path));
    //         return response()->json(['message' => 'File uploaded and customers imported successfully'], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Error processing file: ' . $e->getMessage()], 500);
    //     }
    // }

  
    public function uploadCustomers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        $path = $request->file('file')->store('temp');
        try {
        
            $rows = Excel::toArray(new CustomersImport(), storage_path('app/' . $path));
 
            $errors = [];
            foreach ($rows[0] as $key => $row) {
                $validator = Validator::make(
                    ['name' => $row[1]],
                    ['name' => 'required|string|min:3']
                );
                
                if ($validator->fails()) {
                    $errors[] = "The Name Field Is Required" . " [row : ".($key+1)."]";//phpcs:ignore
                }
 
            }
            if (count($errors) > 0) {
                $logs = '';
                foreach ($errors as $err) {
                    $logs .= $err . PHP_EOL;
                }
                return response(['errors' => $logs, 'code' => 422], 422);
            }
            // if (!empty($errors)) {
            //     return response()->json([
            //         'message' => 'Validation errors occurred.',
            //         'errors' => $errors,
            //         'status' => 422,
            //     ], 400);
            // }
 
            Excel::import(new CustomersImport, storage_path('app/' . $path));
 
            return response()->json(['message' => 'File uploaded and customers imported successfully'], 200);
 
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing file: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ]);
            $locationParts = explode(',', $request->location);
            $location = isset($locationParts[0]) ? trim($locationParts[0]) : '';
            $city = isset($locationParts[1]) ? trim($locationParts[1]) : '';
            $state = isset($locationParts[2]) ? trim($locationParts[2]) : '';
            $country = isset($locationParts[3]) ? trim($locationParts[3]) : '';
            if (!$city || !$state || !$country) {
                $locationParts = array_reverse($locationParts);
                $location = isset($locationParts[0]) ? trim($locationParts[0]) : '';
                $city = isset($locationParts[1]) ? trim($locationParts[1]) : '';
            $state = isset($locationParts[2]) ? trim($locationParts[2]) : '';
            $country = isset($locationParts[3]) ? trim($locationParts[3]) : '';
            }
            $countryId = Country::where('country_name', 'like', "%$country%")
            ->select('id')
            ->first();
            $countryId = $countryId ? $countryId->id : null;
            $stateId = State::where('name', 'like', $state)->select('id')->first();
            $stateId = $stateId ? $stateId->id : null;
            $cityId = City::where('name', 'like', $city)->select('id')->first();
            $cityId = $cityId ? $cityId->id : null;
            $customer = new Customer();
            $customer->name = $request->name;
            $customer->location = $location;
            $customer->country_id = $countryId;
            $customer->state_id = $stateId;
            $customer->city_id = $cityId;       
            $customer->size = $request->size;       
            $customer->customer_type = $request->customer_type;       
            $customer->primary_contact_name = $request->primary_contact_name;
            $customer->primary_contact_email = $request->primary_contact_email;
            $customer->primary_contact_phone = $request->primary_contact_phone;
            $customer->secondary_contact_name = $request->secondary_contact_name;
            $customer->secondary_contact_email = $request->secondary_contact_email;
            $customer->secondary_contact_phone = $request->secondary_contact_phone;
            $customer->key_challenges = $request->key_challenges;
            $customer->important_considerations = $request->important_considerations;
            $customer->specific_requirements = $request->specific_requirements;
            $customer->potential_risks = $request->potential_risks;
            $customer->contactperson = $request->primary_contact_name;
            $customer->email = $request->primary_contact_email;
            $customer->contactnumber = $request->primary_contact_phone;
            $customer->assignedTo = 1;
            $customer->engagement = 'Medium';
            $customer->product_features_promised = $request->product_features_promised;
            $customer->service_level_aggriment = $request->service_level_aggriment;
            $customer->discounts = $request->discounts;
            $customer->general_notes = $request->general_notes;
            $customerCount = Customer::withTrashed()->count() + 1;
            $customer->customer_id = '#CI' . $customerCount;
            if ($customer->save()) {
                if ($request->hasFile('files')) {
                    $uploadedFiles = $request->file('files');
                    foreach ($uploadedFiles as $file) {
                        $filePath = $file->store(
                            'customer_attachments/' . $customer->id,
                            'public'
                        );
                        $customerAttachment = new CustomerAttachment();
                        $customerAttachment->customer_id = $customer->id;
                        $customerAttachment->file = $filePath;
                        $customerAttachment->save();
                    }
                }
                $id = $customer->id;
                $customerData = Customer::getCustomerInfo($id);
                $this->logActivity($customerData,$id);
                DB::commit();
                return response(['message' => 'Customer created successfully!', 'status' => 'success'], 200);
            } else {
                throw new \Exception('Failed to save customer.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'An error occurred: ' . $e->getMessage(), 'status' => 'error'], 500);
        }
    }
    
    
    public function getCustomer(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);
        $customer = Customer::leftjoin('users', 'users.id', 'customers.assignedto')
            ->when($request->customer_id != '', function ($query) use ($request) {
                $query->where('customers.customer_id', 'like', '%' .  $request->customer_id . '%');
            })
            ->when($request->name != '', function ($query) use ($request) {
                $query->where('customers.name', 'like', '%' .  $request->name . '%');
            })
            ->when($request->contactperson != '', function ($query) use ($request) {
                $query->where('customers.contactperson', 'like', '%' .  $request->contactperson . '%');
            })
            ->when($request->email != '', function ($query) use ($request) {
                $query->where('customers.email','like', '%' . $request->email . '%');
            })
            ->select('customers.*','users.name as assigned_to');
        if ($request->sortBy) {
            $customer->orderBy($request->sortBy, $request->sortDesc === 'true' ? 'DESC' : 'ASC');
        } else {
            $customer->orderBy('customers.id', 'DESC');
        }
        $totalCount = $customer->count();
        $data = $customer->paginate($perPage, ['*'], 'page', $page);
        $totalPages = ceil($totalCount / $perPage);
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $data = $customer->paginate($perPage, ['*'], 'page', $page); // Re-fetch the data for the last valid page
        }
    
        return response([
            'data' => $data,
            'total' => $totalCount,
            'status' => 'success'
        ], 200);
    }
    
    public function addCustomers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required', 
            'contactperson' => 'required', 
            // 'email' => 'required', 
           'email' => 'required|email|unique:customers,email',
            'contactnumber' => 'required', 
            'assignedTo' => 'required', 
            'engagement' => 'required', 
           
            
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'code' => 422,
            ], 422);
        }
        
        DB::beginTransaction();
        try{
            $data = new Customer();
            $data->name = $request->name;
            $data->contactperson = $request->contactperson;
            $data->email = $request->email;
            $data->contactnumber = $request->contactnumber;
            $data->assignedTo = $request->assignedTo;
            $data->engagement = $request->engagement;
            $customerCount = Customer::withTrashed()->count() + 1;
            //  $customerCount = Customer::orderBy('id', 'desc')->limit(1)->value(DB::raw('id + 1'));
            $data->customer_id = '#CI' . $customerCount;
            $data->save(); 
            $id = $data->id;
            $customerData = Customer::getCustomerInfo($id);
            $this->logActivity($customerData,$id);
            DB::commit();
            return response(['message' => 'Customer  created successfully!', 'status' => 'success'], 200);
        }
        catch(\Exception $e){
            DB::rollback();
            dd($e);
            return response(['message' => 'something went wrong !', 'status' => false],500);
        }
    }
    public function show($id) {
        $id = base64_decode($id);
        $tickettype = Customer::
        leftJoin('tickets', 'tickets.requester_client_id', 'customers.id')
        ->leftJoin('priorities', 'priorities.id', 'tickets.priority')
        ->leftjoin('users as assignee', 'assignee.id', 'tickets.assignee_id')
            ->select(
                'customers.*',
                'customers.id as cus_id',

                'tickets.*',
                'tickets.created_at as request_date',
                'priorities.title as priority',
                'assignee.name as assignee_name',
            )
            ->where('customers.id', $id)
            ->first();
    
        return response(['data' => $tickettype], 200);
    }
    public function edit($id) {
        $id = base64_decode($id);
    
        $customer = Customer::leftJoin('tickets', 'tickets.requester_client_id', 'customers.id')
            ->leftJoin('priorities', 'priorities.id', 'tickets.priority')
            ->leftJoin('users as assignee', 'assignee.id', 'tickets.assignee_id')
            ->select(
                'customers.*',
                'customers.id as cus_id',
                'tickets.*',
                'tickets.created_at as request_date',
                'priorities.title as priority',
                'assignee.name as assignee_name'
            )
            ->where('customers.id', $id)
            ->first(); 
    
        if (!$customer) {
            return response(['message' => 'Customer not found'], 404);
        }
        if ($customer->assignedto && !User::find($customer->assignedto)) {
            $customer->assignedto = null;
        }
        return response(['data' => $customer], 200);
    }
   

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required', 
            'contactperson' => 'required', 
            "email" => "required|unique:customers,email," . base64_decode($id),
            'contactnumber' => 'required', 
            'assignedTo' => 'required', 
            'engagement' => 'required', 
           
            
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'code' => 422,
            ], 422);
        }
        DB::beginTransaction();
        try{
            $id = base64_decode($id);
            $oldData = Customer::getCustomerInfo($id);
            $customer = Customer::find($id);
            $customer->name = $request->name;
            $customer->contactperson = $request->contactperson;
            $customer->email = $request->email;
            $customer->contactnumber = $request->contactnumber;
            $customer->assignedTo = $request->assignedTo;
            $customer->engagement = $request->engagement;
            $customer->save();
            $customerid = $customer->id;
            $newData = Customer::getCustomerInfo($customerid);
            $this->logUpdated($oldData, $newData, $customerid);
            DB::commit();
            return response(['message' => 'Customer  Updated successfully!', 'status' => 'success'], 200);
        }
        catch(\Exception $e){
            DB::rollback();
            return response(['message' => 'something went wrong !', 'status' => false],500);
        }
    }
    public function destroy(string $id)
    {
        $customers = Customer::find(base64_decode($id));
        $customers->delete();
        if (!$customers) {
            return response()->json(['error' => true,'message'=>'Something went wrong'], 500);
        }
        return response(['message' => 'Deleted successfully','status' => true], 200);
    }

    public function getCustomerTask($id)
    {
        $id = base64_decode($id);
        $tasks = Task::leftJoin('tickets', 'tickets.id', '=', 'tasks.ticket_id')
            ->leftJoin('customers', 'customers.id', '=', 'tickets.requester_client_id')
            ->select('tasks.*')
            ->where('customers.id', $id)
            ->orderBy('tasks.id', 'DESC')
            ->get();
        if ($tasks->isEmpty()) {
            return response(['data' => [], 'message' => 'No tasks found for this customer'], 404);
        }
        return response(['data' => $tasks, 'message' => 'success'], 200);
    }
  
    public function getactivetickets($id)
    {
        $id = base64_decode($id);
        $tasks = Ticket::leftJoin('customers', 'customers.id', '=', 'tickets.requester_client_id')
        ->leftJoin('priorities', 'priorities.id', 'tickets.priority')
        ->leftjoin('users as assignee', 'assignee.id', 'tickets.assignee_id')
            ->select(
                'customers.*',
                'tickets.*',
                'tickets.created_at as request_date',
                'priorities.title as priority',
                'assignee.name as assignee_name',
                )
            ->where('tickets.requester_client_id', $id)
            ->orderBy('tickets.id', 'DESC')
            ->get();
        if ($tasks->isEmpty()) {
            return response(['data' => [], 'message' => 'No tasks found for this customer'], 404);
        }
        return response(['data' => $tasks, 'message' => 'success'], 200);
    }

    public function getCustomerActivity($id)
    {
        $customerId = base64_decode($id); 
    
        $customer = Customer::leftJoin('users', 'users.id', '=', 'customers.assignedto')
            ->select('customers.*', 'users.name as created_by_name')
            ->find($customerId); 
        if (!$customer) {
            return response(['message' => 'Customer not found.', 'status' => 'error'], 404);
        }
        $customerTicket = Ticket::leftJoin('customers', 'customers.id', '=', 'tickets.assignee_id')
            ->select('tickets.*', 'customers.name as created_by')
            ->where('requester_client_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($task) {
                return array_merge($task->toArray(), ['type' => 'ticket']);
            });
        $customerNotes = Note::leftJoin('users', 'users.id', '=', 'notes.added_by')
            ->select('notes.*', 'users.name as created_by')
            ->where('added_for_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($note) {
                return array_merge($note->toArray(), ['type' => 'note']);
            });
        $customerActivities = collect([
            [
                'activity' => 'Customer Created',
                'id' => $customer->id,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
                'created_by' => $customer->created_by_name,
                'ticket_name' => $customer->ticket_name,
                'type' => 'customer_created',
                'event' => 'created',
            ]
        ]);
        $activities = collect()
            ->merge($customerTicket)
            ->merge($customerNotes)
            ->merge($customerActivities)
            ->sortByDesc('created_at')
            ->values()
            ->all();      
        $additionalActivities = ActivityLog::leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
        ->leftJoin('tickets', 'tickets.id', '=', 'activity_log.subject_id')
        ->select('tickets.*', 'activity_log.*', 'users.name as updated_by_name')
        ->where('activity_log.subject_id', $customerId)
        ->where('activity_log.description', 'Updated Customer')
        ->orderBy('activity_log.created_at', 'asc')
        ->get()
        ->map(function($log) {
            return [
                'activity' => 'Customer Updated',
                'id' => $log->id,
                'created_at' => $log->created_at,
                'created_by' => $log->updated_by_name,
                'ticket_name' => $log->ticket_name,
                'type' => 'customer_updated',
                'event' => 'updated',
            ];
        });
        $activities = collect($activities)->merge($additionalActivities)
            ->sortByDesc('created_at')
            ->values()
            ->all();
        if (collect($activities)->isEmpty()) {
            return response(['message' => 'No activities found for this customer.', 'status' => 'success'], 200);
        }
    
        return response(['data' => $activities, 'status' => 'success'], 200);
    }
    public function getCustomerOldNewActivity($activityId)
    {
        $customerId = base64_decode($activityId);
        $dataQuery = ActivityLog::leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->where('activity_log.subject_id', $customerId)
            ->select('activity_log.*', 'users.name as user_name', 'users.email as user_email')
            ->get();    
        return response(['data' => $dataQuery, 'status' => 'success'], 200);
    }
    public function getallcustomer(Request $request) {
        $getallcustomer = Customer::select('id as value','name as label'
        )->get(); 
        return response()->json([
            'alluser' => $getallcustomer
        ]);
    }
    
   public function getAssignee(Request $request){
    $assignee = User::get();
    return response(['data' => $assignee, 'status' => 'success'], 200);
   }
    private function logActivity($systemData,$id) {
        if (Auth::guard('api')->check()) {
            $userId = Auth::guard('api')->user()->id;
        } else {
            $userId = 1; 
        }
        
        $logEntry = new ActivityLog();
        $logEntry->log_name = 'Customer';
        $logEntry->description = 'Created Customer';
        $logEntry->subject_type = Customer::class;
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
        $system->log_name = 'Customer';
        $system->description = 'Updated Customer';
        $system->subject_type = 'App\Models\Customer';
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
