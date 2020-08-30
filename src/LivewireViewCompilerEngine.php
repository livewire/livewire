<?php

namespace Livewire;

use Exception;
use Throwable;

class LivewireViewCompilerEngine extends CompilerEngine
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
            \Closure::bind(function() use($__path, $__data) {
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
}
