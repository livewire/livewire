<?php

namespace Livewire\Mechanisms\ExtendBlade;

use Illuminate\View\Compilers\BladeCompiler;

class DeterministicBladeKeys
{
    protected $countersByPath = [];

    protected $lastPath;

    public function generate()
    {
        if (! $this->lastPath) {
            throw new \Exception('Latest compiled component path not found.');
        }

        $path = $this->lastPath;
        $count = $this->counter();

        // $key = "lw-[hash of Blade view path]-[current @livewire directive count]"
        return 'lw-' . crc32($this->lastPath) . '-' . $count;
    }

    public function counter()
    {
        if (! isset($this->countersByPath[$this->lastPath])) {
            $this->countersByPath[$this->lastPath] = 0;
        }

        return $this->countersByPath[$this->lastPath]++;
    }

    public function interceptCompile(BladeCompiler $compiler)
    {
        $this->lastPath = $compiler->getPath();
    }
}
