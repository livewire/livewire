<?php

namespace Tests\Browser\Stacks;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $show = false;

    public function render()
    {
        return <<<'HTML'
<div>
    <button wire:click="$toggle('show')" dusk="show-child">Show Child</button>

    @if ($show)
        @livewire(\Tests\Browser\Stacks\ChildComponent::class)
    @endif
</div>

@once
    @push('scripts')
        foo
    @endpush
@endonce
HTML;
    }
}
