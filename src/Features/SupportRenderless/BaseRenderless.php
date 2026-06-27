<?php

namespace Livewire\Features\SupportRenderless;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[\Attribute]
class BaseRenderless extends LivewireAttribute
{
    function call()
    {
        store($this->component)->set('skipIslandsRender', true);

        $this->component->skipRender();
    }
}
