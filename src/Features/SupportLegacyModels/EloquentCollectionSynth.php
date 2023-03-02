<?php

namespace Livewire\Features\SupportLegacyModels;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;
use LogicException;

class EloquentCollectionSynth extends Synth
{
    public static $key = 'elcl';

    public static function match($target)
    {
        return $target instanceof EloquentCollection;
    }

    public function dehydrate(EloquentCollection $target, $context, $dehydrateChild)
    {
        $class = $target::class;
        $modelClass = $target->getQueueableClass();

        $context->addMeta('keys', $target->modelKeys());
        $context->addMeta('class', $class);
        $context->addMeta('modelClass', $modelClass);

        if ($modelClass && $connection = $this->getConnection($target) !== $modelClass::make()->getConnectionName()) {
            $context->addMeta('connection', $connection);
        }

        $relations = $target->getQueueableRelations();

        if (count($relations)) {
            $context->addMeta('relations', $relations);
        }

        $data = $this->getDataFromCollection($target, $context);

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($child);
        }

        return $data;
    }

    public function getConnection(EloquentCollection $collection)
    {
        if ($collection->isEmpty()) {
            return;
        }

        $connection = $collection->first()->getConnectionName();

        $collection->each(function ($model) use ($connection) {
            // If there is no connection name, it must be a new model so continue.
            if (is_null($model->getConnectionName())) {
                return;
            }

            if ($model->getConnectionName() !== $connection) {
                throw new LogicException('Livewire can\'t dehydrate an Eloquent Collection with models from different connections.');
            }
        });

        return $connection;
    }

    public function getDataFromCollection(EloquentCollection $collection, $context)
    {
        return $this->filterData($collection->all(), $context);
    }

    public function filterData($data, $context)
    {
        return $data;
        // return array_filter($data, function ($key) use ($context) {
        //     return SupportLegacyModels::hasRuleFor($context->root, $context->path . '.' . $key);
        // }, ARRAY_FILTER_USE_KEY);
    }

    public function loadCollection($meta)
    {
        $modelClass = $meta['modelClass'];

        if (isset($meta['keys']) && count($meta['keys']) >= 0) {
            $model = new $meta['modelClass'];

            if (isset($meta['connection'])) {
                $model->setConnection($meta['connection']);
            }

            $query = $model->newQueryForRestoration($meta['keys']);

            if (isset($meta['relations'])) {
                $query->with($meta['relations']);
            }

            $query->useWritePdo();

            $collection = $query->get();

            $collection = $collection->keyBy->getKey();

            return new $meta['class'](
                collect($meta['keys'])->map(function ($id) use ($collection) {
                    return $collection[$id] ?? null;
                })->filter()
            );
        }

        return new $meta['class']();
    }

    public function hydrate($data, $meta, $hydrateChild)
    {
        if (isset($meta['__child_from_parent'])) {
            $collection = $meta['__child_from_parent'];

            unset($meta['__child_from_parent']);
        } else {
            $collection = $this->loadCollection($meta);
        }

        if (count($data)) {
            foreach ($data as $key => $childData) {
                $childData[1]['__child_from_parent'] = $collection->get($key);

                $data[$key] = $hydrateChild($childData);
            }

            return $collection::wrap($data);
        }

        return $collection;
    }

    public function get(&$target, $key)
    {
        return $target->get($key);
    }

    public function set(&$target, $key, $value, $pathThusFar, $fullPath, $root)
    {
        if (SupportLegacyModels::missingRuleFor($root, $fullPath)) {
            throw new CannotBindToModelDataWithoutValidationRuleException($fullPath, $root->getName());
        }

        $target->put($key, $value);
    }

    public function methods($target)
    {
        return [];
    }

    public function call($target, $method, $params, $addEffect)
    {
    }
}
