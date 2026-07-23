<?php

namespace App\Traits;

use App\Helpers\LogActivity;
use Illuminate\Database\Eloquent\Model;

trait HasActivityLog
{
    protected static function bootHasActivityLog()
    {
        static::created(function (Model $model) {
            $logName = $model->getLogName();
            LogActivity::create($logName, $model, 'create');
        });

        static::updated(function (Model $model) {
            $logName = $model->getLogName();
            LogActivity::create($logName, $model, 'update');
        });

        static::deleted(function (Model $model) {
            $logName = $model->getLogName();
            LogActivity::create($logName, $model, 'delete');
        });
    }

    public function getLogName()
    {
        return property_exists($this, 'logName') ? $this->logName : class_basename($this);
    }
}
