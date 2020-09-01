<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;

class HydratePublicProperties implements HydrationMiddleware
{
    public static function hydrate($unHydratedInstance, $request)
    {
        $publicProperties = $request->memo['data'];

        foreach ($publicProperties as $property => $value) {
            $unHydratedInstance->$property = $value;
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicData = $instance->getPublicPropertiesDefinedBySubClass();

        array_walk($publicData, function ($value, $key) use ($instance) {
            throw_unless(
                is_bool($value) || is_null($value) || is_array($value) || is_numeric($value) || is_string($value) || $value instanceof Model || $value instanceof Collection,
                new PublicPropertyTypeNotAllowedException($instance::getName(), $key, $value)
            );
        });

        $response->memo['data'] = json_decode(json_encode($publicData), true);
    }
}
