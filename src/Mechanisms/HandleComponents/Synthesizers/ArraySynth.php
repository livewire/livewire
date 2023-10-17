<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

class ArraySynth extends Synth
{
    public static $key = 'arr';

    public static function match($target)
    {
        return is_array($target);
    }

    public function dehydrate($target, $dehydrateChild)
    {
        foreach ($target as $key => $child) {
            $target[$key] = $dehydrateChild($key, $child);
        }

        return [$target, []];
    }

    public function hydrate($value, $meta, $hydrateChild)
    {
        // If we are "hydrating" a value about to be used in an update,
        // Let's make sure it's actually an array before try to set it.
        // This is most common in the case of "__rm__" values, but also
        // applies to any non-array value...
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return $value;
    }

    public function set(&$target, $key, $value)
    {
        $target[$key] = $value;
    }

    public function unset(&$target, $key)
    {
        unset($target[$key]);
    }
}
