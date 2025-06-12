<?php

namespace Livewire\V4\Compiler\Exceptions;

use Livewire\Exceptions\BypassViewHandler;

class ParseException extends \Exception
{
    use BypassViewHandler;
}