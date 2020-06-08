<?php

namespace Livewire\Macros;

use Livewire\Livewire;

class RouterMacros
{
    public function layout()
    {
        return function ($layout) {
            return (new RouteRegistrarWithAllowedAttributes($this))
                ->allowAttributes('layout', 'section')
                ->layout($layout);
        };
    }

    public function section()
    {
        return function ($section) {
            return (new RouteRegistrarWithAllowedAttributes($this))
                ->allowAttributes('layout', 'section')
                ->section($section);
        };
    }

    public function livewire()
    {
        return function ($uri, $component = null) {
            $component = $component ?: $uri;

            return $this->get($uri, function () use ($component) {
                $componentClass = app('livewire')->getComponentClass($component);
                $reflected = new \ReflectionClass($componentClass);
                $componentParameters = $reflected->hasMethod('mount')
                    ? (new PretendClassMethodIsControllerMethod($reflected->getMethod('mount'), $this))->retrieveBindings()
                    : [];

                $layout = 'layouts.app'; // $this->current()->getAction('layout') ?? ;
                $layoutParams = [];
                $section = 'content';  //$this->current()->getAction('section') ?? ;

                Livewire::listen('view:rendering', function ($view) use (&$layout, &$layoutParams, &$section) {
                    if ($extends = $view->livewireExtends) {
                        $layout = $extends['view'];
                        $layoutParams = $extends['params'];
                    }

                    if ($section = $view->livewireSection) {
                        $section = $section;
                    }
                });

                $dom = Livewire::mount($component, $componentParameters)->dom;

                $layout = $this->current()->getAction('layout') ?: $layout;
                $layoutParams = $this->current()->layoutParamsFromLivewire ?: $layoutParams;
                $section = $this->current()->getAction('section') ?: $section;

                return app('view')->file(__DIR__.'/livewire-view.blade.php', [
                    'dom' => $dom,
                    'layout' => $layout,
                    'layoutParams' => $layoutParams,
                    'section' => $section,
                ]);
            });
        };
    }
}
