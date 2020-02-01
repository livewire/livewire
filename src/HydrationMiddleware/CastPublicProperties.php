<?php

namespace Livewire\HydrationMiddleware;

use Livewire\DataCaster;

class CastPublicProperties implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $casts = $unHydratedInstance->getCasts();

        foreach ($casts as $property => $cast) {
            $unHydratedInstance->$property = (new DataCaster)->castTo(
                $cast,
                $unHydratedInstance->$property
            );
        }
    }

    public static function dehydrate($instance, $response)
    {
        $casts = $instance->getCasts();

        foreach ($casts as $property => $cast) {
            $instance->$property = (new DataCaster)->castFrom(
                $cast,
                $instance->$property
            );
        }
    }
}
