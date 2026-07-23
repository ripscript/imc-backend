<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Menu::class => \App\Policies\MenuPolicy::class,
        \App\Models\SubMenu::class => \App\Policies\SubMenuPolicy::class,
        \App\Models\ChildSubMenu::class => \App\Policies\ChildSubMenuPolicy::class,
        \App\Models\Content::class => \App\Policies\ContentPolicy::class,
        \App\Models\LatestInformation::class => \App\Policies\LatestInformationPolicy::class,
        \App\Models\QuickLink::class => \App\Policies\QuickLinkPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
