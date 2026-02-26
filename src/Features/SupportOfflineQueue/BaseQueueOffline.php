<?php

namespace Livewire\Features\SupportOfflineQueue;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseQueueOffline extends LivewireAttribute
{
    public function dehydrate($context)
    {
        $methodName = $this->getName();

        $context->pushMemo('offlineQueue', $methodName);
    }
}
