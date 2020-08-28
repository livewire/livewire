<?php

namespace Livewire\Exceptions;

class InvalidHistoryPathException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Livewire encountered an invalid URL when trying to hydrate the [{$component}] component. \n".
            "You cannot update the history state to a URL that is not on the same root domain as your app."
        );
    }
}
