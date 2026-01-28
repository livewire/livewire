<?php

namespace Livewire\Features\SupportJson;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class BaseJson extends LivewireAttribute
{
    public function dehydrate($context)
    {
        $methodName = $this->getName();

        $context->pushMemo('json', $methodName);
        $context->pushMemo('async', $methodName);
    }
}
