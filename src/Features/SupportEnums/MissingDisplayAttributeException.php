<?php

namespace Livewire\Features\SupportEnums;

class MissingDisplayAttributeException extends \Exception
{
    public function __construct($caseName)
    {
        parent::__construct('No #[Display] defined for case: '.$caseName);
    }
}
