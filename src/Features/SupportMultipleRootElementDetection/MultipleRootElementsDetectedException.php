<?php

namespace Livewire\Features\SupportMultipleRootElementDetection;

use Livewire\Exceptions\BypassViewHandler;

class MultipleRootElementsDetectedException extends \Exception
{
    use BypassViewHandler;

    function __construct($component)
    {
        parent::__construct('Livewire only supports one HTML element per component. Multiple root elements detected for component: [' . $component->getName() . ']');
    }
}
