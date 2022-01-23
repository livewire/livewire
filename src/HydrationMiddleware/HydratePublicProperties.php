<?php

namespace Livewire\HydrationMiddleware;

use Livewire\Livewire;

class HydratePublicProperties implements HydrationMiddleware
{
    public static function hydrate($instance, $request)
    {
        $publicProperties = $request->memo['data'] ?? [];

        foreach ($publicProperties as $property => $value) {
            if (Livewire::attemptingToAssignNullToTypedPropertyThatDoesntAllowNullButIsUninitialized($instance, $property, $value)) {
                continue;
            }

            $instance->$property = Livewire::hydrate($instance, $request, $property, $value);
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicData = $instance->getPublicPropertiesDefinedBySubClass();

        data_set($response, 'memo.data', []);
        data_set($response, 'memo.dataMeta', []);

        array_walk($publicData, function ($value, $key) use ($instance, $response) {
            $hydrator = Livewire::hydrator($instance, $key, $value);

            data_set($response, "memo.dataMeta.hydrators.$key", $hydrator);

            data_set($response, "memo.data.$key", app($hydrator)->dehydrate(
                $instance, $response, $key, $value
            ));
        });
    }
}
