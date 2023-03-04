<?php

namespace Livewire\Mechanisms\UpdateComponents\Synthesizers;

use function Livewire\wrap;
use function Livewire\trigger;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;

use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\DataStore;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportModels\Lazy;

class LivewireSynth extends Synth
{
    public static $key = 'lw';

    static function match($target) {
        return $target instanceof \Livewire\Component;
    }

    function dehydrate($target, $dehydrateChild) {
        throw new \Exception('Dehydration shouldnt be handled directly by this synth...');
    }

    function hydrate($data, $meta, $hydrateChild) {
        throw new \Exception('Hydration shouldnt be handled directly by this synth...');
    }

    function set(&$target, $key, $value) {
        $target->$key = $value;
    }
}
