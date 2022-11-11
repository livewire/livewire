<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

use Illuminate\Support\Stringable as SupportStringable;

class EnumSynth extends Synth {
    public static $key = 'enm';

    static function match($target) {
        return is_object($target) && is_subclass_of($target, 'BackedEnum');
    }

    function dehydrate($target, $context) {
        $context->addMeta('class', get_class($target));

        return $target->value;
    }

    function hydrate($value, $meta) {
        $class = $meta['class'];

        return $class::from($value);
    }
}
