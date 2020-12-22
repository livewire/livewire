<?php

namespace Livewire;

use Exception;
use Throwable;
use Illuminate\View\Engines\CompilerEngine;
use Livewire\ComponentConcerns\RendersLivewireComponents;

class LivewireViewCompilerEngine extends CompilerEngine
{
    use RendersLivewireComponents;
}
