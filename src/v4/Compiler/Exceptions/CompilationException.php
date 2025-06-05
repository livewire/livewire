<?php

namespace Livewire\v4\Compiler\Exceptions;

use Livewire\Exceptions\BypassViewHandler;

class CompilationException extends \Exception
{
    use BypassViewHandler;
}