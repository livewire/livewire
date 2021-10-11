<?php

namespace Tests\Browser\Stacks;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ChildComponent extends BaseComponent
{
    public function render()
    {
        return <<<'HTML'
<div>
    The Child component
</div>

@once
    @push('scripts')
        <div dusk="child-stack-push">From child push</div>
    @endpush
@endonce

@once
    @prepend('scripts')
        <div dusk="child-stack-prepend">From child prepend</div>
    @endprepend
@endonce
HTML;
    }
}
