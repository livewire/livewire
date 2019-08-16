<?php

namespace Livewire\Exceptions;

class ComponentNotFoundException extends \Exception
{
    use BypassViewHandler;
}
