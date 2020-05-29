<?php

namespace Livewire\Macros;

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

                // the default is catched by the route action "layout" or the fallback.
                // This can be overwritten from the layout function of a component.
                $layout = $this->current()->getAction('layout') ?? 'layouts.app';

                return app('view')->file(__DIR__.'/livewire-view.blade.php', [
                    'layout' => app('livewire')->getLayout($component) ?? $layout,
                    'section' => $this->current()->getAction('section') ?? 'content',
                    'layout_params' => app('livewire')->getLayoutParams($component),
                    'component' => $component,
                    'componentParameters' => $reflected->hasMethod('mount')
                        ? (new PretendClassMethodIsControllerMethod($reflected->getMethod('mount'), $this))->retrieveBindings()
                        : [],
                ])->with($this->current()->layoutParamsFromLivewire ?? []);
            });
        };
    }
}
