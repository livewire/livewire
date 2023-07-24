<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

use Illuminate\Support\Stringable;

class StringableSynth extends Synth {
    public static $key = 'str';

    static function match($target) {
        return $target instanceof Stringable;
    }

    function dehydrate($target) {
        return [$target->__toString(), []];
    }

    function hydrate($value) {
        return str($value);
    }
}
