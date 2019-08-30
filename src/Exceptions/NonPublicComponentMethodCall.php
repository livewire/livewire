<?php

namespace Livewire\Exceptions;

class NonPublicComponentMethodCall extends \Exception
{
    use BypassViewHandler;
}
