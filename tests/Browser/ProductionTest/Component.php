<?php

namespace Tests\Browser\ProductionTest;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = 'squishy';

    public function mount()
    {
        config()->set('app.debug', false);
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <input type="text" wire:model="foo" dusk="foo">
</div>
HTML;
    }
}
