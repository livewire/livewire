<?php

namespace Livewire\Mechanisms\CompileWireKeys;

use Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys;

class WireKeyCompiler
{
    public function __invoke($value)
    {
        $value = SupportCompiledWireKeys::compile($value);

        return $value;
    }
}