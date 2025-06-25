<?php

namespace Livewire\V4;

use Livewire\V4\Tailwind\Merge;
use Livewire\V4\Slots\SupportSlots;
use Livewire\V4\Registry\ComponentViewPathResolver;
use Livewire\V4\Compiler\SingleFileComponentCompiler;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;

class IntegrateV4
{
    protected SingleFileComponentCompiler $compiler;
    protected ComponentViewPathResolver $finder;

    public function __construct()
    {
        app()->alias(ComponentViewPathResolver::class, 'livewire.resolver');
        app()->singleton(ComponentViewPathResolver::class);
        $this->finder = app('livewire.resolver');

        $this->compiler = app(SingleFileComponentCompiler::class);
    }

    public function __invoke()
    {
        $this->supportRoutingMacro();
        $this->supportSingleFileComponents();
        $this->supportWireTagSyntax();
        $this->supportTailwindMacro();
        $this->registerSlotDirectives();
        $this->registerSlotsSupport();
    }

    protected function supportRoutingMacro()
    {
        Route::macro('livewire', function ($uri, $view) {
            if (class_exists($view)) {
                return Route::get($uri, $view);
            }

            return app('livewire')->route($uri, $view);
        });
    }

    protected function supportSingleFileComponents()
    {
        app('view')->addNamespace('livewire-compiled', storage_path('framework/views/livewire/views'));

        app('view')->addNamespace('pages', resource_path('views/pages'));
        app('view')->addNamespace('layouts', resource_path('views/layouts'));

        app('livewire')->namespace('pages', resource_path('views/pages'));

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
        app('blade.compiler')->prepareStringsForCompilationUsing(function ($string) {
            return app(WireTagCompiler::class)($string);
        });
    }

    protected function supportTailwindMacro()
    {
        ComponentAttributeBag::macro('tailwind', function ($weakClasses) {
            $strongClasses = $this->attributes['class'] ?? '';

            $weakClasses = is_array($weakClasses) ? implode(' ', $weakClasses) : $weakClasses;

            $this->attributes['class'] = app(Merge::class)->merge($weakClasses, $strongClasses);

            return $this;
        });
    }

    protected function registerSlotDirectives()
    {
        Blade::directive('wireSlot', function ($expression) {
            return "<?php
                ob_start();
                \$__slotName = {$expression};
                // \$__slotAttributes = func_num_args() > 1 ? func_get_arg(1) : [];
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

                // Restore previous slot name from stack for nesting
                \$__slotName = array_pop(\$__slotStack);
            ?>";
        });
    }

    protected function registerSlotsSupport()
    {
        // app('livewire')->componentHook(SupportSlots::class);
    }
}
