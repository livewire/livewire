<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

use stdClass;
use Synthetic\Component;
use Livewire\Drawer\Utils;

class StdClassSynth extends Synth {
    public static $key = 'std';

    static function match($target) {
        return $target instanceof stdClass;
    }

    function dehydrate($target, $context, $dehydrateChild) {
        $data = (array) $target;

        foreach ($target as $key => $child) {
            $data[$key] = $dehydrateChild($child);
        }

        return $data;
    }

    function hydrate($value, $meta, $hydrateChild) {
        $obj = new stdClass;

        foreach ($value as $key => $child) {
            $obj->$key = $hydrateChild($child);
        }

        return $obj;
    }

    function set(&$target, $key, $value) {
        $target->$key = $value;
    }
}
