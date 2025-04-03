<?php

namespace Livewire\Features\SupportRequiredProperties;

use Illuminate\Support\Str;

class RequiredPropertyNotProvidedException extends \Exception
{
    public function __construct($componentName, $missingProperties)
    {
        parent::__construct(sprintf(
            'Missing required %s [%s] in component [%s]',
            Str::plural('property', $missingProperties->count()),
            $missingProperties->implode(', '),
            $componentName,
        ));
    }
}
