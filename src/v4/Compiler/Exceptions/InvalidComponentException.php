<?php

namespace Livewire\v4\Compiler\Exceptions;

use Livewire\Exceptions\BypassViewHandler;

class InvalidComponentException extends \Exception
{
    use BypassViewHandler;
}