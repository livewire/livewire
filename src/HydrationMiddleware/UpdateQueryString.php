<?php

namespace Livewire\HydrationMiddleware;

class UpdateQueryString implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        if (! empty($properties = $instance->getUpdatesQueryString())) {
            $response->updatesQueryString = $properties;
        }
    }

    public static function dehydrate($instance, $response)
    {
        if (! empty($properties = $instance->getUpdatesQueryString())) {
            $response->updatesQueryString = $properties;
        }
    }
}
