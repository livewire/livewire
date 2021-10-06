<?php

namespace Tests\Browser\Stacks;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class ChildComponent extends BaseComponent
{
    public function mount()
    {
        
    }

    public function render()
    {
        return <<<'HTML'
<div>
    The Child
</div>

@once
    @push('scripts', 'yo')
@endonce
HTML;
    }
}
