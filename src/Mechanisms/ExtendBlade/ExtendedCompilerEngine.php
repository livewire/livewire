<?php

namespace Livewire\Mechanisms\ExtendBlade;

use function Livewire\trigger;
use Illuminate\View\View;

class ExtendedCompilerEngine extends \Illuminate\View\Engines\CompilerEngine {
    protected $viewPathStack = [];

    public function get($path, array $data = [])
    {
        $this->viewPathStack[] = $path;

        try {
            if (! ExtendBlade::isRenderingLivewireComponent()) return parent::get($path, $data);

            $currentComponent = ExtendBlade::currentRendering();

            trigger('view:compile', $currentComponent, $path);

            return parent::get($path, $data);
        } finally {
            array_pop($this->viewPathStack);
        }
    }

    protected function evaluatePath($__path, $__data)
    {
        if (! ExtendBlade::isRenderingLivewireComponent()) {
            return parent::evaluatePath($__path, $__data);
        }

        $obLevel = ob_get_level();

        ob_start();

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            $component = ExtendBlade::currentRendering();

            \Closure::bind(function () use ($__path, $__data) {
                extract($__data, EXTR_SKIP);
                include $__path;
            }, $component, $component)();
        } catch (\Exception|\Throwable $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    // Errors thrown while a view is rendering are caught by the Blade
    // compiler and wrapped in an "ErrorException". This makes Livewire errors
    // harder to read, AND causes issues like `abort(404)` not actually working.
    protected function handleViewException(\Throwable $e, $obLevel)
    {
        if ($this->shouldBypassExceptionForLivewire($e, $obLevel)) {
            // This is because there is no "parent::parent::".
            \Illuminate\View\Engines\PhpEngine::handleViewException($e, $obLevel);

            return;
        }

        // Enhance exception message with component context before wrapping
        if (ExtendBlade::isRenderingLivewireComponent() && ! str_contains($e->getMessage(), '(Component:')) {
            $component = ExtendBlade::currentRendering();

            if ($component) {
                try {
                    $componentName = $component->getName();
                    $renderStack = \Livewire\Mechanisms\HandleComponents\HandleComponents::$renderStack ?? [];

                    // Try to resolve the original view path
                    $viewPath = null;

                    // 1. Try getting it from the currently rendering View object
                    if (method_exists(ExtendBlade::class, 'currentRenderingView')) {
                        $currentView = ExtendBlade::currentRenderingView();
                        if ($currentView instanceof View) {
                            $viewPath = $currentView->getPath();
                        }
                    }

                    // 2. Fallback to the compiled path if nothing else
                    if (! $viewPath) {
                        $viewPath = end($this->viewPathStack);
                    }

                    // 3. Check if it's a storage path (compiled view)
                    $isStoragePath = $viewPath && function_exists('storage_path') && str_starts_with($viewPath, storage_path());

                    if ($isStoragePath) {
                        try {
                            // A) Try Reflection (for Volt / Class-based components)
                            $reflection = new \ReflectionClass($component);
                            $classFile = $reflection->getFileName();

                            if ($classFile && (str_contains($classFile, 'app') || str_contains($classFile, 'resources'))) {
                                $viewPath = $classFile;
                            } else {
                                // B) Try guessing the view path based on configuration
                                $guesses = [];
                                $componentPath = str_replace('.', '/', $componentName);

                                // Check configured view_path (Standard Livewire)
                                $livewireViewPath = config('livewire.view_path') ?: resource_path('views/livewire');
                                $guesses[] = $livewireViewPath . '/' . $componentPath . '.blade.php';

                                // Check configured component_locations (Volt / Functional)
                                $componentLocations = config('livewire.component_locations') ?: [resource_path('views/components')];
                                foreach ($componentLocations as $location) {
                                    // Standard: components/foo.blade.php
                                    $guesses[] = $location . '/' . $componentPath . '.blade.php';

                                    // Nested/Custom: components/foo/foo.blade.php
                                    if (str_contains($componentName, '.')) {
                                        $pathParts = explode('.', $componentName);
                                        $name = end($pathParts);
                                        $path = implode('/', $pathParts);
                                        $guesses[] = $location . '/' . $path . '/' . $name . '.blade.php';
                                    }
                                }

                                foreach ($guesses as $guess) {
                                    if (file_exists($guess)) {
                                        $viewPath = $guess;
                                        break;
                                    }
                                }
                            }
                        } catch (\Throwable $r) {}
                    }

                    // Format the path relative to base_path
                    $relativeViewPath = $viewPath;
                    if ($viewPath && function_exists('base_path') && str_contains($viewPath, base_path())) {
                        $relativeViewPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $viewPath);
                    }

                    $componentContext = '';
                    if (count($renderStack) > 1) {
                        $componentNames = array_map(fn($c) => $c->getName(), $renderStack);
                        $hierarchy = implode(' -> ', $componentNames);
                        $componentContext = " (Component: [{$hierarchy}])";
                    } else {
                        $componentContext = " (Component: [{$componentName}])";
                    }

                    if ($relativeViewPath) {
                        // Use a slightly different label if we resolved it to the class file
                        $label = (str_ends_with($relativeViewPath, '.php') && !str_ends_with($relativeViewPath, '.blade.php'))
                            ? 'Class'
                            : 'View';

                        $componentContext = " ({$label}: {$relativeViewPath})" . $componentContext;
                    }

                    // Create new exception with enhanced message
                    $severity = ($e instanceof \ErrorException) ? $e->getSeverity() : \E_ERROR;
                    $e = new \ErrorException(
                        $e->getMessage() . $componentContext,
                        0,
                        $severity,
                        $e->getFile(),
                        $e->getLine(),
                        $e
                    );
                } catch (\Throwable $componentException) {
                    // If we can't get component name, continue with original exception
                }
            }
        }

        parent::handleViewException($e, $obLevel);
    }

    /**
     * Get the exception message for an exception.
     *
     * @param  \Throwable  $e
     * @return string
     */
    protected function getMessage(\Throwable $e)
    {
        return parent::getMessage($e);
    }

    public function shouldBypassExceptionForLivewire(\Throwable $e, $obLevel)
    {
        $uses = array_flip(class_uses_recursive($e));

        return (
            // Don't wrap "abort(403)".
            $e instanceof \Illuminate\Auth\Access\AuthorizationException
            // Don't wrap "abort(404)".
            || $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
            // Don't wrap "abort(500)".
            || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
            // Don't wrap most Livewire exceptions.
            || isset($uses[\Livewire\Exceptions\BypassViewHandler::class])
        );
    }
}
