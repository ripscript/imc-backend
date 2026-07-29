<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Pemohon::class => \App\Policies\PemohonPolicy::class,
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
