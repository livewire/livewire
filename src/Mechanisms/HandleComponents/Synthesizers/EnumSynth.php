<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

class EnumSynth extends Synth {
    public static $key = 'enm';

    static function match($target) {
        return is_object($target) && is_subclass_of($target, 'BackedEnum');
    }

    static function matchByType($type) {
        return is_subclass_of($type, 'BackedEnum');
    }

    static function hydrateFromType($type, $value) {
        if ($value === '') return null;

        return $type::from($value);
    }

    function dehydrate($target) {
        return [
            $target->value,
            ['class' => get_class($target)]
        ];
    }

    function hydrate($value, $meta) {
        if ($value === null) return null;

        $class = $meta['class'];

        return $class::from($value);
    }
}
