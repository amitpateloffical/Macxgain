<?php

namespace App\Models;
use App\Models\ActivityLog;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
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
