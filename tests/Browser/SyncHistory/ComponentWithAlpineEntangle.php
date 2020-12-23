<?php

namespace Tests\Browser\SyncHistory;

use Livewire\Component;

class ComponentWithAlpineEntangle extends Component
{
    public $page = 1;
    public $foo = 'bar';

    protected $queryString = ['page'];

    public function nextPage() { $this->page++; }

    public function render()
    {
        return <<<'blade'
            <div>
                <button wire:click="nextPage" dusk="next">next page</button>
                <span dusk="blade.output">{{ $page }}</span>

                <button wire:click="$set('foo', 'baz')" dusk="changeFoo">prev page</button>
                <div x-data="{ foo: @entangle('foo') }" x-text="foo" dusk="alpine.output"></div>
            </div>
blade;
    }
}
