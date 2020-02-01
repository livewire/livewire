<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;

class HydratePublicProperties implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $publicProperties = $request['data'];

        foreach ($publicProperties as $property => $value) {
            $unHydratedInstance->$property = $value;
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicData = $instance->getPublicPropertiesDefinedBySubClass();

        array_walk($publicData, function ($value, $key) use ($instance) {
            throw_unless(
                is_bool($value) || is_null($value) || is_array($value) || is_numeric($value) || is_string($value),
                new PublicPropertyTypeNotAllowedException($instance->getName(), $key, $value)
            );
        });

        $response->data = json_decode(json_encode($publicData), true);
    }
}
