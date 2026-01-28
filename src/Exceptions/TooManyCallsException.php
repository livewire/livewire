<?php

namespace Livewire\Exceptions;

class TooManyCallsException extends \Exception
{
    public function __construct(int $count, int $maxCalls)
    {
        $message = "Too many method calls in a single request ({$count}). "
            . "Maximum allowed is {$maxCalls}. "
            . "You can configure this limit in config/livewire.php under 'payload.max_calls'.";

        parent::__construct($message);
    }
}
