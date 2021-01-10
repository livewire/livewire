<?php

namespace Livewire;

use Illuminate\View\Engines\PhpEngine;
use Facade\Ignition\Views\Engines\CompilerEngine;
use Livewire\ComponentConcerns\RendersLivewireComponents;
use Throwable;

class CompilerEngineForIgnition extends CompilerEngine
{
    use RendersLivewireComponents;

    protected function handleViewException(Throwable $e, $obLevel)
    {
        if ($this->shouldBypassExceptionForLivewire($e, $obLevel)) {
            (new PhpEngine($this->files))->handleViewException($e, $obLevel);

            return;
        }

        parent::handleViewException($e, $obLevel);
    }
}
