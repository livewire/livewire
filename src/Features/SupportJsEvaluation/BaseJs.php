<?php

namespace Livewire\Features\SupportJsEvaluation;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseJs extends LivewireAttribute
{
    function dehydrate($context)
    {
        $name = $this->getName();

        $stringifiedMethod = $this->component->$name();

        $context->pushEffect('js', $stringifiedMethod, $name);
    }
}




