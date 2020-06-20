<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

                // only needed in Laravel 5.6 and 5.7
                if($unHydratedInstance -> $property instanceof EloquentCollection){
                    $collection = $unHydratedInstance -> $property ->keyBy->getKey();
                    $collectionClass = get_class($collection);

                    $unHydratedInstance -> $property =  new $collectionClass(
                        collect($value['id'])->map(function ($id) use ($collection) {
                            return $collection[$id] ?? null;
                        })->filter()
                    );
                }
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
