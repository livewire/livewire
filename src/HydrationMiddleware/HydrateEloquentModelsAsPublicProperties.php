<?php

namespace Livewire\HydrationMiddleware;

use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableCollection;
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
            if (isset($value['id'])) {
                $model = (new static)->getRestoredPropertyValue(
                    new ModelIdentifier($value['class'], $value['id'], $value['relations'], $value['connection'])
                );
            } else {
                $model = new $value['class'];
            }

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
            if ($value instanceof QueueableEntity || $value instanceof QueueableCollection) {
                $serializedModel = $value instanceof QueueableEntity && ! $value->exists
                    ? ['class' => get_class($value)]
                    : (array) (new static)->getSerializedPropertyValue($value);
	
	            $modelData = [];
                if ($rules = $instance->rulesForModel($property)) {
                    $keys = $rules->keys()->map(function ($key) use ($instance) {
                        return $instance->afterFirstDot($key);
                    });

                    foreach ($keys as $key) {
                        data_set($modelData, $key, data_get($instance->$property, $key));
                    }
                }

                // Only include the allowed data (defined by rules) in the response payload
	            data_set($response, 'memo.data.'.$property, $modelData);

                // Deserialize the models into the "meta" bag.
                data_set($response, 'memo.dataMeta.models.'.$property, $serializedModel);
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
