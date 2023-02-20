<?php

namespace Livewire\Features\SupportJavaScriptMethods;

use Livewire\PropertyHook;
use ReflectionMethod;

#[\Attribute]
class JavaScript extends PropertyHook
{
    function dehydrate($context)
    {
        $name = $this->getName();

        $stringifiedMethod = $this->component->$name();

        $context->pushEffect('js', $stringifiedMethod, $name);
    }
}
