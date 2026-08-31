<?php

namespace Livewire\Features\SupportModels;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class EloquentCollectionSynth extends Synth {
    use SerializesAndRestoresModelIdentifiers, IsLazy;

    public static $key = 'elcln';

    static function match($target)
    {
        return $target instanceof EloquentCollection;
    }

    function dehydrate(EloquentCollection $target, $dehydrateChild)
    {
        if ($this->isLazy($target)) {
            $meta = $this->getLazyMeta($target);

            return [
                null,
                $meta,
            ];
        }

        $class = $target::class;
        $modelClass = $target->getQueueableClass();

        if ($modelClass) {
            $morphMap = Relation::morphMap();

            $modelAlias = in_array($modelClass, $morphMap)
                ? array_search($modelClass, $morphMap, true)
                : $modelClass;
        } else {
            $modelAlias = null;
        }

        $meta = [];

        $serializedCollection = (array) $this->getSerializedPropertyValue($target);

        $meta['keys'] = $serializedCollection['id'];
        $meta['class'] = $class;
        $meta['modelClass'] = $modelAlias;

        return [
            null,
            $meta
        ];
    }

    function hydrate($data, $meta, $hydrateChild)
    {
        $class = $meta['class'];

        $modelClass = $meta['modelClass'];

        // If no alias found, this returns `null`
        $modelAlias = $modelClass ? Relation::getMorphedModel($modelClass) : null;

        if (! is_null($modelAlias)) {
            $modelClass = $modelAlias;
        }

        $keys = $meta['keys'] ?? [];

        if (count($keys) === 0) {
            return new $class();
        }

        return $this->makeLazyProxy($class, $meta, function () use ($modelClass, $keys, $meta) {
            $models = [];
            $missingKeys = [];

            // Reuse any models already resolved this request — by route binding (via
            // SubstituteBindings middleware) or by another hydrated property — so
            // that only the remaining models need to be queried.
            foreach (array_unique($keys) as $key) {
                if ($model = SupportModels::getResolvedModel($modelClass, $key)) {
                    $models[$key] = $model;
                } else {
                    $missingKeys[] = $key;
                }
            }

            if (count($missingKeys) > 0) {
                // We are using Laravel's method here for restoring the collection, which ensures
                // that all models in the collection are restored in one query, preventing n+1
                // issues and also only restores models that exist.
                $collection = (new $modelClass)->newQueryForRestoration($missingKeys)->useWritePdo()->get();

                foreach ($collection as $model) {
                    $models[$model->getKey()] = $model;

                    SupportModels::rememberResolvedModel($model);
                }
            }

            return new $meta['class'](
                collect($meta['keys'])->map(function ($id) use ($models) {
                    return $models[$id] ?? null;
                })->filter()
            );
        });
    }

    function get(&$target, $key) {
        throw new \Exception('Can\'t access model properties directly');
    }

    function set(&$target, $key, $value, $pathThusFar, $fullPath) {
        throw new \Exception('Can\'t set model properties directly');
    }

    function call($target, $method, $params, $addEffect) {
        throw new \Exception('Can\'t call model methods directly');
    }
}