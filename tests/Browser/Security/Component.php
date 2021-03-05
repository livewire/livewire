<?php

namespace Tests\Browser\Security;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public static $loggedMiddleware = [];

    public $middleware = [];
    public $showNested = false;

    public function showNestedComponent()
    {
        $this->showNested = true;
    }

    public function render()
    {
        $this->middleware = static::$loggedMiddleware;

        return <<<'HTML'
<div>
    <span dusk="middleware">@json($middleware)</span>
    <span dusk="path">{{ \Livewire\Livewire::isDefinitelyLivewireRequest() ? request('fingerprint')['path'] : '' }}</span>

    <button wire:click="$refresh" dusk="refresh">Refresh</button>
    <button wire:click="showNestedComponent" dusk="showNested">Show Nested</button>

    <h1>Protected Content</h1>

    @if ($showNested)
        @livewire(\Tests\Browser\Security\NestedComponent::class)
    @endif
</div>
HTML;
    }
}
