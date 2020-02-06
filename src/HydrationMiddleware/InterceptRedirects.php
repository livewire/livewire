<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Redirector;

class InterceptRedirects implements HydrationMiddleware
{
    public static $redirectorCache;

    public static function hydrate($unHydratedInstance, $request)
    {
        static::$redirectorCache = app('redirect');

        app()->bind('redirect', function () use ($unHydratedInstance) {
            $redirector = app(Redirector::class)->component($unHydratedInstance);

            if (app('session.store')) {
                $redirector->setSession(app('session.store'));
            }

            return $redirector;;
        });
    }

    public static function dehydrate($instance, $response)
    {
        app()->instance('redirect', static::$redirectorCache);

        $response->redirectTo = $instance->redirectTo ?? false;
    }
}
