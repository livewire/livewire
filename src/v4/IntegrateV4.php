<?php

namespace Livewire\V4;

use Illuminate\Support\Facades\Blade;
use Livewire\V4\Registry\ComponentViewPathResolver;
use Livewire\V4\Compiler\SingleFileComponentCompiler;

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
        $this->supportWireTagSyntax();
        $this->registerSlotDirectives();
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

    protected function supportWireTagSyntax()
    {
        app('blade.compiler')->precompiler(function ($string) {
            return app(WireTagCompiler::class)($string);
        });
    }

    protected function registerSlotDirectives()
    {
        Blade::directive('wireSlot', function ($expression) {
            return "<?php
                ob_start();
                \$__slotName = {$expression};
                \$__slotAttributes = func_num_args() > 1 ? func_get_arg(1) : [];
                \$__previousSlotName = \$__slotName ?? null;

                // Track slot stack for nesting support
                \$__slotStack = \$__slotStack ?? [];
                array_push(\$__slotStack, \$__previousSlotName);
            ?>";
        });

        Blade::directive('endWireSlot', function () {
            return "<?php
                \$__slotContent = ob_get_clean();
                \$__slots = \$__slots ?? [];
                \$__slots[\$__slotName] = \$__slotContent;

                // Store on parent component for subsequent render tracking
                if (isset(\$_instance) && \$_instance instanceof \Livewire\Component) {
                    \$_instance->trackSlotForSubsequentRenders(\$__slotName, \$__slotContent);
                }

                // Restore previous slot name from stack for nesting
                \$__slotName = array_pop(\$__slotStack);
            ?>";
        });
    }
}
