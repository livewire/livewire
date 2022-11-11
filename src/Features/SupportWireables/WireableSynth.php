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

    function dehydrate($target, $context)
    {
        $context->addMeta('class', get_class($target));

        return $target->toLivewire();
    }

    function hydrate($value, $meta)
    {
        return $meta['class']::fromLivewire($value);
    }
}
