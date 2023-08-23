<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseRenderless extends LivewireAttribute
{
    function call()
    {
        $this->component->skipRender();
    }
}
