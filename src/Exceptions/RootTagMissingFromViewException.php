<?php

namespace Livewire\Exceptions;

class RootTagMissingFromViewException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Livewire encountered a missing root tag when trying to render the [{$component}] " .
            "component. \n When rendering a Blade view, make sure it contains a root HTML tag."
        );
    }
}
