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
        if (! isset($request->memo['dataMeta']['models'])) return;

        $models = $request->memo['dataMeta']['models'];

        foreach ($models as $property => $value) {
            $model = (new static)->getRestoredPropertyValue(
                new ModelIdentifier($value['class'], $value['id'], $value['relations'], $value['connection'])
            );

            $dirtyModelData = $request->memo['data'][$property];

            if ($rules = $unHydratedInstance->rulesForModel($property)) {
                $keys = $rules->keys()->map(function ($key) use ($unHydratedInstance) {
                    return $unHydratedInstance->afterFirstDot($key);
                });

                foreach ($keys as $key) {
                    data_set($model, $key, data_get($dirtyModelData, $key));
                }
            }

            $unHydratedInstance->$property = $model;
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicProperties = $instance->getPublicPropertiesDefinedBySubClass();

        foreach ($publicProperties as $property => $value) {
            if (($serializedModel = (new static)->getSerializedPropertyValue($value)) instanceof ModelIdentifier) {
                $meta = $response->memo['dataMeta'] ?? [];

                if (! isset($meta['models'])) $meta['models'] = [];

                if ($rules = $instance->rulesForModel($property)) {
                    $keys = $rules->keys()->map(function ($key) use ($instance) {
                        return $instance->afterFirstDot($key);
                    });

                    $explodedModelData = [];

                    foreach ($keys as $key) {
                        data_set($explodedModelData, $key, data_get($instance->$property, $key));
                    }

                    $instance->$property = $explodedModelData;
                } else {
                    $instance->$property = [];
                }

                // Deserialize the models into the "meta" bag.
                $meta['models'][$property] = (array) $serializedModel;
                $response->memo['dataMeta'] = $meta;
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
