<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

class EnumSynth extends Synth
{
    public static $key = 'enm';

    public static function match($target)
    {
        return is_object($target) && is_subclass_of($target, 'BackedEnum');
    }

    public static function matchByType($type)
    {
        return is_subclass_of($type, 'BackedEnum');
    }

    public static function hydrateFromType($type, $value)
    {
        if ($value === '') {
            return null;
        }

        return $type::from($value);
    }

    public function dehydrate($target)
    {
        return [
            $target->value,
            ['class' => get_class($target)],
        ];
    }

    public function hydrate($value, $meta)
    {
        if ($value === null) {
            return null;
        }

        $class = $meta['class'];

        return $class::from($value);
    }
}
