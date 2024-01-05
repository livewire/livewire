<?php

namespace Livewire\Mechanisms\CompileLivewireTags;

use Livewire\Mechanisms\Mechanism;

class CompileLivewireTags extends Mechanism
{
    public function boot()
    {
        app('blade.compiler')->precompiler(new LivewireTagPrecompiler);
    }
}
