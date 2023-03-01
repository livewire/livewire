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
            $data[$key] = $dehydrateChild($child);
        }

        return $data;
    }

    function hydrate($value, $meta, $hydrateChild) {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($child);
        }

        return $meta['class']::fromLivewire($value);
    }
}
