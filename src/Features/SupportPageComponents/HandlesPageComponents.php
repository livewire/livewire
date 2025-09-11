<?php

namespace Livewire\Features\SupportPageComponents;

trait HandlesPageComponents
{
    function __invoke()
    {
        // Here's we're hooking into the "__invoke" method being called on a component.
        // This way, users can pass Livewire components into Routes as if they were
        // simple invokable controllers. Ex: Route::get('...', SomeLivewireComponent::class);
        $html = null;

        $layoutConfig = SupportPageComponents::interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration(function () use (&$html) {
            $params = SupportPageComponents::gatherMountMethodParamsFromRouteParameters($this);

            $name = $this->getName() !== null ? $this->getName() : $this::class;

            $html = app('livewire')->mount($name, $params);
        });

        $layoutConfig = $layoutConfig ?: new PageComponentConfig;

        $layoutConfig->normalizeViewNameAndParamsForBladeComponents();

        $response = response(SupportPageComponents::renderContentsIntoLayout($html, $layoutConfig));

        if (is_callable($layoutConfig->response)) {
            call_user_func($layoutConfig->response, $response);
        }

        return $response;
    }
}
