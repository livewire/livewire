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

    // Uninitialized `public Selection $selection` properties spring to
    // life as empty selections — no mount() assignment needed...
    function initialize($type, $assign)
    {
        $assign(new $type);
    }

    function dehydrate($target)
    {
        // The total rides in META, not the value: meta comes back through
        // the checksummed snapshot, so a hostile client can't forge it —
        // only the server (via outOf) ever writes it...
        $meta = ['class' => get_class($target)];

        if ($target->total() !== null) $meta['total'] = $target->total();

        return [[
            'mode' => $target->isAll() ? 'except' : 'include',
            'keys' => $target->isAll() ? $target->except() : $target->keys(),
        ], $meta];
    }

    function hydrate($value, $meta)
    {
        // Verify class extends Selection even though checksum protects this...
        if (! isset($meta['class']) || ! is_a($meta['class'], Selection::class, true)) {
            throw new \Exception('Livewire: Invalid selection class.');
        }

        [$keys, $mode] = static::parseWireValue($value);

        $selection = new $meta['class']($keys, $mode);

        if (isset($meta['total'])) $selection->setTotal((int) $meta['total']);

        return $selection;
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
