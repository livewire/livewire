<?php

namespace Livewire\Types\Concerns;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Livewire\Attributes\ModelKey;
use Livewire\Livewire;
use Livewire\ReflectionPropertyType;
use ReflectionProperty;
use stdClass;

trait HydratesEloquentModels
{
    use SerializesAndRestoresModelIdentifiers;

    public function filterData($instance, $property)
    {
        if (Livewire::attemptingToAssignNullToTypedPropertyThatDoesntAllowNullButIsUninitialized($instance, $property, null)) return null;

        if (! $instance->$property) return null;

        $data = $instance->$property->toArray();

        $rules = $instance->rulesForModel($property)->keys();

        $rules = $this->processRules($rules)->get($property, []);

        return $this->extractData($data, $rules, []);
    }

    public function processRules($rules)
    {
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
            $collectionRules = $this->processRules($collectionRules);

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

    public function setDirtyData($model, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $existingData = data_get($model, $key);

                if (is_array($existingData)) {
                    $updatedData = $this->setDirtyData([], data_get($data, $key));
                } else {
                    $updatedData = $this->setDirtyData($existingData, data_get($data, $key));
                }
            } else {
                $updatedData = data_get($data, $key);
            }

            if ($model instanceof Model && $model->relationLoaded($key)) {
                $model->setRelation($key, $updatedData);
            } else {
                data_set($model, $key, $updatedData);
            }
        }

        return $model;
    }

    /** @return ModelKey|object|null */
    public function getModelKeyAttribute($instance, $name)
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            return null;
        }

        $property = new ReflectionProperty($instance, $name);

        $attributes = $property->getAttributes(ModelKey::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance();
        }

        return null;
    }
}
