<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Livewire;

class UpdateQueryString implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        // Only seed public properties from the query string on the initial page load.
        if (Livewire::isLivewireRequest()) return;

        if (empty($properties = $instance->getFromQueryStringProperties())) return;

        foreach ($properties as $property) {
            $instance->$property = request()->query($property, $instance->$property);
        }
    }

    public static function dehydrate($instance, $response)
    {
        if (empty($properties = $instance->getFromQueryString())) return;

        $meta = $response->meta;

        $meta['fromQueryString'] = [
            'properties' => $instance->getFromQueryStringProperties(),
            'excepts' => $instance->getFromQueryStringExcepts(),
        ];

        $response->meta = $meta;
    }
}
