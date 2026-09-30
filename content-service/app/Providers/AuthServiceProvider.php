<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        \App\Models\ContentDetailPublication::class => \App\Policies\ContentDetailPublicationPolicy::class,
        \App\Models\ContentDetailGallery::class => \App\Policies\ContentDetailGalleryPolicy::class,
        \App\Models\ContentDetailRepository::class => \App\Policies\ContentDetailRepositoryPolicy::class,
        \App\Models\ContentDetailAgenda::class => \App\Policies\ContentDetailAgendaPolicy::class,
        \App\Models\ContentDetailShowcase::class => \App\Policies\ContentDetailShowcasePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('viewApiDocs', function ($user = null) {
            $docsUser = env('DOCS_USER');
            $docsPass = env('DOCS_PASS');

            if (empty($docsUser) || empty($docsPass)) {
                return true;
            }

            return request()->getUser() === $docsUser
                && request()->getPassword() === $docsPass;
        });
    }
}
