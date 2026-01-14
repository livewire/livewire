<?php

namespace Livewire\Features\SupportCssModules;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            app('livewire.finder')->addNamespace('testns', viewPath: __DIR__ . '/fixtures');
        };
    }

    public function test_nested_namespaced_component_loads_css_module()
    {
        // This tests that components with multiple dots in their path
        // (e.g., testns::nested.component.index) correctly load their CSS modules.
        // Regression test for: https://github.com/livewire/livewire/discussions/9614
        Livewire::visit('testns::nested.component.index')
            ->waitForLivewireToLoad()
            // Pause for a moment to allow the stylesheet to be loaded...
            ->pause(100)
            // If the CSS loaded correctly, the element will have red text
            ->assertScript("getComputedStyle(document.querySelector('[dusk=\"target\"]')).color === 'rgb(255, 0, 0)'");
    }
}
