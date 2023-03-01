<?php

namespace Livewire\Features\SupportLegacyModels;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class EloquentModelSynth extends Synth
{
    public static $key = 'elmdl';

    public static function match($target)
    {
        return $target instanceof Model;
    }

    public function dehydrate($target, &$context)
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

        return $this->getDataFromModel($target, $context);
    }

    public function getDataFromModel(Model $model, $context)
    {
        return [
            ...$this->filterData($this->getAttributes($model), $context),
            ...$this->filterData($model->getRelations(), $context),
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

    public function filterData($data, $context)
    {
        return array_filter($data, function ($key) use ($context) {
            return SupportLegacyModels::hasRuleFor($context->root, $context->path . '.' . $key);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function load($meta, $loaded)
    {
        if ($loaded) {
            return $loaded;
        }

        return $this->loadModel($meta);
    }

    public function hydrate($value, $meta, $model = null)
    {
        if (is_null($model)) {
            $model = $this->loadModel($meta);
        }

        // Change this to it is a `with()` query
        // instead of being applied after the fact.
        if (isset($meta['relations'])) {
            foreach($meta['relations'] as $relation) {
                if (! isset($value[$relation])) continue;

                $model->setRelation($relation, $value[$relation]);

                unset($value[$relation]);
            }
        }

        $this->setDataOnModel($model, $value);

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
            throw new CannotBindToModelDataWithoutValidationRuleException($pathThusFar, $root->getName());
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
