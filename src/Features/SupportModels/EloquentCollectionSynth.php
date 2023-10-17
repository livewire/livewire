<?php

namespace Livewire\Features\SupportModels;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class EloquentCollectionSynth extends Synth
{
    use SerializesAndRestoresModelIdentifiers;

    public static $key = 'elcln';

    public static function match($target)
    {
        return $target instanceof EloquentCollection;
    }

    public function dehydrate(EloquentCollection $target, $dehydrateChild)
    {
        $class = $target::class;
        $modelClass = $target->getQueueableClass();

        /**
         * `getQueueableClass` above checks all models are the same and
         * then returns the class. We then instantiate a model object
         * so we can call `getMorphClass()` on it.
         *
         * If no alias is found, this just returns the class name
         */
        $modelAlias = $modelClass ? (new $modelClass)->getMorphClass() : null;

        $meta = [];

        $serializedCollection = (array) $this->getSerializedPropertyValue($target);

        $meta['keys'] = $serializedCollection['id'];
        $meta['class'] = $class;
        $meta['modelClass'] = $modelAlias;

        return [
            null,
            $meta,
        ];
    }

    public function hydrate($data, $meta, $hydrateChild)
    {
        $class = $meta['class'];

        $modelClass = $meta['modelClass'];

        // If no alias found, this returns `null`
        $modelAlias = Relation::getMorphedModel($modelClass);

        if (! is_null($modelAlias)) {
            $modelClass = $modelAlias;
        }

        $keys = $meta['keys'] ?? [];

        if (count($keys) === 0) {
            return new $class();
        }

        // We are using Laravel's method here for restoring the collection, which ensures
        // that all models in the collection are restored in one query, preventing n+1
        // issues and also only restores models that exist.
        $collection = (new $modelClass)->newQueryForRestoration($keys)->useWritePdo()->get();

        return $collection;
    }

    public function get(&$target, $key)
    {
        throw new \Exception('Can\'t access model properties directly');
    }

    public function set(&$target, $key, $value, $pathThusFar, $fullPath)
    {
        throw new \Exception('Can\'t set model properties directly');
    }

    public function call($target, $method, $params, $addEffect)
    {
        throw new \Exception('Can\'t call model methods directly');
    }
}
