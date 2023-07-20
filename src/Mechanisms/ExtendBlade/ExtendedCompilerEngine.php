<?php

namespace Livewire\Mechanisms\ExtendBlade;

use function Livewire\trigger;

class ExtendedCompilerEngine extends \Illuminate\View\Engines\CompilerEngine {
    public function get($path, array $data = [])
    {
        if (! ExtendBlade::isRenderingLivewireComponent()) return parent::get($path, $data);

        $currentComponent = ExtendBlade::currentRendering();

        trigger('view:compile', $currentComponent, $path);

        return parent::get($path, $data);
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
        } catch (\Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (\Throwable $e) {
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

        parent::handleViewException($e, $obLevel);
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
