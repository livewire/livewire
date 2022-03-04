<?php

namespace Tests\Browser\Morph;

use Livewire\Component as BaseComponent;

class WithNestedLivewireComponent extends BaseComponent
{
    public $showPreviousChild = false;

    public function render()
    {
        return <<< 'HTML'
<div>
    <button wire:click="$toggle('showPreviousChild')" dusk="togglePreviousChild">Toggle Previous Child</button>

    <div dusk="output">
        @if ($showPreviousChild)
            <div>first</div>
        @endif

        <div>
            <div>second</div>
            <div>
                @livewire(\Tests\Browser\Morph\NestedComponent::class, [], key('nested'))
            </div>
        </div>
    </div>

    <script>window.useMorphdom = true</script>
</div>
HTML;
    }
}
