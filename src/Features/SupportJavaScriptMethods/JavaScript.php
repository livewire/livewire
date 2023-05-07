<?php

namespace Livewire\Features\SupportJavaScriptMethods;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class JavaScript extends LivewireAttribute
{
    function dehydrate($context)
    {
        $name = $this->getName();

        $stringifiedMethod = $this->component->$name();

        $context->pushEffect('js', $stringifiedMethod, $name);
    }
}




