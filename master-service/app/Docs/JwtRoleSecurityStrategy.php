<?php

namespace App\Docs;

use Dedoc\Scramble\Configuration\OperationTransformers;
use Dedoc\Scramble\Configuration\SecurityDocumentationContext;
use Dedoc\Scramble\Contracts\SecurityDocumentationStrategy;
use Dedoc\Scramble\GeneratorConfig;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\Str;

class JwtRoleSecurityStrategy implements SecurityDocumentationStrategy
{
    public function configure(SecurityDocumentationContext $context): GeneratorConfig
    {
        $adminScheme = SecurityScheme::http('bearer')->as('bearerAdmin');
        $pemohonScheme = SecurityScheme::http('bearer')->as('bearerPemohon');

        return $context->config
            ->withDocumentTransformers(function (OpenApi $openApi) use ($adminScheme, $pemohonScheme) {
                $openApi->components->addSecurityScheme('bearerAdmin', $adminScheme);
                $openApi->components->addSecurityScheme('bearerPemohon', $pemohonScheme);
            })
            ->withOperationTransformers(function (OperationTransformers $transformers) {
                $transformers->prepend(function (Operation $operation, RouteInfo $routeInfo): void {
                    $middleware = collect($routeInfo->route->gatherMiddleware());

                    $hasJwt = $middleware->contains(fn ($m) => Str::startsWith($m, 'jwt.error'));

                    $adminFound = $middleware->some(fn ($m) => Str::is('jwt.error:admin', $m));
                    $pemohonFound = $middleware->some(fn ($m) => Str::is('jwt.error:pemohon', $m));

                    $commaSeparated = $middleware->first(fn ($m) => Str::is('jwt.error:*,*', $m));
                    if ($commaSeparated) {
                        $guards = explode(',', Str::after($commaSeparated, 'jwt.error:'));
                        if (in_array('admin', $guards)) {
                            $adminFound = true;
                        }
                        if (in_array('pemohon', $guards)) {
                            $pemohonFound = true;
                        }
                    }

                    if ($adminFound && $pemohonFound) {
                        $operation->addSecurity(new SecurityRequirement(['bearerAdmin' => []]));
                        $operation->addSecurity(new SecurityRequirement(['bearerPemohon' => []]));
                    } elseif ($adminFound) {
                        $operation->addSecurity(new SecurityRequirement(['bearerAdmin' => []]));
                    } elseif ($pemohonFound) {
                        $operation->addSecurity(new SecurityRequirement(['bearerPemohon' => []]));
                    } elseif ($hasJwt) {
                        $operation->addSecurity(new SecurityRequirement(['bearerAdmin' => []]));
                    } else {
                        $operation->security = [];
                    }
                });
            });
    }
}