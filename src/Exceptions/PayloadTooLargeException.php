<?php

namespace Livewire\Exceptions;

class PayloadTooLargeException extends \Exception
{
    public function __construct(int $size, int $maxSize)
    {
        $sizeKb = round($size / 1024);
        $maxKb = round($maxSize / 1024);

        $message = "Livewire request payload is too large ({$sizeKb}KB). "
            . "Maximum allowed size is {$maxKb}KB. "
            . "You can configure this limit in config/livewire.php under 'payload.max_size'.";

        parent::__construct($message);
    }
}
