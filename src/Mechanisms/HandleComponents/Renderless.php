<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class Renderless extends LivewireAttribute
{
    function call()
    {
        $this->component->skipRender();
    }
}
