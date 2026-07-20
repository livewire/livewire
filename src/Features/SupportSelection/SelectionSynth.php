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
        [$keys, $mode] = static::parseWireValue($value);

        return new $type($keys, $mode);
    }

    function dehydrate($target)
    {
        return [[
            'mode' => $target->isAll() ? 'except' : 'include',
            'keys' => $target->isAll() ? $target->except() : $target->all(),
        ], ['class' => get_class($target)]];
    }

    function hydrate($value, $meta)
    {
        // Verify class extends Selection even though checksum protects this...
        if (! isset($meta['class']) || ! is_a($meta['class'], Selection::class, true)) {
            throw new \Exception('Livewire: Invalid selection class.');
        }

        [$keys, $mode] = static::parseWireValue($value);

        return new $meta['class']($keys, $mode);
    }

    protected static function parseWireValue($value): array
    {
        if (! is_array($value)) return [[], 'include'];

        // A plain list (e.g. wire:model sending raw keys) means include mode...
        if (array_is_list($value)) return [array_values($value), 'include'];

        return [
            array_values($value['keys'] ?? []),
            ($value['mode'] ?? null) === 'except' ? 'except' : 'include',
        ];
    }
}
