<?php

namespace Livewire\Exceptions;

class MultipleRootTagsInViewException extends \Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        parent::__construct(
            "Livewire encountered a multiple root tags when trying to render a " .
            "component. \n When rendering a Blade view, make sure it contains " .
            "just one root HTML tag."
        );
    }
}
