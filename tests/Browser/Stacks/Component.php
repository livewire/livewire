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
        <div dusk="parent-stack-push">From parent push</div>
    @endpush
@endonce

@once
    @prepend('scripts')
        <div dusk="parent-stack-prepend">From parent prepend</div>
    @endprepend
@endonce
HTML;
    }
}
