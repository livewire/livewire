<?php

namespace Livewire\Exceptions;

class CannotCombineHistoryPathAndQueryStringException extends \Exception
{
    use BypassViewHandler;

    public function __construct($component)
    {
        parent::__construct(
            "Livewire encountered both a query string and history path when trying to hydrate the [{$component}] component. \n".
            "You cannot combine these features. If you need both, you must set the query string in your mapStateToUrl method."
        );
    }
}
