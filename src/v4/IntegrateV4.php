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
        $supportedExtensions = ['.blade.php', '.wire.php'];

        $this->finder = new ComponentViewPathResolver(null, $supportedExtensions);
        $this->compiler = new SingleFileComponentCompiler(null, $supportedExtensions);
    }

    public function __invoke()
    {
        $this->supportSingleFileComponents();
    }

    protected function supportSingleFileComponents()
    {
        app('view')->addNamespace('livewire-compiled', storage_path('framework/livewire/views'));

        // Register a missing component resolver with Livewire's component registry
        app('livewire')->resolveMissingComponent(function ($componentName) {
            $viewPath = $this->finder->resolve($componentName);

            $result = $this->compiler->compile($viewPath);

            $className = $result->className;

            // Load the generated class file since it won't be autoloaded
            if (! class_exists($className)) {
                require_once $result->classPath;
            }

            // Double-check that the class now exists after loading
            if (! class_exists($className)) {
                throw new \Exception("Class {$className} does not exist after loading from {$result->classPath}");
            }

            return $className;
        });
    }
}
