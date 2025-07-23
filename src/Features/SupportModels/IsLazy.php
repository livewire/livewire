<?php

namespace Livewire\Features\SupportModels;

trait IsLazy {
    protected static ?\WeakMap $lazyMetas = null;

    public function isLazy($target) {
        if (PHP_VERSION_ID < 80400) {
            return false;
        }

        return (new \ReflectionClass($target))->isUninitializedLazyObject($target);
    }

    public function getLazyMeta($target) {
        if (! static::$lazyMetas) {
            static::$lazyMetas = new \WeakMap();
        }

        if (! static::$lazyMetas->offsetExists($target)) {
            throw new \Exception('Lazy model not found');
        }

        return static::$lazyMetas[$target];
    }

    public function setLazyMeta($target, $meta) {
        if (! static::$lazyMetas) {
            static::$lazyMetas = new \WeakMap();
        }

        static::$lazyMetas[$target] = $meta;
    }

    public function makeLazyProxy($class, $meta, $callback) {
        if (PHP_VERSION_ID < 80400) {
            return $callback();
        }

        $reflector = new \ReflectionClass($class);

        $lazyModel = $reflector->newLazyProxy($callback);

        $this->setLazyMeta($lazyModel, $meta);

        return $lazyModel;
    }
}