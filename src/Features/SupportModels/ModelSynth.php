<?php

namespace Livewire\Features\SupportModels;

use stdClass;
use Synthetic\SyntheticValidation;
use Synthetic\Synthesizers\Synth;
use Livewire\Drawer\Utils;
use Illuminate\Support\Stringable;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Database\ModelIdentifier;
use Exception;

class ModelSynth extends Synth {
    use SerializesAndRestoresModelIdentifiers, SyntheticValidation;

    public static $key = 'mdl';

    static function match($target) {
        return $target instanceof Model;
    }

    function dehydrate($target, $context) {
        if ($target instanceof QueueableEntity && ! $target->exists) {
            $context->addMeta('class', get_class($target));
        } else {
            $serializedModel = (array) $this->getSerializedPropertyValue($target);

            $context->addMeta('connection', $serializedModel['connection']);
            $context->addMeta('relations', $serializedModel['relations']);
            $context->addMeta('class', $serializedModel['class']);
            $context->addMeta('key', $serializedModel['id']);
        }

        // $filteredModelData = $this->filterData($context->root, $target, $context->path);

        $data = $this->getDataFromModel($target);

        return $data;
        // dd($target);

        // Only include the allowed data (defined by rules) in the response payload
        // return $filteredModelData;

        // foreach ($value->relations as $relation) {
        //     $attributes[$relation] = $target->getRelationValue($relation);
        // }
    }

    function getDataFromModel($model)
    {
        $data = [];

        /**
         * Extract normal attributes...
         */
        $attributeKeys = array_keys($model->getAttributes());
        $attributes = array_intersect_key($model->toArray(), array_flip($attributeKeys));

        $data = [...$data, ...$attributes];

        /**
         * Uncast custom casters...
         */
        foreach ($model->getCasts() as $key => $cast) {
            if (! class_exists($cast)) continue;
            $interfaces = class_implements($cast);

            if (array_key_exists(\Illuminate\Contracts\Database\Eloquent\CastsAttributes::class, $interfaces)) {
                $data[$key] = $model->getAttributes()[$key];
            }
        }

        /**
         * Extract relationships...
         */
        $relationKeys = array_keys($model->getRelations());
        $relations = [];
        foreach ($relationKeys as $relation) {
            $relations[$relation] = $model->$relation;
        }

        $data = [...$data, ...$relations];

        return $data;
    }

    function setDataOnModel($model, $data)
    {
        foreach ($data as $key => $value) {
            $model->$key = $value;
        }

        return $model;
    }

    function hydrate($value, $meta) {
        if (! isset($meta['key'])) {
            return tap(new $meta['class'])->forceFill($value);
        }

        $identifier = new ModelIdentifier(
            $meta['class'],
            $meta['key'],
            $meta['relations'] ?? [],
            $meta['connection'],
        );

        $model = $this->getRestoredPropertyValue($identifier);

        return $this->setDataOnModel($model, $value);
    }

    function get(&$target, $key) {
        return $target->$key;
    }

    function set(&$target, $key, $value, $pathThusFar, $fullPath, $root) {
        $component = $root;

        // make sure "path" is the right path here.
        throw_if(
            $component->missingRuleFor($fullPath),
            new CannotBindToModelDataWithoutValidationRuleException($pathThusFar, $component->getName())
        );

        $target->$key = $value;
    }

    function methods($target)
    {
        return ['save'];
    }

    function call($target, $method, $params, $addEffect) {
        if ($method === 'save') {
            $models = $this->validate(
                $target->getAttributes(),
                $target->rules(),
            );

            return $target->save();
        }

        throw new Exception;
    }

    public static function filterData($instance, $model, $path) {
        $data = $model->toArray();

        // $data['items'] = $model->items;

        // foreach ($data as $key => $value) {
        //     if (is_array($value)) {
        //         $data[$key] = $model->$key;
        //     }
        // }

        // If there are any "custom class casts" we need to uncast them individually.
        foreach ($model->getCasts() as $key => $cast) {
            if (! class_exists($cast)) continue;
            $interfaces = class_implements($cast);

            if (array_key_exists(\Illuminate\Contracts\Database\Eloquent\CastsAttributes::class, $interfaces)) {
                $data[$key] = $model->getAttributes()[$key];
            }
        }

        // return $data;
        $rules = $instance->rulesForModel($path)->keys();

        $rules = static::processRules($rules)->get($path, []);

        return static::extractData($data, $rules, []);
    }

    public static function processRules($rules) {
        $rules = \Illuminate\Support\Collection::wrap($rules);

        $rules = $rules
            ->mapInto(Stringable::class);

        [$groupedRules, $singleRules] = $rules->partition(function($rule) {
            return $rule->contains('.');
        });

        $singleRules = $singleRules->map(function(\Illuminate\Support\Stringable $rule) {
            return $rule->__toString();
        });

        $groupedRules = $groupedRules->mapToGroups(function(\Illuminate\Support\Stringable $rule) {
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

    public static function extractData($data, $rules, $filteredData)
    {
        foreach($rules as $key => $rule) {
            if ($key === '*') {
                if ($data) {
                    foreach($data as $item) {
                        $filteredData[] = static::extractData($item, $rule, []);
                    }
                }
            } else {
                if (is_array($rule) || $rule instanceof \Illuminate\Support\Collection) {
                    $newFilteredData = data_get($data, $key) instanceof \stdClass ? new \stdClass : [];
                    data_set($filteredData, $key, static::extractData(data_get($data, $key), $rule, $newFilteredData));
                } else {
                    if ($rule == "*") {
                        $filteredData = $data;
                    } elseif (\Illuminate\Support\Arr::accessible($data) || is_object($data)) {
                        data_set($filteredData, $rule, data_get($data, $rule));
                    }
                }
            }
        }

        return $filteredData;
    }
}
