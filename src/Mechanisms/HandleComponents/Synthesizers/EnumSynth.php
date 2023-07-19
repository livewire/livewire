<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

class EnumSynth extends Synth {
    public static $key = 'enm';

    static function match($target) {
        return is_object($target) && is_subclass_of($target, 'BackedEnum');
    }

    function dehydrate($target) {
        return [
            $target->value,
            ['class' => get_class($target)]
        ];
    }

    function hydrate($value, $meta) {
        $class = $meta['class'];

        return $class::from($value);
    }
}
