<?php

namespace Livewire\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class LivewirePageExpiredBecauseNewDeploymentHasSignificantEnoughChanges extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            419,
            'New deployment contains changes to Livewire that have invalidated currently open browser pages.'
        );
    }
}
