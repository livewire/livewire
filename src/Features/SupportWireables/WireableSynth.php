<?php

namespace Livewire\Features\SupportWireables;

use Livewire\Wireable;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class WireableSynth extends Synth
{
    public static $key = 'wrbl';

    static function match($target)
    {
        return is_object($target) && $target instanceof Wireable;
    }

    static function unwrapForValidation($target)
    {
        return $target->toLivewire();
    }

    function dehydrate($target, $dehydrateChild)
    {
        $data = $target->toLivewire();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [
            $data,
            ['class' => get_class($target)],
        ];
    }

    function hydrate($value, $meta, $hydrateChild) {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return $meta['class']::fromLivewire($value);
    }

    function set(&$target, $key, $value) {
        $target->{$key} = $value;
    }
}
