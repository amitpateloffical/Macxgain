<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Customer extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'customers'; // Specify the table if it doesn't follow naming convention

    // Define the fillable properties to allow mass assignment
    protected $fillable = [
        'customer_id',
        'name',
        'contactperson',
        'email',
        'contactnumber',
        'assignedto',
        'engagement'
    ];
    public static function getCustomerInfo($customerId)
    {
        $customer = Customer::orderBy('customers.id', 'desc')
            ->leftJoin('users', 'users.id', '=', 'customers.assignedto')
            ->leftJoin('countries', 'countries.id', '=', 'customers.country_id')
            ->leftJoin('cities', 'cities.id', '=', 'customers.city_id')
            ->leftJoin('states', 'states.id', '=', 'customers.state_id')
            ->select(
                'customers.name as Customer Name',
                'customers.contactperson as Contact Person',
                'customers.email as Email',
                'customers.contactnumber as Contact Number',
                'users.name as Assigned To',
                'customers.customer_id as Customer Id',
                'customers.engagement as Engagement',
                'customers.created_at as Created At',
                'customers.updated_at as Updated At',
                'countries.country_name as Country',
                'cities.name as City',
                'states.name as State',
                'customers.size as Size',
                'customers.location as Location',
                'customers.customer_type as Customer Type',
                'customers.primary_contact_name as Primary Contact Name',
                'customers.primary_contact_email as Primary Contact Email',
                'customers.primary_contact_phone as Primary Contact Phone',
                'customers.secondary_contact_name as Secondary Contact Name',
                'customers.secondary_contact_email as Secondary Contact Email',
                'customers.secondary_contact_phone as Secondary Contact Phone',
                DB::raw("REGEXP_REPLACE(customers.key_challenges, '<[^>]*>', '') as Key_Challenges"),
                DB::raw("REGEXP_REPLACE(customers.important_considerations, '<[^>]*>', '') as Important_Considerations"),
                DB::raw("REGEXP_REPLACE(customers.specific_requirements, '<[^>]*>', '') as Specific_Requirements"),
                DB::raw("REGEXP_REPLACE(customers.potential_risks, '<[^>]*>', '') as Potential_Risks"),
                DB::raw("REGEXP_REPLACE(customers.product_features_promised, '<[^>]*>', '') as Product_Features_Promised"),
                DB::raw("REGEXP_REPLACE(customers.service_level_aggriment, '<[^>]*>', '') as Service_Level_Agreement"),
                DB::raw("REGEXP_REPLACE(customers.discounts, '<[^>]*>', '') as Discounts"),
                DB::raw("REGEXP_REPLACE(customers.general_notes, '<[^>]*>', '') as General_Notes")
            )
            ->where('customers.id', $customerId)
            ->first();
        if (!$customer) {
            return null;
        }
        $attributes = $customer->getAttributes();
        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                unset($attributes[$key]);
            }
        }
        return (object) $attributes;
    }
    
    
        // public static function boot()
    // {
    //     parent::boot();
    //     static::created(function ($model) {
    //         if ($model->exists) {
    //             ActivityLog::logCreation($model);
    //         }
    //     });

    //     static::updated(function ($model) {
    //         $oldData = $model->getOriginal();
    //         $newData = $model->getAttributes();
    //         ActivityLog::logUpdate($model, $oldData, $newData);
    //     });
    // }
}
