<?php

namespace LegacyTests\Browser\Stacks;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $show = false;

    public function render()
    {
        return <<<'HTML'
<div>
    <button wire:click="$toggle('show')" dusk="toggle-child">Toggle Child</button>
    <button wire:click="$refresh" dusk="refresh-parent">Refresh</button>

    <script>window.stack_output = []</script>

    @if ($show)
        @livewire(\Tests\Browser\Stacks\ChildComponent::class)
    @endif
</div>

@once
    @push('styles')
        <script>window.stack_output.push('parent-styles')</script>
    @endpush
@endonce

@once
    @prepend('scripts')
        <script>window.stack_output.push('parent-scripts')</script>
    @endprepend
@endonce
HTML;
    }
}
