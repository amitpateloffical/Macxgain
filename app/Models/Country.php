<?php

namespace App\Models;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    public function states()
    {
     return $this->belongsTo(State::class);
    }
}
