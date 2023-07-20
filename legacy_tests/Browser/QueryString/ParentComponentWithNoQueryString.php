<?php

namespace LegacyTests\Browser\QueryString;

use Livewire\Component as BaseComponent;

class ParentComponentWithNoQueryString extends BaseComponent
{
    public $showNestedComponent = false;

    public function render()
    {
        return <<< 'HTML'
<div>
    <button type="button" wire:click="$toggle('showNestedComponent')" dusk="toggle-nested">Toggle Nested</button>

    <div>
        @if ($showNestedComponent)
            @livewire('nested')
        @endif
    </div>
</div>
HTML;
    }
}
