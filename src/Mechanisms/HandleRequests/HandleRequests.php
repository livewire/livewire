<?php

namespace Livewire\Mechanisms\HandleRequests;

use function Livewire\on;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
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

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $uri = (string) str(app($this::class)->updateRoute->uri)->start('/');

            if ($context->initial) $context->addEffect('uri', $uri);
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
        $targets = request('targets');

        $responses = [];

        foreach ($targets as $target) {
            $snapshot = $target['snapshot'];
            $diff = $target['diff'];
            $calls = $target['calls'];

            $response = Livewire::update($snapshot, $diff, $calls);

            unset($response['target']);

            $responses[] = $response;
        }

        return $responses;
    }
}
