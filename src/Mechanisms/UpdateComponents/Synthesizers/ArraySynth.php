<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

class ArraySynth extends Synth {
    public static $key = 'arr';

    static function match($target) {
        return is_array($target);
    }

    function dehydrate($target, $context, $dehydrateChild) {
        foreach ($target as $key => $child) {
            $target[$key] = $dehydrateChild($child);
        }

        return $target;
    }

    function hydrate($value, $meta, $hydrateChild) {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($child);
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
