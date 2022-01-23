<?php

namespace Livewire\Types;

use Illuminate\Contracts\Database\ModelIdentifier;
use Livewire\LivewirePropertyType;
use Livewire\Types\Concerns\HydratesEloquentModels;

class EloquentModelCollectionType implements LivewirePropertyType
{
    use HydratesEloquentModels;

    public function hydrate($instance, $request, $name, $value)
    {
        if (! $serialized = data_get($request->memo, "dataMeta.modelCollections.$name")) {
            return $value;
        }

        $idsWithNullsIntersparsed = $serialized['id'];

        $models = $this->getRestoredPropertyValue(
            new ModelIdentifier(
                $serialized['class'],
                $serialized['id'],
                $serialized['relations'],
                $serialized['connection']
            )
        );

        // Use `loadMissing` here incase loading collection
        // relations gets fixed in Laravel framework, in
        // which case we don't want to load relations again.
        $models->loadMissing($serialized['relations']);

        $dirtyModelData = $request ? $request->memo['data'][$name] : [];

        foreach ($idsWithNullsIntersparsed as $index => $id) {
            if (is_null($id)) {
                $model = new $serialized['class'];
                $models->splice($index, 0, [$model]);
            }

            $this->setDirtyData(data_get($models, $index), data_get($dirtyModelData, $index, []));
        }

        return $models;
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        $serializedModel = (array) $this->getSerializedPropertyValue($value);

        // Deserialize the models into the "meta" bag.
        if ($response) data_set($response, "memo.dataMeta.modelCollections.$name", $serializedModel);

        return $this->filterData($instance, $name);
    }
}
