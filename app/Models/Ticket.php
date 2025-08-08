<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Ticket extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table ='tickets';
    protected $fillable = [
        'ticket_name',
        'priority,',     
        'ticket_type',
        'requester_client_id',
        'assignee_id',
        'message_from',
        'message',
        'tags,',        
        'followers,',       
        'ticket_id',
    ];



    public static function getTicketInfo($ticketId)
    {
        $ticket = Ticket::orderBy('tickets.id', 'desc')
            ->leftJoin('users', 'users.id', '=', 'tickets.assignee_id')
            ->leftJoin('priorities', 'priorities.id', '=', 'tickets.priority')
            ->leftJoin('ticket_types', 'ticket_types.id', '=', 'tickets.ticket_type')
            ->leftJoin('customers as requester_client', 'requester_client.id', '=', 'tickets.requester_client_id')
            // ->leftJoin('tags', 'tags.id', '=', 'tickets.tags')
            ->leftJoin('tags', function ($join) {
                $join->on(DB::raw('JSON_CONTAINS(tickets.tags, CAST(tags.id AS JSON))'), '=', DB::raw('1'));
            })
            ->leftJoin('users as follower', function ($join) {
                $join->on(DB::raw('JSON_CONTAINS(tickets.followers, CAST(follower.id AS JSON))'), '=', DB::raw('1'));
            })
            ->leftJoin('users as message_from', 'message_from.id', '=', 'tickets.message_from')
            ->select(
                'tickets.ticket_id as Ticket Id',
                'tickets.ticket_name as Ticket Name',
                'priorities.title as priority',
                'ticket_types.title as Ticket Type',
                'requester_client.name as Requester Client Name',
                'users.name as Assignee Name',
                DB::raw('GROUP_CONCAT(tags.title SEPARATOR ", ") as Tags'),
                DB::raw('GROUP_CONCAT(follower.name SEPARATOR ", ") as Followers'),
                'message_from.name as Message From',
                'tickets.message as Message',

                'tickets.created_at as Created At',
                'tickets.updated_at as Updated At',
               
              
                 )
            ->where('tickets.id', $ticketId)
            ->groupBy(
                'tickets.id',
                'tickets.ticket_id',
                'tickets.ticket_name',
                'priorities.title',
                'ticket_types.title',
                'requester_client.name',
                'users.name',
                'message_from.name',
                'tickets.message',
                'tickets.created_at',
                'tickets.updated_at'
            )
            ->first();
   
        if (!$ticket) {
            return null;
        }
        $attributes = $ticket->getAttributes();
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
    //         ActivityLog::logCreation($model);
    //     });

    //     static::updated(function ($model) {
    //         $oldData = $model->getOriginal();
    //         $newData = $model->getAttributes();
    //         ActivityLog::logUpdate($model, $oldData, $newData);
    //     });
    // }
}
