<?php

namespace Livewire\Features\SupportLegacyModels;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\ClassMorphViolationException;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class EloquentModelSynth extends Synth
{
    public static $key = 'elmdl';

    public static function match($target)
    {
        return $target instanceof Model;
    }

    public function dehydrate($target, $dehydrateChild)
    {
        $class = $target::class;

        try {
            // If no alias is found, this just returns the class name
            $alias = $target->getMorphClass();
        } catch (ClassMorphViolationException $e) {
            // If the model is not using morph classes, this exception is thrown
            $alias = $class;
        }

        $meta = [];

        if ($target->exists) {
            $meta['key'] = $target->getKey();
        }

        $meta['class'] = $alias;

        if ($target->getConnectionName() !== $class::make()->getConnectionName()) {
            $meta['connection'] = $target->getConnectionName();
        }

        $relations = $target->getQueueableRelations();

        if (count($relations)) {
            $meta['relations'] = $relations;
        }

        $rules = $this->getRules($this->context);

        if (empty($rules)) return [[], $meta];

        $data = $this->getDataFromModel($target, $rules);

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [$data, $meta];
    }

    public function hydrate($data, $meta, $hydrateChild)
    {
        if ($data === '' || $data === null) return null;
        
        if (isset($meta['__child_from_parent'])) {
            $model = $meta['__child_from_parent'];

            unset($meta['__child_from_parent']);
        } else {
            $model = $this->loadModel($meta);
        }

        if (isset($meta['relations'])) {
            foreach($meta['relations'] as $relationKey) {
                if (! isset($data[$relationKey])) continue;

                $data[$relationKey][1]['__child_from_parent'] = $model->getRelation($relationKey);

                $model->setRelation($relationKey, $hydrateChild($relationKey, $data[$relationKey]));

                unset($data[$relationKey]);
            }
        }

        foreach ($data as $key => $child) {
            $data[$key] = $hydrateChild($key, $child);
        }

        $this->setDataOnModel($model, $data);

        return $model;
    }

    public function get(&$target, $key)
    {
        return $target->$key;
    }

    public function set(Model &$target, $key, $value, $pathThusFar, $fullPath)
    {
        if (SupportLegacyModels::missingRuleFor($this->context->component, $fullPath)) {
            throw new CannotBindToModelDataWithoutValidationRuleException($fullPath, $this->context->component->getName());
        }

        if ($target->relationLoaded($key)) {
            return $target->setRelation($key, $value);
        }

        if (array_key_exists($key, $target->getCasts()) && enum_exists($target->getCasts()[$key]) && $value === '') {
            $value = null;
        }

        $target->$key = $value;
    }

    public function methods($target)
    {
        return [];
    }

    public function call($target, $method, $params, $addEffect)
    {
    }

    protected function getRules($context)
    {
        $key = $this->path ?? null;

        if (is_null($key)) return [];

        if ($context->component) {
            return SupportLegacyModels::getRulesFor($this->context->component, $key);
        }
    }

    protected function getDataFromModel(Model $model, $rules)
    {
        return [
            ...$this->filterAttributes($this->getAttributes($model), $rules),
            ...$this->filterRelations($model->getRelations(), $rules),
        ];
    }

    protected function getAttributes($model)
    {
        $attributes = $model->attributesToArray();

        foreach ($model->getCasts() as $key => $cast) {
            if (! class_exists($cast)) continue;

            if (
                in_array(CastsAttributes::class, class_implements($cast))
                && isset($attributes[$key])
                ) {
                $attributes[$key] = $model->getAttributes()[$key];
            }
        }

        return $attributes;
    }

    protected function filterAttributes($data, $rules)
    {
        $filteredAttributes = [];

        foreach($rules as $key => $rule) {
            // If the rule is an array, take the key instead
            if (is_array($rule)) {
                $rule = $key;
            }

            // If someone has created an empty model, the attribute may not exist
            // yet, so use data_get so it will still be sent to the front end.
            $filteredAttributes[$rule] = data_get($data, $rule);
        }

        return $filteredAttributes;
    }

    protected function filterRelations($data, $rules)
    {
        return array_filter($data, function ($key) use ($rules) {
            return array_key_exists($key, $rules) || in_array($key, $rules);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function loadModel($meta): ?Model
    {
        $class = $meta['class'];

        // If no alias found, this returns `null`
        $aliasClass = Relation::getMorphedModel($class);

        if (! is_null($aliasClass)) {
            $class = $aliasClass;
        }

        if (isset($meta['key'])) {
            $model = new $class;

            if (isset($meta['connection'])) {
                $model->setConnection($meta['connection']);
            }

            $query = $model->newQueryForRestoration($meta['key']);

            if (isset($meta['relations'])) {
                $query->with($meta['relations']);
            }

            $model = $query->first();
        } else {
            $model = new $class();
        }

        return $model;
    }

    protected function setDataOnModel(Model $model, $data)
    {
        foreach ($data as $key => $value) {
            $model->$key = $value;
        }
    }
}
