<?php

namespace Livewire\Features\SupportLegacyModels;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Component;

class EloquentModelSynth extends Synth
{
    public static $key = 'elmdl';

    public static function match($target)
    {
        return $target instanceof Model;
    }

    public function dehydrate($target, $context, $dehydrateChild)
    {
        $class = $target::class;

        // If no alias is found, this just returns the class name
        $alias = $target->getMorphClass();

        $context->addMeta('key', $target->getKey());
        $context->addMeta('class', $alias);

        if ($target->getConnectionName() !== $class::make()->getConnectionName()) {
            $context->addMeta('connection', $target->getConnectionName());
        }

        $relations = $target->getQueueableRelations();

        if (count($relations)) {
            $context->addMeta('relations', $relations);
        }

        $rules = $this->getRules($context);

        if (empty($rules)) return [];

        $data = $this->getDataFromModel($target, $rules);

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($child, ['key' => $key, 'rules' => $rules[$key] ?? []]);
        }

        return $data;
    }

    public function getRules($context)
    {
        $key = $context->dataFromParent['key'] ?? null;

        if (is_null($key)) return [];

        if (isset($context->dataFromParent['parent']) && $context->dataFromParent['parent'] instanceof Component) {
            return SupportLegacyModels::getRulesFor($context->dataFromParent['parent'], $key);
        }

        if (isset($context->dataFromParent['rules'])) {
            return $context->dataFromParent['rules'];
        }

        return [];
    }

    public function getDataFromModel(Model $model, $rules)
    {
        return [
            ...$this->filterData($this->getAttributes($model), $rules),
            ...$this->filterData($model->getRelations(), $rules),
        ];
    }

    public function getAttributes($model)
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

    public function filterData($data, $rules)
    {
        return array_filter($data, function ($key) use ($rules) {
            return array_key_exists($key, $rules) ||in_array($key, $rules);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function hydrate($data, $meta, $hydrateChild)
    {
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

                $model->setRelation($relationKey, $hydrateChild($data[$relationKey]));

                unset($data[$relationKey]);
            }
        }

        foreach ($data as $key => $child) {
            $data[$key] = $hydrateChild($child);
        }

        $this->setDataOnModel($model, $data);

        return $model;
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

    public function setDataOnModel(Model $model, $data)
    {
        foreach ($data as $key => $value) {
            $model->$key = $value;
        }
    }

    public function get(&$target, $key)
    {
        return $target->$key;
    }

    public function set(Model &$target, $key, $value, $pathThusFar, $fullPath, $root)
    {
        if (SupportLegacyModels::missingRuleFor($root, $fullPath)) {
            throw new CannotBindToModelDataWithoutValidationRuleException($fullPath, $root->getName());
        }

        if ($target->relationLoaded($key)) {
            return $target->setRelation($key, $value);
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
}
