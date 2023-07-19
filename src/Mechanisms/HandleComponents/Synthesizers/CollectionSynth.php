<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

use Illuminate\Support\Collection;

class CollectionSynth extends ArraySynth {
    public static $key = 'clctn';

    static function match($target) {
        return $target instanceof Collection;
    }

    function dehydrate($target, $dehydrateChild) {
        $data = $target->all();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [
            $data,
            ['class' => get_class($target)]
        ];
    }

    function hydrate($value, $meta, $hydrateChild) {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return new $meta['class']($value);
    }

    function &get(&$target, $key) {
        // We need this "$reader" callback to get a reference to
        // the items property inside collections. Otherwise,
        // we'd receive a copy instead of the reference.
        $reader = function & ($object, $property) {
            $value = & \Closure::bind(function & () use ($property) {
                return $this->$property;
            }, $object, $object)->__invoke();

            return $value;
        };

        $items =& $reader($target, 'items');

        return $items[$key];
    }
}
