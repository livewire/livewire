<?php

namespace Livewire\HydrationMiddleware;

use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
                if (isset($serialized['id'])) {
                    $model = (new static)->getRestoredPropertyValue(
                        new ModelIdentifier($serialized['class'], $serialized['id'], $serialized['relations'], $serialized['connection'])
                    );
                } else {
                    $model = new $serialized['class'];
                }

                $dirtyModelData = $request->memo['data'][$property];

                if ($rules = $instance->rulesForModel($property)) {
                    $keys = $rules->keys()->map(function ($key) use ($instance) {
                        return $instance->beforeFirstDot($instance->afterFirstDot($key));
                    });

                    foreach ($keys as $key) {
                        data_set($model, $key, data_get($dirtyModelData, $key));
                    }
                }

                $instance->$property = $model;
            } else {
                $instance->$property = $value;
            }
        }
    }

    public static function dehydrate($instance, $response)
    {
        $publicData = $instance->getPublicPropertiesDefinedBySubClass();

        data_set($response, 'memo.data', []);

        array_walk($publicData, function ($value, $key) use ($instance, $response) {
            if (
                // The value is a supported type, set it in the data, if not, throw an exception for the user.
                is_bool($value) || is_null($value) || is_array($value) || is_numeric($value) || is_string($value)
            ) {
                data_set($response, 'memo.data.'.$key, $value);
            } else if ($value instanceof QueueableEntity || $value instanceof QueueableCollection) {
                static::dehydrateModel($value, $key, $response, $instance);
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
            } else {
                throw new PublicPropertyTypeNotAllowedException($instance::getName(), $key, $value);
            }
        });
    }

    protected static function dehydrateModel($value, $property, $response, $instance)
    {
        $serializedModel = $value instanceof QueueableEntity && ! $value->exists
            ? ['class' => get_class($value)]
            : (array) (new static)->getSerializedPropertyValue($value);

        // Deserialize the models into the "meta" bag.
        data_set($response, 'memo.dataMeta.models.'.$property, $serializedModel);

        $modelData = [];
        if ($rules = $instance->rulesForModel($property)) {
            $keys = $rules->keys()->map(function ($key) use ($instance) {
                return $instance->beforeFirstDot($instance->afterFirstDot($key));
            });

            foreach ($keys as $key) {
                data_set($modelData, $key, data_get($instance->$property, $key));
            }
        }

        // Only include the allowed data (defined by rules) in the response payload
        data_set($response, 'memo.data.'.$property, $modelData);
    }
}
