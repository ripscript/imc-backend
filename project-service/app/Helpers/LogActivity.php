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

        $activity = activity('project-management')->event($action);

        if ($model) {
            $activity->performedOn($model);
        }

        if ($user) {
            $activity->causedBy($user);
        }

        $projectId = $model && isset($model->project_id)
            ? $model->project_id
            : ($model instanceof \App\Models\Project ? $model->getKey() : null);

        $activity->withProperties(['project_id' => $projectId])->log($description);
    }
}
