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
        // Only set it if another provider hasn't already set it....
        if (! $this->updateRoute) {
            app($this::class)->setUpdateRoute(function ($handle) {
                return Route::post('/livewire/update', $handle)->middleware('web');
            });
        }

        $this->skipRequestPayloadTamperingMiddleware();
    }

    function getUpdateUri()
    {
        return (string) str(
            route($this->updateRoute->getName(), [], false)
        )->start('/');
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
        $components = request('components');

        $responses = [];

        foreach ($components as $component) {
            $snapshot = json_decode($component['snapshot'], associative: true);
            $updates = $component['updates'];
            $calls = $component['calls'];

            [ $snapshot, $effects ] = app('livewire')->update($snapshot, $updates, $calls);

            $responses[] = [
                'snapshot' => json_encode($snapshot),
                'effects' => $effects,
            ];
        }

        $response = [
            'components' => $responses,
            'assets' => SupportScriptsAndAssets::getAssets(),
        ];

        $finish = trigger('response', $response);

        return $finish($response);
    }
}
