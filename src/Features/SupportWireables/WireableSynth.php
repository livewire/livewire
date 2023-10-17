<?php

namespace Livewire\Features\SupportWireables;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Livewire\Wireable;

class WireableSynth extends Synth
{
    public static $key = 'wrbl';

    public static function match($target)
    {
        return is_object($target) && $target instanceof Wireable;
    }

    public function dehydrate($target, $dehydrateChild)
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

    public function hydrate($value, $meta, $hydrateChild)
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return $meta['class']::fromLivewire($value);
    }

    public function set(&$target, $key, $value)
    {
        $target->{$key} = $value;
    }
}
