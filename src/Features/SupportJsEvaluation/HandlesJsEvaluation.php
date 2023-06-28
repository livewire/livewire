<?php

namespace Livewire\Features\SupportJsEvaluation;

use function Livewire\store;

trait HandlesJsEvaluation
{
    function js($expression)
    {
        store($this)->push('js', $expression);
    }
}
