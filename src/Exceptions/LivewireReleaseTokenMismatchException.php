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
            "This happens when Livewire's, the application's, or a component's release token has changed since this page was loaded."
        );
    }
}
