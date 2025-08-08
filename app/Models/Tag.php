<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ActivityLog;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
    ];
    public static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            ActivityLog::logCreation($model);
        });

        static::updated(function ($model) {
            $oldData = $model->getOriginal();
            $newData = $model->getAttributes();
            ActivityLog::logUpdate($model, $oldData, $newData);
        });
    }
}
