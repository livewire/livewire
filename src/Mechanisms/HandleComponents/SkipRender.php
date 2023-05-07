<?php

namespace Livewire\Mechanisms\HandleComponents;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class SkipRender extends LivewireAttribute
{
    function call()
    {
        $this->component->skipRender();
    }
}
