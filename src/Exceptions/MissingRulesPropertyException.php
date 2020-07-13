<?php

namespace Livewire\Exceptions;

class MissingRulesPropertyException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Missing [\$rules] property on Livewire component: [{$component}]."
        );
    }
}
