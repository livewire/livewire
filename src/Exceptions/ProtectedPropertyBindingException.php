<?php

namespace Livewire\Exceptions;

class ProtectedPropertyBindingException extends \Exception
{
    use BypassViewHandler;
}
