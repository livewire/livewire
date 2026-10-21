<?php

namespace Livewire\Features\SupportIslands;

class IslandPartsCspMinifiedBrowserTest extends IslandPartsBrowserTest
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['app.debug' => false, 'livewire.csp_safe' => true]);
        };
    }
}
