<?php

namespace Livewire\Attributes;

use Livewire\Mechanisms\HandleComponents\SkipRender as BaseSkipRender;

#[\Attribute]
class SkipRender extends BaseSkipRender
{
    function call()
    {
        $this->component->skipRender();
    }
}
