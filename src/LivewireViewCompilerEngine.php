<?php

namespace Livewire;

use Illuminate\View\Engines\CompilerEngine;
use Livewire\ComponentConcerns\RendersLivewireComponents;

class LivewireViewCompilerEngine extends CompilerEngine
{
    use RendersLivewireComponents;
}
