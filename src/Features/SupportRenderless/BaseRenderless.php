<?php

namespace Livewire\Features\SupportRenderless;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseRenderless extends LivewireAttribute
{
    function call()
    {
        $this->storeSet('skipIslandsRender', true);

        // Record the opt-out; the render is only skipped once every action in the request has (see HandleComponents::callMethods).
        $this->storeSet('renderlessCallCount', $this->storeGet('renderlessCallCount', 0) + 1);
    }
}
