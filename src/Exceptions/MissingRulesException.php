<?php

namespace Livewire\Exceptions;

class MissingRulesException extends \Exception
{
    use BypassViewHandler;

    public function __construct($instance)
    {
        $class = $instance::class;

        parent::__construct(
            "Missing [\$rules/rules()] property/method on: [{$class}]."
        );
    }
}
