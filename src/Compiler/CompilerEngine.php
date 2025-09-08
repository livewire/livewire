<?php

namespace Livewire\Compiler;

class CompilerEngine
{
    public function __construct(
        protected Compiler $compiler,
    ) {}

    public function compileSingleFileComponent(string $path): string
    {
        if ($this->isExpired($path)) {
            // Recompile if source file is newer than cached version
            // @todo: actual compilation logic
        }

        return $this->compiler->compile($path)->className;
    }

    public function compileMultiFileComponent(string $path): string
    {
        if ($this->isExpired($path)) {
            // Recompile if any source files are newer than cached version
            // @todo: actual compilation logic
        }

        return $this->compiler->compileMultiFileComponent($path)->className;
    }

    protected function isExpired(string $path): bool
    {
        return false;
    }
}
