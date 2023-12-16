<?php

namespace Livewire\Features\SupportPageComponents;

use Livewire\Attributes\Layout;

trait HandlesPageComponents
{
    function __invoke()
    {
        // Here's we're hooking into the "__invoke" method being called on a component.
        // This way, users can pass Livewire components into Routes as if they were
        // simple invokable controllers. Ex: Route::get('...', SomeLivewireComponent::class);
        $html = null;
        $layout = null;

        $layoutConfig = SupportPageComponents::interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration(function () use (&$html) {
            $params = SupportPageComponents::gatherMountMethodParamsFromRouteParameters($this);

            $html = app('livewire')->mount($this::class, $params);
        });

        $reflectionClass = new \ReflectionClass($this);
        $layout = collect($reflectionClass->getAttributes(Layout::class))->first()?->getArguments()[0];

        $layoutConfig = $layoutConfig ?: new PageComponentConfig(view: $layout);

        $layoutConfig->normalizeViewNameAndParamsForBladeComponents();

        $response = response(SupportPageComponents::renderContentsIntoLayout($html, $layoutConfig));

        if (is_callable($layoutConfig->response)) {
            call_user_func($layoutConfig->response, $response);
        }

        return $response;
    }
}
