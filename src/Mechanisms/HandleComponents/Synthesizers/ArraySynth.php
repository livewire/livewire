<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

class ArraySynth extends Synth {
    public static $key = 'arr';

    static function match($target) {
        return is_array($target);
    }

    function dehydrate($target, $dehydrateChild) {
        foreach ($target as $key => $child) {
            $target[$key] = $dehydrateChild($key, $child);
        }

        return [$target, []];
    }

    function hydrate($value, $meta, $hydrateChild) {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return $value;
    }

    function set(&$target, $key, $value) {
        $target[$key] = $value;
    }

    function unset(&$target, $key) {
        unset($target[$key]);
    }
}
