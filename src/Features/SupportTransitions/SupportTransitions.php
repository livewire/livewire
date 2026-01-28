<?php

namespace Livewire\Features\SupportTransitions;

use Livewire\ComponentHook;

class SupportTransitions extends ComponentHook
{
    function dehydrate($context)
    {
        $type = $this->storeGet('transitionType');
        $skip = $this->storeGet('transitionSkip');

        if ($type || $skip) {
            $context->addEffect('transition', array_filter([
                'type' => $type,
                'skip' => $skip,
            ]));
        }
    }
}
