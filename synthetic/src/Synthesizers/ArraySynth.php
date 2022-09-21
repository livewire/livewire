<?php

namespace Synthetic\Synthesizers;

class ArraySynth extends Synth {
    public static $key = 'arr';

    static function match($target) {
        return is_array($target);
    }

    function dehydrate($target, $context) {
        return $target;
    }

    function hydrate($value, $meta) {
        return $value;
    }

    function &get(&$target, $key) {
        return $target[$key];
    }

    function set(&$target, $key, $value) {
        $target[$key] = $value;
    }

    function unset(&$target, $key) {
        unset($target[$key]);
    }
}
