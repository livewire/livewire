<?php

namespace Livewire\Features\SupportOptimistic;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseOptimistic extends LivewireAttribute
{
    public function dehydrate($context)
    {
        $methodName = $this->getName();

        $context->pushMemo('optimistic', $methodName);
    }
}
