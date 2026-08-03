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

        // Record the opt-out; the render is only skipped once every action in the request has (see HandleComponents::callMethods).
        store($this->component)->set('renderlessCallCount', store($this->component)->get('renderlessCallCount', 0) + 1);
    }
}
