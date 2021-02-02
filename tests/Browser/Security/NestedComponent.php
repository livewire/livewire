<?php

namespace Tests\Browser\Security;

use Livewire\Component as BaseComponent;

class NestedComponent extends BaseComponent
{
    public $middleware = [];

    public function render()
    {
        $this->middleware = Component::$loggedMiddleware;

        return <<<'HTML'
<div>
    <span dusk="nested-middleware">@json($middleware)</span>
    <span dusk="nested-url">{{ \Livewire\Livewire::isDefinitelyLivewireRequest() ? request('fingerprint')['url'] : '' }}</span>

    <button wire:click="$refresh" dusk="refreshNested">Refresh</button>
</div>
HTML;
    }
}
