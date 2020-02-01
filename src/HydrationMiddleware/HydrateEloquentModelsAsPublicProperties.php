<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

class HydrateEloquentModelsAsPublicProperties implements HydrationMiddleware
{
    use SerializesAndRestoresModelIdentifiers;

    public static function hydrate($unHydratedInstance, $request)
    {
        $publicProperties = $unHydratedInstance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if (is_array($value) && array_keys($value) === ['class', 'id', 'relations', 'connection']) {
                $unHydratedInstance->lockPropertyFromSync($property);

                $unHydratedInstance->$property = (new static)->getRestoredPropertyValue(
                    new ModelIdentifier($value['class'], $value['id'], $value['relations'], $value['connection'])
                );
            }
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicProperties = $instance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if (($serializedModel = (new static)->getSerializedPropertyValue($value)) instanceof ModelIdentifier) {
                $instance->$property = (array) $serializedModel;
            }
        }
    }
}
