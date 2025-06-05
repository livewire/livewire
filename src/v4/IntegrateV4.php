<?php

namespace Livewire\V4;

use Livewire\V4\Registry\ComponentViewPathResolver;
use Livewire\V4\Compiler\SingleFileComponentCompiler;
use Livewire\V4\Compiler\Exceptions\CompilationException;
use Livewire\V4\Compiler\Exceptions\ParseException;
use Livewire\V4\Compiler\Exceptions\InvalidComponentException;

class IntegrateV4
{
    protected SingleFileComponentCompiler $compiler;
    protected ComponentViewPathResolver $finder;

    public function __construct()
    {
        $this->finder = new ComponentViewPathResolver();
        $this->compiler = new SingleFileComponentCompiler();
    }

    public function __invoke()
    {
        $this->supportSingleFileComponents();
    }

    protected function supportSingleFileComponents()
    {
        // Register a missing component resolver with Livewire's component registry
        app('livewire')->resolveMissingComponent(function ($componentName) {
            $viewPath = $this->finder->resolve($componentName);

            $result = $this->compiler->compile($viewPath);

            $className = $result->className;

            if (! class_exists($className)) {
                throw new \Exception("Class {$className} does not exist");
            }

            return new $className;
        });
    }

}
