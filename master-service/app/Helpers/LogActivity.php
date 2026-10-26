<?php

namespace App\Helpers;

class LogActivity
{
    public static function create($logName, $model, $action = 'unknown', $user = null)
    {
        if (! $user) {
            $user = auth()->user();
        }
        $description = match (strtolower($action)) {
            'create' => 'Menambahkan data '.$logName,
            'update' => 'Memperbarui data '.$logName,
            'delete' => 'Menghapus data '.$logName,
            default => ucfirst($action).' data '.$logName,
        };

        activity($logName)
            ->performedOn($model)
            ->causedBy($user)
            ->log($description);
    }

    public static function createCustom($logName, $description, $model = null, $user = null)
    {
        if (! $user && auth('admin')->check()) {
            $user = auth('admin')->user();
        }

        $activity = activity($logName);

        if ($user) {
            $activity->causedBy($user);
        }

        if ($model) {
            $activity->performedOn($model);
        }

        $activity->log($description);
    }
}
