<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\PhpEngine;
use Livewire\Exceptions\BypassViewHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait RendersLivewireComponents
{
    protected $livewireComponent;
    protected $isRenderingLivewireComponent = false;

    public function startLivewireRendering($component)
    {
        $this->livewireComponent = $component;
        $this->isRenderingLivewireComponent = true;
    }

    public function endLivewireRendering()
    {
        $this->isRenderingLivewireComponent = false;
    }

    public function setLivewireComponent($component)
    {
        $this->livewireComponent = $component;
    }

    protected function evaluatePath($__path, $__data)
    {
        if (! $this->isRenderingLivewireComponent) {
            return parent::evaluatePath($__path, $__data);
        }

        $obLevel = ob_get_level();

        ob_start();

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            \Closure::bind(function () use ($__path, $__data) {
                extract($__data, EXTR_SKIP);
                include $__path;
            }, $this->livewireComponent ? $this->livewireComponent : $this)();
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (Throwable $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    // Errors thrown while a view is rendering are caught by the Blade
    // compiler and wrapped in an "ErrorException". This makes Livewire errors
    // harder to read, AND causes issues like `abort(404)` not actually working.
    protected function handleViewException(Throwable $e, $obLevel)
    {
        if ($this->shouldBypassExceptionForLivewire($e, $obLevel)) {
            // This is because there is no "parent::parent::".
            PhpEngine::handleViewException($e, $obLevel);

            return;
        }

        CompilerEngine::handleViewException($e, $obLevel);
    }

    public function shouldBypassExceptionForLivewire(Throwable $e, $obLevel)
    {
        $uses = array_flip(class_uses_recursive($e));

        return (
            // Don't wrap "abort(403)".
            $e instanceof AuthorizationException
            // Don't wrap "abort(404)".
            || $e instanceof NotFoundHttpException
            // Don't wrap "abort(500)".
            || $e instanceof HttpException
            // Don't wrap most Livewire exceptions.
            || isset($uses[BypassViewHandler::class])
        );
    }
}
