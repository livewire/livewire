<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

class EnumSynth extends Synth {
    public static $key = 'enm';

    static function match($target) {
        return is_object($target) && is_subclass_of($target, 'UnitEnum');
    }

    static function matchByType($type) {
        return is_subclass_of($type, 'UnitEnum');
    }

    static function hydrateFromType($type, $value) {
        if ($value === '') return null;

        $backed = is_subclass_of($type, 'BackedEnum');

        if(! $backed) {
            return is_subclass_of($value, 'UnitEnum')
                ? $value
                : constant("$type::$value");
        }

        return $type::from($value);
    }

    function dehydrate($target) {
        $backed = is_subclass_of($target, 'BackedEnum');

        return [
            $backed ? $target->value : $target->name,
            ['class' => get_class($target)]
        ];
    }

    function hydrate($value, $meta) {
        if ($value === null || $value === '') return null;

        $class = $meta['class'];

        $backed = is_subclass_of($class, 'BackedEnum');

        return $backed ? $class::from($value) : constant("$class::$value");
    }
}
