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

            $html = app('livewire')->mount($this::class, $params);
        });

        $layoutConfig = $layoutConfig ?: new LayoutConfig;

        $layoutConfig->normalizeViewNameAndParamsForBladeComponents();

        return SupportPageComponents::renderContentsIntoLayout($html, $layoutConfig);
    }
}
