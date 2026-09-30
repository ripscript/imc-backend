<?php

namespace App\Traits;

use App\Helpers\LogActivity;
use Illuminate\Database\Eloquent\Model;

trait HasActivityLog
{
    protected static function bootHasActivityLog()
    {
        static::created(function (Model $model) {
            LogActivity::create($model->getLogName(), $model, 'create');
        });

        static::updated(function (Model $model) {
            LogActivity::create($model->getLogName(), $model, 'update');
        });

        static::deleted(function (Model $model) {
            LogActivity::create($model->getLogName(), $model, 'delete');
        });
    }

    public function getLogName()
    {
        return property_exists($this, 'logName') ? $this->logName : class_basename($this);
    }
}
