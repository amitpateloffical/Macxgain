<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use App\Models\City;

class State extends Model
{
    use HasFactory;
    public function country()
    {
        return $this->hasOne(Country::class);
    }
    public function city()
    {
     return $this->belongsTo(City::class);
    }
}
