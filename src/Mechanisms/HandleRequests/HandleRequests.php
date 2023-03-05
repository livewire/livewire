<?php

namespace Livewire\Mechanisms\HandleRequests;

use function Livewire\on;

use Livewire\Mechanisms\HandleComponents\Synthesizers\LivewireSynth;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;

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

        $this->updateRoute = $route;
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
