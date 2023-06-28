<?php

namespace Livewire\Features\SupportJsEvaluation;

use Livewire\ComponentHook;

use function Livewire\store;

class SupportJsEvaluation extends ComponentHook
{
    function dehydrate($context)
    {
        if (! store($this->component)->has('js')) return;

        $context->addEffect('xjs', store($this->component)->get('js'));
    }
}
