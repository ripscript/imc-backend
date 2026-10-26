<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            Project::MORPH_TYPE => Project::class,
            'project_activation' => Project::class,
            'stage_clearance' => \App\Models\ProjectStage::class,
            'change_request' => \App\Models\PmChangeRequest::class,
        ]);
    }
}
