<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Livewire;

class CallHydrationHooks implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        Livewire::dispatch('component.hydrate', $instance, $request);
        Livewire::dispatch('component.hydrate.subsequent', $instance, $request);

        $instance->hydrate($request);

        Livewire::dispatch('component.booted', $instance, $request);
    }

    public static function dehydrate($instance, $response)
    {
        $instance->dehydrate($response);

        Livewire::dispatch('component.dehydrate', $instance, $response);
        Livewire::dispatch('component.dehydrate.subsequent', $instance, $response);
    }

    public static function initialDehydrate($instance, $response)
    {
        $instance->dehydrate($response);

        Livewire::dispatch('component.dehydrate', $instance, $response);
        Livewire::dispatch('component.dehydrate.initial', $instance, $response);
    }

    public static function initialHydrate($instance, $request)
    {
        Livewire::dispatch('component.hydrate', $instance, $request);
        Livewire::dispatch('component.hydrate.initial', $instance, $request);
    }
}
