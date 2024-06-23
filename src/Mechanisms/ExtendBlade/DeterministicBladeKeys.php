<?php

namespace Livewire\Mechanisms\ExtendBlade;

use Illuminate\View\Compilers\BladeCompiler;

class DeterministicBladeKeys
{
    protected $countersByPath = [];

    protected $currentPathHash;

    public function generate()
    {
        if (! $this->currentPathHash) {
            throw new \Exception('Latest compiled component path not found.');
        }

        $path = $this->currentPathHash;
        $count = $this->counter();

        // $key = "lw-[hash of Blade view path]-[current @livewire directive count]"
        return 'lw-' . $this->currentPathHash . '-' . $count;
    }

    public function counter()
    {
        if (! isset($this->countersByPath[$this->currentPathHash])) {
            $this->countersByPath[$this->currentPathHash] = 0;
        }

        return $this->countersByPath[$this->currentPathHash]++;
    }

    public function hookIntoCompile(BladeCompiler $compiler, $viewContent)
    {
        $path = $compiler->getPath();

        // If there is no path this means this Blade is being compiled
        // with ->compileString(...) directly instead of ->compile()
        // therefore we'll generate a hash of the contents instead
        if ($path === null) {
            $path = $viewContent;
        }

        $this->currentPathHash = crc32($path);
    }
}
