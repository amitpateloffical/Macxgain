<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'amount',
        'description',
        'image_path',
        'request_by',
        'request_create_for',
        'reject_reason',
        'status',
        'approve_by',
        'approve_date',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'request_by');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'request_create_for');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approve_by');
    }
}
