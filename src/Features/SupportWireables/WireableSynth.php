<?php

namespace Livewire\Features\SupportWireables;

use Livewire\Wireable;
use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;

class WireableSynth extends Synth
{
    public static $key = 'wrbl';

    static function match($target)
    {
        return is_object($target) && $target instanceof Wireable;
    }

    function dehydrate($target, $context, $dehydrateChild)
    {
        $context->addMeta('class', get_class($target));

        $data = $target->toLivewire();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return $data;
    }

    function hydrate($value, $meta, $hydrateChild) {
        foreach ($value as $key => $iValue) {
            $value[$key] = $hydrateChild($key, $iValue);
        }

        return $meta['class']::fromLivewire($value);
    }
}
