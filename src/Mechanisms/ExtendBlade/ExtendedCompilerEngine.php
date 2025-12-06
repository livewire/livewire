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

    protected function handleViewException(\Throwable $e, $obLevel)
    {
        // Enhance Livewire exceptions (like PropertyNotFoundException) even if they bypass view handler
        // This ensures they include component context and view path
        $shouldBypass = $this->shouldBypassExceptionForLivewire($e, $obLevel);

        // Prevent duplicate enhancement when exception bubbles up through nested views
        if ($this->isExceptionAlreadyEnhanced($e)) {
            if ($shouldBypass) {
                \Illuminate\View\Engines\PhpEngine::handleViewException($e, $obLevel);
                return;
            }
            parent::handleViewException($e, $obLevel);
            return;
        }

        if (ExtendBlade::isRenderingLivewireComponent()) {
            $component = ExtendBlade::currentRendering();

            if ($component) {
                try {
                    $componentName = $component->getName();
                    $renderStack = \Livewire\Mechanisms\HandleComponents\HandleComponents::$renderStack ?? [];

                    // Resolve original view path (not the compiled storage path)
                    $viewPath = null;

                    if (method_exists(ExtendBlade::class, 'currentRenderingView')) {
                        $currentView = ExtendBlade::currentRenderingView();
                        if ($currentView instanceof View) {
                            $viewPath = $currentView->getPath();
                        }
                    }

                    if (! $viewPath) {
                        $viewPath = end($this->viewPathStack);
                    }

                    // If it's a compiled storage path, try to resolve the original source file
                    $isStoragePath = $viewPath && function_exists('storage_path') && str_starts_with($viewPath, storage_path());

                    if ($isStoragePath) {
                        try {
                            // Try Reflection first (works for Volt/Class-based components)
                            $reflection = new \ReflectionClass($component);
                            $classFile = $reflection->getFileName();

                            if ($classFile && (str_contains($classFile, 'app') || str_contains($classFile, 'resources'))) {
                                $viewPath = $classFile;
                            } else {
                                // Fallback: guess based on component name and configured locations
                                $guesses = [];
                                $componentPath = str_replace('.', '/', $componentName);

                                $livewireViewPath = config('livewire.view_path') ?: resource_path('views/livewire');
                                $guesses[] = $livewireViewPath . '/' . $componentPath . '.blade.php';

                                $componentLocations = config('livewire.component_locations') ?: [resource_path('views/components')];
                                foreach ($componentLocations as $location) {
                                    $guesses[] = $location . '/' . $componentPath . '.blade.php';

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

                    $relativeViewPath = $viewPath;
                    if ($viewPath && function_exists('base_path') && str_contains($viewPath, base_path())) {
                        $relativeViewPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $viewPath);
                    }

                    // Build component hierarchy: [parent -> child] for nested, [name] for single
                    $componentContext = '';
                    if (count($renderStack) > 1) {
                        $componentNames = array_map(fn($c) => $c->getName(), $renderStack);
                        $hierarchy = implode(' -> ', $componentNames);
                        $componentContext = " (Component: [{$hierarchy}])";
                    } else {
                        $componentContext = " (Component: [{$componentName}])";
                    }

                    if ($relativeViewPath) {
                        $label = (str_ends_with($relativeViewPath, '.php') && !str_ends_with($relativeViewPath, '.blade.php'))
                            ? 'Class'
                            : 'View';

                        $componentContext = " ({$label}: {$relativeViewPath})" . $componentContext;
                    }

                    $originalMessage = $e->getMessage();

                    // For exceptions that bypass view handler, enhance message but keep original type
                    if ($shouldBypass) {
                        // Use reflection to set protected message property while preserving exception type
                        $reflection = new \ReflectionClass($e);
                        $messageProperty = $reflection->getProperty('message');
                        $messageProperty->setAccessible(true);
                        $messageProperty->setValue($e, $originalMessage . $componentContext);
                    } else {
                        // For regular exceptions, wrap in ErrorException
                        $severity = ($e instanceof \ErrorException) ? $e->getSeverity() : \E_ERROR;
                        $e = new \ErrorException(
                            $originalMessage . $componentContext,
                            0,
                            $severity,
                            $e->getFile(),
                            $e->getLine(),
                            $e
                        );
                    }
                } catch (\Throwable $componentException) {
                }
            }
        }

        // If this exception should bypass view handler, use PhpEngine instead of parent
        if ($shouldBypass) {
            \Illuminate\View\Engines\PhpEngine::handleViewException($e, $obLevel);
            return;
        }

        parent::handleViewException($e, $obLevel);
    }

    /**
     * Check if an exception (or any previous exception in the chain) has already been enhanced.
     *
     * When exceptions bubble up through nested views, handleViewException() is called at each level.
     * This prevents duplicate enhancement by checking the entire exception chain.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    protected function isExceptionAlreadyEnhanced(\Throwable $e): bool
    {
        $exceptionToCheck = $e;
        while ($exceptionToCheck) {
            $message = $exceptionToCheck->getMessage();
            if (str_contains($message, '(Component:')
                || str_contains($message, '(View:')
                || str_contains($message, '(Class:')) {
                return true;
            }
            $exceptionToCheck = $exceptionToCheck->getPrevious();
        }

        return false;
    }

    /**
     * Override to prevent parent CompilerEngine from adding duplicate (View: ...).
     *
     * The parent's handleViewException() wraps exceptions and calls getMessage(), which always
     * adds (View: ...). When exceptions bubble up through nested views, this causes duplicates.
     *
     * @param  \Throwable  $e
     * @return string
     */
    protected function getMessage(\Throwable $e)
    {
        if ($this->isExceptionAlreadyEnhanced($e)) {
            return $e->getMessage();
        }

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
