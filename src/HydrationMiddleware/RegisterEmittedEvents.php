<?php

namespace Livewire\HydrationMiddleware;

class RegisterEmittedEvents implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        $response->eventQueue = $instance->getEventQueue();
        $response->dispatchQueue = $instance->getDispatchQueue();
    }
}
