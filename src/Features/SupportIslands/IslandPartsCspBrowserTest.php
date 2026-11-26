<?php

namespace Livewire\Features\SupportIslands;

class IslandPartsCspBrowserTest extends IslandPartsBrowserTest
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['app.debug' => true, 'livewire.csp_safe' => true]);
        };
    }
}
