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

        return $context->config
            ->withDocumentTransformers(function (OpenApi $openApi) use ($adminScheme) {
                $openApi->components->addSecurityScheme('bearerAdmin', $adminScheme);
            })
            ->withOperationTransformers(function (OperationTransformers $transformers) {
                $transformers->prepend(function (Operation $operation, RouteInfo $routeInfo): void {
                    $middleware = collect($routeInfo->route->gatherMiddleware());

                    $hasJwt = $middleware->contains(fn ($m) => Str::startsWith($m, 'jwt.error'));

                    if ($hasJwt) {
                        $operation->addSecurity(new SecurityRequirement(['bearerAdmin' => []]));
                    } else {
                        $operation->security = [];
                    }
                });
            });
    }
}
