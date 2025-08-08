<?php

namespace App\Models;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{

    protected $table ='conversations';
    protected $fillable = [
    ];
  
    public static function getConversationInfo($id)
    {
        
        $conversation = Conversation::orderBy('conversations.id', 'desc')
                     ->leftJoin('users','users.id','=','conversations.added_by')
                     ->leftJoin('tickets','tickets.id','=','conversations.added_for_id')
                     ->leftJoin('conversation_types','conversation_types.id','=','conversations.conversation_type_id')
                     ->leftJoin('conversation_via_types','conversation_via_types.id','=','conversations.conversation_via_id')
            ->select(
                'conversations.message as Message ',
                'conversations.added_at as Added At',
                'users.name as Added By',
                'conversations.added_for as Added For',
                'tickets.ticket_name as Added For This Ticket',
                'conversation_types.title as Conversation Type',
                'conversation_via_types.title as Conversation Via',
                'conversations.created_at as Created At',
                'conversations.updated_at as Updated At',
            )
            ->where('conversations.id', $id)
            ->first();
     
        if (!$conversation) {
            return null;
        }
     
        $attributes = $conversation->getAttributes();
        foreach ($attributes as $key => $value) {
            if (is_null($value)) {
                unset($attributes[$key]);
            }
        }
        return (object) $attributes;
    }
}


