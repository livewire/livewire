<?php

namespace Synthetic\Synthesizers;

use Illuminate\Support\Stringable as SupportStringable;

class StringableSynth extends Synth {
    public static $key = 'str';

    static function match($target) {
        return $target instanceof SupportStringable;
    }

    function dehydrate($target) {
        return $target->__toString();
    }

    function hydrate($value) {
        return str($value);
    }
}
