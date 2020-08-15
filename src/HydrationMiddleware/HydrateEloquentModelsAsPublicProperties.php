<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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

    /**
     * This method overrides the one included in the "SerializesAndRestoresModelIdentifiers" trait.
     * It adopts a Laravel 5.8+ fix to provide better support for 5.6+.
     * https://github.com/laravel/framework/blob/5.8/src/Illuminate/Queue/SerializesAndRestoresModelIdentifiers.php#L60-L90
     */
    protected function restoreCollection($value)
    {
        if (! $value->class || count($value->id) === 0) {
            return new EloquentCollection;
        }

        $collection = $this->getQueryForModelRestoration(
            (new $value->class)->setConnection($value->connection), $value->id
        )->useWritePdo()->get();

        if (is_a($value->class, Pivot::class, true) ||
            in_array(AsPivot::class, class_uses($value->class))) {
            return $collection;
        }

        $collection = $collection->keyBy->getKey();

        $collectionClass = get_class($collection);

        return new $collectionClass(
            collect($value->id)->map(function ($id) use ($collection) {
                return $collection[$id] ?? null;
            })->filter()
        );
    }
}
