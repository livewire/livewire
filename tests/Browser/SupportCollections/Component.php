<?php

namespace Tests\Browser\SupportCollections;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $things;
    public $unorderedKeyedThings;

    public function mount()
    {
        $this->things = collect('foo');
        $this->unorderedKeyedThings = collect([
            2 => 'foo',
            1 => 'bar',
        ]);
    }

    public function addBar()
    {
        $this->things->push('bar');
        $this->unorderedKeyedThings[3] = 'baz';
    }

    public function render()
    {
        return <<<'HTML'
<div>
    <button wire:click="addBar" dusk="add-bar">Add Bar</button>

    <div dusk="things">
        @foreach ($things as $thing)
            <h1>{{ $thing }}</h1>
        @endforeach
    </div>

    <div dusk="unordered">
        @foreach ($unorderedKeyedThings as $thing)
            <h1>{{ $thing }}</h1>
        @endforeach
    </div>
</div>
HTML;
    }
}
