<?php

namespace Livewire\Mechanisms\HijackBlade;

class HijackedCompilerEngine extends \Illuminate\View\Engines\CompilerEngine {
    public function get($path, array $data = [])
    {
        if (! HijackBlade::isRenderingLivewireComponent()) return parent::get($path, $data);

        $currentComponent = HijackBlade::currentRendering();

        app('synthetic')->trigger('view:compile', $currentComponent, $path);

        return parent::get($path, $data);
    }

    protected function evaluatePath($path, $data)
    {
        if (! HijackBlade::isRenderingLivewireComponent()) {
            return parent::evaluatePath($path, $data);
        }

        $obLevel = ob_get_level();

        ob_start();

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        try {
            $component = HijackBlade::currentRendering();

            \Closure::bind(function () use ($path, $data) {
                extract($data, EXTR_SKIP);
                include $path;
            }, $component, $component)();
        } catch (\Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (\Throwable $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }
}
