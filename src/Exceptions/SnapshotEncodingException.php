<?php

namespace Livewire\Exceptions;

class SnapshotEncodingException extends \Exception
{
    public function __construct(string $componentName, string $jsonError)
    {
        $message = "Failed to JSON-encode the Livewire snapshot for component [{$componentName}]: {$jsonError}. "
            . "This usually means a public property contains data with invalid UTF-8 encoding. "
            . "Ensure all string data stored in public properties is valid UTF-8.";

        parent::__construct($message);
    }
}
