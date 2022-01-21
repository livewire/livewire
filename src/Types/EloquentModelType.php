<?php

namespace Livewire\Types;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Livewire\LivewirePropertyType;
use stdClass;

class EloquentModelType implements LivewirePropertyType
{
    use SerializesAndRestoresModelIdentifiers;

    public function hydrate($instance, $name, $value)
    {
        if (isset($value['id'])) {
            $model = (new static)->getRestoredPropertyValue(
                new ModelIdentifier(
                    $value['class'],
                    $value['id'],
                    $value['relations'],
                    $value['connection']
                )
            );
        } else {
            $model = new $value['class'];
        }

        $dirtyModelData = $request->memo['data'][$property];

        static::setDirtyData($model, $dirtyModelData);

        return $model;
    }

    public function dehydrate($instance, $name, $value)
    {
        $serializedModel = $value instanceof QueueableEntity && ! $value->exists
            ? ['class' => get_class($value)]
            : (array) (new static)->getSerializedPropertyValue($value);

        $filteredModelData = $this->filterData($instance, $name);

        // Only include the allowed data (defined by rules) in the response payload
        return $filteredModelData;
    }

    public function filterData($instance, $property) {
        $data = $instance->$property->toArray();

        $rules = $instance->rulesForModel($property)->keys();

        $rules = $this->processRules($rules)->get($property, []);

        return $this->extractData($data, $rules, []);
    }

    public function processRules($rules) {
        $rules = Collection::wrap($rules);

        $rules = $rules->mapInto(Stringable::class);

        [$groupedRules, $singleRules] = $rules->partition(function($rule) {
            return $rule->contains('.');
        });

        $singleRules = $singleRules->map(function(Stringable $rule) {
            return $rule->__toString();
        });

        $groupedRules = $groupedRules->mapToGroups(function(Stringable $rule) {
            return [$rule->before('.')->__toString() => $rule->after('.')];
        });

        $groupedRules = $groupedRules->mapWithKeys(function($rules, $group) {
            // Split rules into collection and model rules.
            [$collectionRules, $modelRules] = $rules
                ->partition(function($rule) {
                    return $rule->contains('.');
                });

            // If collection rules exist, and value of * in model rules, remove * from model rule.
            if ($collectionRules->count()) {
                $modelRules = $modelRules->reject(function($value) {
                    return ((string) $value) === '*';
                });
            }

            // Recurse through collection rules.
            $collectionRules = static::processRules($collectionRules);

            $modelRules = $modelRules->map->__toString();

            $rules = $modelRules->union($collectionRules);

            return [$group => $rules];
        });

        $rules = $singleRules->union($groupedRules);

        return $rules;
    }

    public function extractData($data, $rules, $filteredData)
    {
        foreach($rules as $key => $rule) {
            if ($key === '*') {
                if ($data) {
                    foreach($data as $item) {
                        $filteredData[] = $this->extractData($item, $rule, []);
                    }
                }
            } else {
                if (is_array($rule) || $rule instanceof Collection) {
                    $newFilteredData = data_get($data, $key) instanceof stdClass ? new stdClass : [];
                    data_set($filteredData, $key, $this->extractData(data_get($data, $key), $rule, $newFilteredData));
                } else {
                    if ($rule == "*") {
                        $filteredData = $data;
                    } elseif (Arr::accessible($data) || is_object($data)) {
                        data_set($filteredData, $rule, data_get($data, $rule));
                    }
                }
            }
        }

        return $filteredData;
    }
}
