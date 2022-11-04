<?php

namespace Livewire\Features\SupportModels;

use Synthetic\Synthesizers\Synth;

class CastableSynth extends Synth {
    public static $key = 'csts';

    static function match($target) {
        return $target instanceof \Illuminate\Contracts\Database\Eloquent\CastsAttributes;
    }

    function dehydrate($target, $context) {
        dd($target);
    }

    function hydrate($value, $meta) {
        // return/h
    }

    function set(&$target, $key, $value, $root, $path) {

    }

    function methods($target)
    {
        return [];
    }

    function call($target, $method, $params, $addEffect) {
        //
    }
}
