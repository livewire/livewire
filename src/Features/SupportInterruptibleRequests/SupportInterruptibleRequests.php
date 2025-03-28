<?php

namespace Livewire\Features\SupportInterruptibleRequests;

use Livewire\ComponentHook;

class SupportInterruptibleRequests extends ComponentHook
{
    public function dehydrate($context)
    {
        if ($this->shouldBeInterruptible()) {
            $context->addMemo('interruptible', true);
        }
    }

    public function shouldBeInterruptible()
    {
        return $this->component->getAttributes()
            ->filter(fn ($i) => is_subclass_of($i, BaseInterruptible::class))
            ->count() > 0;
    }
}