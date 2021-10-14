<?php

namespace Tests\Browser\Stacks;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ChildComponent extends BaseComponent
{
    public $show = false;
    
    public function render()
    {
        return <<<'HTML'
<div>
    The Child component
    
    <button wire:click="$toggle('show')" dusk="toggle-blade-child">Toggle Blade Component</button>
    <button wire:click="$refresh" dusk="refresh-child">Refresh</button>

    @if ($show)
        <x-stack-child />
        <x-stack-child />
    @endif
</div>

@once
    @push('scripts')
        <script>window.stack_output.push('child-scripts')</script>
    @endpush
@endonce
HTML;
    }
}
