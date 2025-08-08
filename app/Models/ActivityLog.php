<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Log;
class ActivityLog extends Model
{
    use HasFactory;
    protected $table = 'activity_log';
    protected $fillable = ['id','log_name', 'description', 'subject_type','subject_id','causer_type','causer_id','properties','event'];//phpcs:ignore
    public static function logCreation($model, $action = 'Created')
    {
        $userId = Auth::guard('api')->user() ? Auth::guard('api')->user()->id : null;
        if ($userId === null) {
            Log::warning('User not authenticated for logging creation of ' . class_basename($model));
            return; 
        }
        $logEntry = new ActivityLog();
        $logEntry->log_name = class_basename($model);
        $logEntry->description = $action . ' ' . class_basename($model);
        $logEntry->subject_type = get_class($model);
        $logEntry->subject_id = $model->getKey(); 
        $logEntry->causer_type = 'App\Models\User';
        $logEntry->causer_id = $userId;
        $logEntry->event = strtolower($action);
        $logEntry->properties = json_encode(['attributes' => $model]);
        $logEntry->created_at = Carbon::now();
        $logEntry->updated_at = Carbon::now();
        $logEntry->save();
    }
    
    public static function logUpdate($model, $oldData, $newData)
    {
        $userId = Auth::guard('api')->user()->id;
        $keyOld = "old";
        $oldDataWithKey = [$keyOld => $oldData];
        $keyNew = "attributes";
        $newDataWithKey = [$keyNew => $newData];
        $bothDataWithKey = $newDataWithKey + $oldDataWithKey;
        $jsonData = json_encode($bothDataWithKey);
        $logEntry = new ActivityLog();
        $logEntry->log_name = class_basename($model);
        $logEntry->description = 'Updated ' . class_basename($model);
        $logEntry->subject_type = get_class($model);
        $logEntry->subject_id = $model->id;
        $logEntry->causer_type = 'App\Models\User';
        $logEntry->causer_id = $userId;
        $logEntry->properties = $jsonData;
        $logEntry->event = 'updated';
        $logEntry->created_at = Carbon::now();
        $logEntry->updated_at = Carbon::now();
        $logEntry->save();
    }
}
