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

                $paramters = $reflected->hasMethod('mount')
                        ? (new PretendClassMethodIsControllerMethod($reflected->getMethod('mount'), $this))->retrieveBindings()
                        : [];

                $lastRenderedLivewireView = null;
                Livewire::listen('view:render', function ($view) use (&$lastRenderedLivewireView) {
                    $lastRenderedLivewireView = $view;
                });

                $response = Livewire::mount($component, $paramters);

                $componentViewData = array_diff_key(
                    $lastRenderedLivewireView->getData(),
                    array_flip(['_instance', 'errors'])
                );

                return app('view')->file(__DIR__.'/livewire-shared-view.blade.php', [
                    'layout' => $this->current()->getAction('layout') ?? 'layouts.app',
                    'section' => $this->current()->getAction('section') ?? 'content',
                    'livewireRenderedContent' => $response->dom,
                ])
                ->with($this->current()->layoutParamsFromLivewire ?? [])
                ->with(Livewire::componentViewDataIsShared() ? $componentViewData : []);
            });
        };
    }
}
