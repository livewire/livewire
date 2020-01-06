<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Livewire\DataCaster;
use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;

class HydratePublicProperties implements HydrationMiddleware
{
    use SerializesAndRestoresModelIdentifiers;

    public static function hydrate($unHydratedInstance, $request)
    {
        // Grab the public properties out of the request.
        $publicProperties = $request['data'];

        foreach ($publicProperties as $property => $value) {
            $unHydratedInstance->$property
                = static::castValue($property, $value, $unHydratedInstance->getCasts(), function ($property) use ($unHydratedInstance) {
                    $unHydratedInstance->lockPropertyFromSync($property);
                });
        }
    }

    public static function dehydrate($instance, $response)
    {
        $data = $instance->getPublicPropertiesDefinedBySubClass();

        $uncastData = collect($data)->mapWithKeys(function ($value, $key) use ($instance) {
            return [$key => static::uncastValue($key, $value, $instance->getCasts())];
        })->all();

        array_walk($uncastData, function ($value, $key) use ($instance) {
            throw_unless(
                is_bool($value) || is_null($value) || is_array($value) || is_numeric($value) || is_string($value),
                new PublicPropertyTypeNotAllowedException($instance->getName(), $key, $value)
            );
        });

        $response->data = json_decode(json_encode($uncastData), true);
    }

    public static function castValue($propertyName, $value, $casts, $callIfThePropertyIsADeserializedModel)
    {
        if (isset($casts[$propertyName])) {
            return (new DataCaster)->castTo($casts[$propertyName], $value);
        }

        if (is_array($value) && array_keys($value) === ['class', 'id', 'relations', 'connection']) {
            $callIfThePropertyIsADeserializedModel($propertyName);

            return (new static)->getRestoredPropertyValue(
                new ModelIdentifier($value['class'], $value['id'], $value['relations'], $value['connection'])
            );
        }

        return $value;
    }

    public static function uncastValue($propertyName, $value, $casts)
    {
        if (isset($casts[$propertyName])) {
            return (new DataCaster)->castFrom($casts[$propertyName], $value);
        }

        if (($serializedModel = (new static)->getSerializedPropertyValue($value)) instanceof ModelIdentifier) {
            return (array) $serializedModel;
        }

        return $value;
    }
}
