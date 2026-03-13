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

    // In debug mode, let Laravel render the full error page.
    // In production, return a generic 419 to avoid leaking details.
    public function render($request)
    {
        if (config('app.debug')) return false;

        return response('', 419);
    }
}
