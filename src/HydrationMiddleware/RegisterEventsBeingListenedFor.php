<?php

namespace Livewire\HydrationMiddleware;

class RegisterEventsBeingListenedFor implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        //
    }

    public static function dehydrate($instance, $response)
    {
        $response->events = $instance->getEventsBeingListenedFor();
    }
}
