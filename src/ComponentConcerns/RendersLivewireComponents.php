<?php

namespace Livewire\ComponentConcerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\PhpEngine;
use Livewire\Exceptions\BypassViewHandler;
use Livewire\LivewireManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait RendersLivewireComponents
{
    protected $livewireComponents = [];

    public function startLivewireRendering($component)
    {
        $this->livewireComponents[] = $component;
    }

    public function endLivewireRendering()
    {
        array_pop($this->livewireComponents);
    }

    public function isRenderingLivewireComponent()
    {
        return ! empty($this->livewireComponents);
    }
   
    public function get($path, array $data = [])
    {
        if (! $this->isRenderingLivewireComponent()) return parent::get($path, $data); 

        $this->lastCompiled[] = $path;

        // If this given view has expired, which means it has simply been edited since
        // it was last compiled, we will re-compile the views so we can evaluate a
        // fresh copy of the view. We'll pass the compiler the path of the view.
        if ($this->compiler->isExpired($path)) {
            // @note: this is the only modification of this overladed Laravel method:
            // We are globally setting the current view path being compiled for
            // reference from the @livewire Blade directive.
            LivewireManager::$currentCompilingViewPath = $path;
            LivewireManager::$currentCompilingChildCounter = 0;
            
            $this->compiler->compile($path);

            // Here, we'll reset them back, for the next view to be compiled.
            LivewireManager::$currentCompilingViewPath = null;
            LivewireManager::$currentCompilingChildCounter = null;
        }

        // Once we have the path to the compiled file, we will evaluate the paths with
        // typical PHP just like any other templates. We also keep a stack of views
        // which have been rendered for right exception messages to be generated.
        $results = $this->evaluatePath($this->compiler->getCompiledPath($path), $data);

        array_pop($this->lastCompiled);

        return $results;
    }

    protected function evaluatePath($__path, $__data)
    {
        if (! $this->isRenderingLivewireComponent()) {
            return parent::evaluatePath($__path, $__data);
        }

        $obLevel = ob_get_level();

        ob_start();

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            $component = end($this->livewireComponents);
            \Closure::bind(function () use ($__path, $__data) {
                extract($__data, EXTR_SKIP);
                include $__path;
            }, $component, $component)();
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
