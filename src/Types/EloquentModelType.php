<?php

namespace Livewire\Types;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableEntity;
use Livewire\LivewirePropertyType;
use Livewire\Types\Concerns\HydratesEloquentModels;

class EloquentModelType implements LivewirePropertyType
{
    use HydratesEloquentModels;

    public function hydrate($instance, $request, $name, $value)
    {
        if (! $serialized = data_get($request->memo, "dataMeta.models.$name")) {
            throw new \Exception('Absolutely fucked');
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

        if ($request) $this->setDirtyData($model, $request->memo['data'][$name]);

        return $model;
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        $serializedModel = $value instanceof QueueableEntity && ! $value->exists
            ? ['class' => get_class($value)]
            : (array) $this->getSerializedPropertyValue($value);

        if ($response) data_set($response, "memo.dataMeta.models.$name", $serializedModel);

        return $this->filterData($instance, $name);
    }
}
