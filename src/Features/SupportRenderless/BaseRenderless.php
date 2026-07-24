<?php

namespace Livewire\Features\SupportRenderless;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseRenderless extends LivewireAttribute
{
    function call()
    {
        $this->storeSet('skipIslandsRender', true);

        $this->component->skipRender();
    }
}
