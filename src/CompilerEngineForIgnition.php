<?php

namespace Livewire;

use Facade\Ignition\Views\Engines\PhpEngine;
use Facade\Ignition\Views\Engines\CompilerEngine;
use Livewire\ComponentConcerns\RendersLivewireComponents;
use Throwable;

class CompilerEngineForIgnition extends CompilerEngine
{
    use RendersLivewireComponents;

    protected function handleViewException(Throwable $e, $obLevel)
    {
        if ($this->shouldBypassExceptionForLivewire($e, $obLevel)) {
            PhpEngine::handleViewException($e, $obLevel);

            return;
        }

        parent::handleViewException($e, $obLevel);
    }
}
