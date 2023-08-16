<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

// This synth exists solely to capture empty strings being set to integer properties...
class IntSynth extends Synth {
    public static $key = 'int';

    static function match($target) {
        return false;
    }

    static function matchByType($type) {
        return $type === 'int';
    }

    static function hydrateFromType($type, $value) {
        if ($value === '') return null;

        return (int) $value;
    }
}
