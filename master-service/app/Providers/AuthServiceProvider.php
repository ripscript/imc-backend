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
        \App\Models\MsFaq::class => \App\Policies\MsFaqPolicy::class,
        \App\Models\MsKategoriFaq::class => \App\Policies\MsKategoriFaqPolicy::class,
        \App\Models\MsNegara::class => \App\Policies\MsNegaraPolicy::class,
        \App\Models\MsPerwakilan::class => \App\Policies\MsPerwakilanPolicy::class,
        \App\Models\MsSatuanKerja::class => \App\Policies\MsSatuanKerjaPolicy::class,
        \App\Models\MsSifatInformasi::class => \App\Policies\MsSifatInformasiPolicy::class,
        \App\Models\MsTag::class => \App\Policies\MsTagPolicy::class,
        \App\Models\MsKategoriContent::class => \App\Policies\MsKategoriContentPolicy::class,
        \App\Models\MsHoliday::class => \App\Policies\MsHolidayPolicy::class,
        \App\Models\MsSurveyQuestion::class => \App\Policies\MsSurveyQuestionPolicy::class,
        \App\Models\MsAlasanKeberatan::class => \App\Policies\MsAlasanKeberatanPolicy::class,
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
