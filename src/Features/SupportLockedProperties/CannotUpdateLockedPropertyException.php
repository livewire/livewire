<?php

namespace Livewire\Features\SupportLockedProperties;

class CannotUpdateLockedPropertyException extends \Exception
{
    public function __construct(public $property)
    {
        parent::__construct(
            'Cannot update locked property: ['.$this->property.']'
        );
    }
}
