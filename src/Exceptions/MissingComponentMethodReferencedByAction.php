<?php

namespace Livewire\Exceptions;

class MissingComponentMethodReferencedByAction extends \Exception
{
    use BypassViewHandler;
}
