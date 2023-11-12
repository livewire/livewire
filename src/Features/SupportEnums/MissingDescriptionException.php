<?php

namespace Livewire\Features\SupportEnums;

class MissingDescriptionException extends \Exception
{
    public function __construct($caseName)
    {
        parent::__construct('No #[Description] defined for case: '.$caseName);
    }
}
