<?php

namespace Livewire\Features\SupportIslands;

class IslandPartsMinifiedBrowserTest extends IslandPartsBrowserTest
{
    public static function tweakApplicationHook()
    {
        return function () {
            config(['app.debug' => false]);
        };
    }
}
