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

        on('dehydrate', function ($target, $context) {
            $uri = (string) str(app($this::class)->updateRoute->uri)->start('/');

            if ($context->mounting) $context->addEffect('uri', $uri);
        });

        $this->skipRequestPayloadTamperingMiddleware();
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
            $snapshot = $component['snapshot'];
            $updates = $component['updates'];
            $calls = $component['calls'];

            [ $snapshot, $effects ] = app('livewire')->update($snapshot, $updates, $calls);

            $responses[] = [
                'snapshot' => $snapshot,
                'effects' => $effects,
            ];
        }

        return $responses;
    }
}
