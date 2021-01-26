<?php

namespace Tests\Browser\Security;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $middleware = [];
    public $showNested = false;

    public function showNestedComponent()
    {
        $this->showNested = true;
    }

    public function render()
    {
        $this->middleware = app('router')->current()->gatherMiddleware();

        return <<<'HTML'
<div>
    <span dusk="middleware">@json($middleware)</span>
    <span dusk="url">{{ \Livewire\Livewire::isLivewireRequest() ? request('fingerprint')['url'] : '' }}</span>

    <button wire:click="$refresh" dusk="refresh">Refresh</button>
    <button wire:click="showNestedComponent" dusk="showNested">Show Nested</button>

    @if ($showNested)
        @livewire(\Tests\Browser\Security\NestedComponent::class)
    @endif
</div>
HTML;
    }
}
