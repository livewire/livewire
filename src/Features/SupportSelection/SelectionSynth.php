<?php

namespace Livewire\Features\SupportSelection;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class SelectionSynth extends Synth {
    public static $key = 'sel';

    static function match($target)
    {
        return $target instanceof Selection;
    }

    static function matchByType($type)
    {
        return is_a($type, Selection::class, true);
    }

    function hydrateFromType($type, $value)
    {
        return new $type(is_array($value) ? array_values($value) : []);
    }

    function dehydrate($target)
    {
        return [$target->all(), ['class' => get_class($target)]];
    }

    function hydrate($value, $meta)
    {
        // Verify class extends Selection even though checksum protects this...
        if (! isset($meta['class']) || ! is_a($meta['class'], Selection::class, true)) {
            throw new \Exception('Livewire: Invalid selection class.');
        }

        return new $meta['class'](is_array($value) ? array_values($value) : []);
    }
}
