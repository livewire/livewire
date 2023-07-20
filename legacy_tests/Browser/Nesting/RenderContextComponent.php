<?php

namespace LegacyTests\Browser\Nesting;

use Livewire\Component as BaseComponent;

class RenderContextComponent extends BaseComponent
{
    public $one = 'Blade 1';
    public $two = 'Blade 2';
    public $three = 'Blade 3';

    public function render()
    {
        return <<< 'HTML'
<div>
    <x-blade-component dusk="output.blade-component1" property="one" />
    <x-blade-component dusk="output.blade-component2" property="two" />

    <div>
        @livewire('nested', ['output' => 'Sub render'])
    </div>

    <x-blade-component dusk="output.blade-component3" property="three" />
</div>
HTML;
    }
}
