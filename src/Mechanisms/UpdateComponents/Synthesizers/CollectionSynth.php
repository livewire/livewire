<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

use Illuminate\Support\Collection;

use function Livewire\invade;

class CollectionSynth extends ArraySynth {
    public static $key = 'clctn';

    static function match($target) {
        return $target instanceof Collection;
    }

    function dehydrate($target, $context) {
        $context->addMeta('class', get_class($target));

        return $target->all();
    }

    function hydrate($value, $meta) {
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
