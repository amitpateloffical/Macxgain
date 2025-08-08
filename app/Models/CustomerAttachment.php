<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAttachment extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'customer_attachments'; // Specify the table if it doesn't follow naming convention

   
}
