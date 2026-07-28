<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
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
