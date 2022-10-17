<?php

namespace Livewire\Features\SupportWireables;

use Livewire\Wireable;
use Synthetic\Synthesizers\Synth;

class WireableSynth extends Synth
{
    static function match($target)
    {
        return is_object($target) && $target instanceof Wireable;
    }

    function dehydrate($target, $context)
    {
        return $target->toLivewire();
    }

    function hydrate($value, $meta, $getProperty)
    {
        return
    }
}
