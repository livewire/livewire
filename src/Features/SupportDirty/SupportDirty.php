<?php

namespace Livewire\Features\SupportDirty;

use Livewire\ComponentHook;

class SupportDirty extends ComponentHook
{
    public function dehydrate($context)
    {
        if (! $this->storeGet('rebaseline')) {
            return;
        }

        $context->addEffect('rebaseline', true);
    }
}
