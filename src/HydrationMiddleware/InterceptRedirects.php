<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Routing\Redirector;

class InterceptRedirects implements HydrationMiddleware
{
    public static $redirectorCache;

    public static function hydrate($unHydratedInstance, $request)
    {
        static::$redirectorCache = app('redirect');

        app()->bind('redirect', function () use ($unHydratedInstance) {
            return app(Redirector::class)->component($unHydratedInstance);
        });
    }

    public static function dehydrate($instance, $response)
    {
        app()->instance('redirect', static::$redirectorCache);

        $response->redirectTo = $instance->redirectTo ?? false;
    }
}
