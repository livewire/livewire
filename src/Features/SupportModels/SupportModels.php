<?php

namespace Livewire\Features\SupportModels;

use function Livewire\on;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Livewire\ComponentHook;

class SupportModels extends ComponentHook
{
    public static $resolvedModels = [];

    public static $staleModels = [];

    static function provide()
    {
        app('livewire')->propertySynthesizer([
            ModelSynth::class,
            EloquentCollectionSynth::class,
        ]);

        on('flush-state', function () {
            static::$resolvedModels = [];
            static::$staleModels = [];
        });
    }

    static function rememberResolvedModel($model)
    {
        $hash = get_class($model).':'.$model->getKey();

        // Don't track models that have gone stale, otherwise a deletion or an
        // update made through the original instance couldn't be detected...
        if (isset(static::$staleModels[$hash])) return;

        static::$resolvedModels[$hash] = $model;
    }

    static function getResolvedModel($class, $key)
    {
        $hash = $class.':'.$key;

        if (isset(static::$staleModels[$hash])) return null;

        $model = static::$resolvedModels[$hash]
            ?? app(PersistentMiddleware::class)->getResolvedRouteModel($class, $key);

        if (! $model) return null;

        // Only reuse an instance that is indistinguishable from a freshly queried
        // one. Once an instance has been deleted or mutated, stop sharing that
        // model for the rest of the request and fall back to fresh queries...
        if (! $model->exists || $model->isDirty() || $model->wasChanged()) {
            static::$staleModels[$hash] = true;

            unset(static::$resolvedModels[$hash]);

            return null;
        }

        return $model;
    }
}
