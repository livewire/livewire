<?php

namespace Livewire\HydrationMiddleware;

use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;

class HydratePublicProperties implements HydrationMiddleware
{
    use SerializesAndRestoresModelIdentifiers;

    public static function hydrate($instance, $request)
    {
        $publicProperties = $request->memo['data'] ?? [];

        $dates = data_get($request, 'memo.dataMeta.dates', []);
        $collections = data_get($request, 'memo.dataMeta.collections', []);
        $models = data_get($request, 'memo.dataMeta.models', []);
        $modelCollections = data_get($request, 'memo.dataMeta.modelCollections', []);
        $stringables = data_get($request, 'memo.dataMeta.stringables', []);

        foreach ($publicProperties as $property => $value) {
            if ($type = data_get($dates, $property)) {
                $types = [
                    'native' => DateTime::class,
                    'carbon' => Carbon::class,
                    'illuminate' => IlluminateCarbon::class,
                ];

                data_set($instance, $property, new $types[$type]($value));
            } else if (in_array($property, $collections)) {
                data_set($instance, $property, collect($value));
            } else if ($serialized = data_get($models, $property)) {
                static::hydrateModel($serialized, $property, $request, $instance);
            } else if ($serialized = data_get($modelCollections, $property)) {
                static::hydrateModels($serialized, $property, $request, $instance);
            } else if (in_array($property, $stringables)) {
                data_set($instance, $property, new Stringable($value));
            } else {
                // If the value is null, don't set it, because all values start off as null and this
                // will prevent Typed properties from wining about being set to null.
                is_null($value) || $instance->$property = $value;
            }
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicData = $instance->getPublicPropertiesDefinedBySubClass();

        data_set($response, 'memo.data', []);
        data_set($response, 'memo.dataMeta', []);

        array_walk($publicData, function ($value, $key) use ($instance, $response) {
            if (
                // The value is a supported type, set it in the data, if not, throw an exception for the user.
                is_bool($value) || is_null($value) || is_array($value) || is_numeric($value) || is_string($value)
            ) {
                data_set($response, 'memo.data.'.$key, $value);
            } else if ($value instanceof QueueableEntity) {
                static::dehydrateModel($value, $key, $response, $instance);
            } else if ($value instanceof QueueableCollection) {
                static::dehydrateModels($value, $key, $response, $instance);
            } else if ($value instanceof Collection) {
                $response->memo['dataMeta']['collections'][] = $key;

                data_set($response, 'memo.data.'.$key, $value->toArray());
            } else if ($value instanceof DateTime) {
                if ($value instanceof IlluminateCarbon) {
                    $response->memo['dataMeta']['dates'][$key] = 'illuminate';
                } elseif ($value instanceof Carbon) {
                    $response->memo['dataMeta']['dates'][$key] = 'carbon';
                } else {
                    $response->memo['dataMeta']['dates'][$key] = 'native';
                }

                data_set($response, 'memo.data.'.$key, $value->format(\DateTimeInterface::ISO8601));
            } else if ($value instanceof Stringable) {
                $response->memo['dataMeta']['stringables'][] = $key;

                data_set($response, 'memo.data.'.$key, $value->__toString());
            } else {
                throw new PublicPropertyTypeNotAllowedException($instance::getName(), $key, $value);
            }
        });
    }

    protected static function hydrateModel($serialized, $property, $request, $instance)
    {
        if (isset($serialized['id'])) {
            $model = (new static)->getRestoredPropertyValue(
                new ModelIdentifier($serialized['class'], $serialized['id'], $serialized['relations'], $serialized['connection'])
            );
        } else {
            $model = new $serialized['class'];
        }

        $modelData = $request->memo['data'][$property];

        foreach ($modelData as $key => $value) {
            data_set($model, $key, $value);
        }

        $instance->$property = $model;
    }

    protected static function hydrateModels($serialized, $property, $request, $instance)
    {
        $idsWithNullsIntersparsed = $serialized['id'];

        $models = (new static)->getRestoredPropertyValue(
            new ModelIdentifier($serialized['class'], $serialized['id'], $serialized['relations'], $serialized['connection'])
        );

        /*
         * Use `loadMissing` here incase loading collection relations gets fixed in Laravel framework,
         * in which case we don't want to load relations again.
         */
        $models->loadMissing($serialized['relations']);

        $dirtyModelData = $request->memo['data'][$property];

        foreach ($idsWithNullsIntersparsed as $index => $id) {
            if ($rules = $instance->rulesForModel($property)) {
                $keys = $rules->keys()
                    ->map([$instance, 'ruleWithNumbersReplacedByStars'])
                    ->mapInto(Stringable::class)
                    ->filter->contains('*.')
                    ->map->after('*.')
                    ->map->__toString();

                if (is_null($id)) {
                    $model = new $serialized['class'];
                    $models->splice($index, 0, [$model]);
                }

                foreach ($keys as $key) {
                    data_set($models[$index], $key, data_get($dirtyModelData[$index], $key));
                }
            }
        }

        $instance->$property = $models;
    }

    protected static function dehydrateModel($value, $property, $response, $instance)
    {
        $serializedModel = $value instanceof QueueableEntity && ! $value->exists
            ? ['class' => get_class($value)]
            : (array) (new static)->getSerializedPropertyValue($value);

        // Deserialize the models into the "meta" bag.
        data_set($response, 'memo.dataMeta.models.'.$property, $serializedModel);

        $filteredModelData = [];
        if ($rules = $instance->rulesForModel($property)) {
            $keys = $rules->keys()->map(function ($key) use ($instance) {
                return $instance->beforeFirstDot($instance->afterFirstDot($key));
            });

            $fullModelData = $instance->$property->toArray();

            foreach ($keys as $key) {
                data_set($filteredModelData, $key, data_get($fullModelData, $key));
            }
        }

        // Only include the allowed data (defined by rules) in the response payload
        data_set($response, 'memo.data.'.$property, $filteredModelData);
    }

    protected static function dehydrateModels($value, $property, $response, $instance)
    {
        $serializedModel = (array) (new static)->getSerializedPropertyValue($value);

        // Deserialize the models into the "meta" bag.
        data_set($response, 'memo.dataMeta.modelCollections.'.$property, $serializedModel);

        $filteredModelData = [];
        if ($rules = $instance->rulesForModel($property)) {
            $keys = $rules->keys()
                ->map([$instance, 'ruleWithNumbersReplacedByStars'])
                ->mapInto(Stringable::class)
                ->filter->contains('*.')
                ->map->after('*.')
                ->map->__toString();

            $fullModelData = $instance->$property->map->toArray();

            foreach ($fullModelData as $index => $data) {
                $filteredModelData[$index] = [];

                foreach ($keys as $key) {
                    data_set($filteredModelData[$index], $key, data_get($data, $key));
                }
            }
        }

        // Only include the allowed data (defined by rules) in the response payload
        data_set($response, 'memo.data.'.$property, $filteredModelData);
    }
}
