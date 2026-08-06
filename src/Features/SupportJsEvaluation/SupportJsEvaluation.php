<?php

namespace Livewire\Features\SupportJsEvaluation;

use Livewire\ComponentHook;

class SupportJsEvaluation extends ComponentHook
{
    function dehydrate($context)
    {
        if (! $this->storeHas('js')) return;

        $context->addEffect('xjs', $this->storeGet('js'));
    }
}
