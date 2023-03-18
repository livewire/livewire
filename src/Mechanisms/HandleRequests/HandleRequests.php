<?php

namespace Livewire\Mechanisms\HandleRequests;

use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleComponents\Checksum;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;

class HandleRequests
{
    protected $updateRoute;

    function boot()
    {
        app()->singleton($this::class);

        app($this::class)->setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)->middleware('web');
        });

        $this->skipRequestPayloadTamperingMiddleware();
    }

    function getUpdateUri()
    {
        return (string) str($this->updateRoute->uri)->start('/');
    }

    function skipRequestPayloadTamperingMiddleware()
    {
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::skipWhen(function () {
            // @todo: update this...
            return request()->is('synthetic/update');
        });

        \Illuminate\Foundation\Http\Middleware\TrimStrings::skipWhen(function () {
            return request()->is('synthetic/update');
        });
    }

    function setUpdateRoute($callback)
    {
        $route = $callback(function () {
            return $this->handleUpdate();
        });

        // Append `livewire.update` to the existing name, if any.
        $route->name('livewire.update');

        $this->updateRoute = $route;
    }

    function isLivewireRequest()
    {
        // @todo: Needed to re-add this as Testable Livewire doesn't use the update route, and instead sets this.
        // @todo: Also remove `X-Synthetic` from front end and then delete it here.
        return request()->hasHeader('X-Livewire') || request()->hasHeader('X-Synthetic');
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

        return $responses;
    }
}
