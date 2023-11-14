<?php

namespace Livewire\Features\SupportLockedProperties;

class CannotUpdateLockedPropertyException extends \Exception
{
    public function __construct($property)
    {
        parent::__construct(
            'Cannot update locked property: ['.$property.']'
        );
    }
}
