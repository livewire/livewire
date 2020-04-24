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
        if (version_compare(PHP_VERSION, '7.4') >= 0) {
            return function ($uri, $component = null) {
                $component = $component ?: $uri;

                return $this->get($uri, [LivewireSPAController::class, $component]);
            };
        }

        return function ($uri, $component = null) {
            $component = $component ?: $uri;

            return $this->get($uri, function () use ($component) {
                $componentClass = app('livewire')->getComponentClass($component);
                $reflected = new \ReflectionClass($componentClass);

                return view()->file(__DIR__ . '/livewire-view.blade.php', [
                    'layout' => $this->current()->getAction('layout') ?? 'layouts.app',
                    'section' => $this->current()->getAction('section') ?? 'content',
                    'component' => $component,
                    'componentParameters' => $reflected->hasMethod('mount')
                        ? (new PretendClassMethodIsControllerMethod($reflected->getMethod('mount'), $this))->retrieveBindings()
                        : [],
                ])->with($this->current()->layoutParamsFromLivewire ?? []);
            });
        };
    }
}
