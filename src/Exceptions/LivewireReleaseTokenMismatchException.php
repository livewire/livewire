<?php

namespace Livewire\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class LivewireReleaseTokenMismatchException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            419,
            "Livewire detected a release token mismatch. \n".
            "This happens when a user's browser session has been invalidated by a new deployment."
        );
    }
}
