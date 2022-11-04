<?php

namespace Livewire\Features\SupportPageComponents;

trait HandlesPageComponents
{
    function __invoke()
    {
        SupportPageComponents::$isPageComponentRequest = true;

        // Here's we're hooking into the "__invoke" method being called on a component.
        // This way, users can pass Livewire components into Routes as if they were
        // simple invokable controllers. Ex: Route::get('...', SomeLivewireComponent::class);
        $html = null;

        $layoutConfig = SupportPageComponents::getInstance()->interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration(function () use (&$html) {
            $params = SupportPageComponents::getInstance()->gatherMountMethodParamsFromRouteParameters($this);

            [$html] = app('livewire')->mount($this::class, $params);
        });

        $layoutConfig = SupportPageComponents::getInstance()->mergeLayoutDefaults($layoutConfig);

        return SupportPageComponents::getInstance()->renderContentsIntoLayout($html, $layoutConfig);
    }
}
