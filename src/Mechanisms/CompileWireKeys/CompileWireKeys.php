<?php

namespace Livewire\Mechanisms\CompileWireKeys;

use Livewire\Mechanisms\Mechanism;

class CompileWireKeys extends Mechanism
{
    function boot()
    {
        app('blade.compiler')->precompiler(new WireKeyCompiler);
    }
}
