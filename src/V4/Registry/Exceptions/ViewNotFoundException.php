<?php

namespace Livewire\V4\Registry\Exceptions;

use Livewire\Exceptions\BypassViewHandler;

class ViewNotFoundException extends \Exception
{
    use BypassViewHandler;
}