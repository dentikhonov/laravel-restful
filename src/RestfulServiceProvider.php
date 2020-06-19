<?php

namespace Devolt\Restful;

use Devolt\Restful\Contracts\Restful;
use Devolt\Restful\Services\JsonApiRestfulService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

final class RestfulServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->bind(Restful::class, fn($app) => new JsonApiRestfulService($app['request']));
    }

    public function provides()
    {
        return [
            Restful::class
        ];
    }
}
