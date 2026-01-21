<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Support\Facades\Route;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;

use Livewire\Mechanisms\Mechanism;

use function Livewire\trigger;

class HandleRequests extends Mechanism
{
    protected $updateRoute;

    function boot()
    {
        // Only set it if another provider or routes file haven't already set it....
        app()->booted(function () {
            // Check both instance state and router to handle cached routes scenario.
            // When routes are cached and loaded, $this->updateRoute will be null but
            // the route already exists in the router. This prevents duplicate registration
            // which Laravel v12.29.0+ treats as an error.
            if (! $this->updateRoute && ! $this->updateRouteExists()) {
                app($this::class)->setUpdateRoute(function ($handle) {
                    return Route::post('/livewire/update', $handle)->middleware('web');
                });
            }
        });

        $this->skipRequestPayloadTamperingMiddleware();
    }

    protected function updateRouteExists()
    {
        return $this->findUpdateRoute() !== null;
    }

    function getUpdateUri()
    {
        // When routes are cached, $this->updateRoute may be null because
        // setUpdateRoute() was never called (the route already existed).
        // In this case, find the route from the router.
        $route = $this->updateRoute ?? $this->findUpdateRoute();

        return (string) str(
            route($route->getName(), [], false)
        )->start('/');
    }

    protected function findUpdateRoute()
    {
        // Find the route with name ending in 'livewire.update'.
        // Custom routes can have prefixes (e.g., 'tenant.livewire.update')
        // so we check for routes ending with 'livewire.update', not just exact matches.
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (str($route->getName())->endsWith('livewire.update')) {
                return $route;
            }
        }

        return null;
    }

    function skipRequestPayloadTamperingMiddleware()
    {
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::skipWhen(function () {
            return $this->isLivewireRequest();
        });

        \Illuminate\Foundation\Http\Middleware\TrimStrings::skipWhen(function () {
            return $this->isLivewireRequest();
        });
    }

    function setUpdateRoute($callback)
    {
        $route = $callback([self::class, 'handleUpdate']);

        // Append `livewire.update` to the existing name, if any.
        if (! str($route->getName())->endsWith('livewire.update')) {
            $route->name('livewire.update');
        }

        $this->updateRoute = $route;
    }

    function isLivewireRequest()
    {
        return request()->hasHeader('X-Livewire');
    }

    function isLivewireRoute()
    {
        // @todo: Rename this back to `isLivewireRequest` once the need for it in tests has been fixed.
        $route = request()->route();

        if (! $route) return false;

        /*
         * Check to see if route name ends with `livewire.update`, as if
         * a custom update route is used and they add a name, then when
         * we call `->name('livewire.update')` on the route it will
         * suffix the existing name with `livewire.update`.
         */
        return $route->named('*livewire.update');
    }

    function handleUpdate()
    {
        $requestPayload = request(key: 'components', default: []);

        $finish = trigger('request', $requestPayload);

        $requestPayload = $finish($requestPayload);

        $componentResponses = [];

        foreach ($requestPayload as $componentPayload) {
            $snapshot = json_decode($componentPayload['snapshot'], associative: true);
            $updates = $componentPayload['updates'];
            $calls = $componentPayload['calls'];

            [ $snapshot, $effects ] = app('livewire')->update($snapshot, $updates, $calls);

            $componentResponses[] = [
                'snapshot' => json_encode($snapshot),
                'effects' => $effects,
            ];
        }

        $responsePayload = [
            'components' => $componentResponses,
            'assets' => SupportScriptsAndAssets::getAssets(),
        ];

        $finish = trigger('response', $responsePayload);

        return $finish($responsePayload);
    }
}
