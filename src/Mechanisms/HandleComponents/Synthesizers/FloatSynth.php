<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

// This synth exists solely to capture empty strings being set to float properties...
class FloatSynth extends Synth {
    public static $key = 'float';

    static function match($target)
    {
        return false;
    }

    static function matchByType($type) {
        return $type === 'float';
    }

    static function hydrateFromType($type, $value) {
        if ($value === '' || $value === null) return null;

        if ((float) $value == $value) return (float) $value;

        return $value;
    }
}
