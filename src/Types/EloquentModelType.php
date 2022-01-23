<?php

namespace Livewire\Types;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\LivewirePropertyType;
use Livewire\ReflectionPropertyType;
use Livewire\Types\Concerns\HydratesEloquentModels;

class EloquentModelType implements LivewirePropertyType
{
    use HydratesEloquentModels;

    public function hydrate($instance, $request, $name, $value)
    {
        if (! $value) return null;

        $modelData = data_get($request->memo, "dataMeta.modelData.$name");

        if (is_int($value) || is_string($value)) {
            if ($attribute = $this->getModelKeyAttribute($instance, $name)) {
                if ($type = ReflectionPropertyType::get($instance, $name)) {
                    if ($modelData) {
                        $value = $modelData[$attribute->key] ?? $value;
                    }

                    $found = $type->getName()::where($attribute->key, $value)->first();

                    if (! $found && ! $type->allowsNull()) {
                        throw new ModelNotFoundException("Model [{$type->getName()}] not found using column [{$attribute->key}] with value [{$value}]");
                    }

                    $value = $found;
                }
            }
        }

        if (! $serialized = data_get($request->memo, "dataMeta.models.$name")) {
            return $value;
        }

        if (isset($serialized['id'])) {
            $model = $this->getRestoredPropertyValue(
                new ModelIdentifier(
                    $serialized['class'],
                    $serialized['id'],
                    $serialized['relations'],
                    $serialized['connection']
                )
            );
        } else {
            $model = new $serialized['class'];
        }

        if ($request) $this->setDirtyData($model, $modelData ?? $request->memo['data'][$name]);

        return $model;
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        if (! $value) return null;

        $serializedModel = $value instanceof QueueableEntity && ! $value->exists
            ? ['class' => get_class($value)]
            : (array) $this->getSerializedPropertyValue($value);

        if ($response) data_set($response, "memo.dataMeta.models.$name", $serializedModel);

        $modelData = $this->filterData($instance, $name);

        if ($attribute = $this->getModelKeyAttribute($instance, $name)) {
            if ($response) data_set($response, "memo.dataMeta.modelData.$name", $modelData);

            return data_get($instance->$name, $attribute->key, $modelData);
        }

        return $modelData;
    }
}
