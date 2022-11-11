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

    function dehydrate($target, $context) {
        return (array) $target;
    }

    function hydrate($value, $meta) {
        $obj = new stdClass;

        foreach ($value as $key => $value) {
            $obj->$key = $value;
        }

        return $obj;
    }

    function set(&$target, $key, $value) {
        $target->$key = $value;
    }

    function methods($target)
    {
        return [];
    }

    function call($target, $method, $params, $addEffect) {
        //
    }
}
