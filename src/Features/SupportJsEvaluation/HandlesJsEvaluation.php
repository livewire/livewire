<?php

namespace Livewire\Features\SupportJsEvaluation;

use function Livewire\store;

trait HandlesJsEvaluation
{
    function js($expression, ...$params)
    {
        store($this)->push('js', compact('expression', 'params'));
    }
}
