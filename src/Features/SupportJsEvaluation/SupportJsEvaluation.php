<?php

namespace Livewire\Features\SupportJsEvaluation;

use function Livewire\store;

use Livewire\ComponentHook;
use Illuminate\Support\Facades\Blade;

class SupportJsEvaluation extends ComponentHook
{
    function dehydrate($context)
    {
        if (! store($this->component)->has('js')) return;

        $context->addEffect('xjs', store($this->component)->get('js'));
    }
}
