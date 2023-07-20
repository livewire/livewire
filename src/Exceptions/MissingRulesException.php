<?php

namespace Livewire\Exceptions;

class MissingRulesException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Missing [\$rules/rules()] property/method on Livewire component: [{$component}]."
        );
    }
}
