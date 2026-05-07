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

    public function test_component_with_slashes_in_name_loads_css_module()
    {
        // Regression test for https://github.com/livewire/livewire/issues/10261
        // When a child is referenced via dynamic-component using slash-notation
        // (e.g. "testns::a/⚡b/⚡c"), the resulting component name carries those
        // slashes. Without the route accepting them, the CSS URL 404s silently.
        Livewire::visit('testns::slash-notation.parent')
            ->waitForLivewireToLoad()
            ->pause(100)
            ->assertScript("getComputedStyle(document.querySelector('[dusk=\"target\"]')).color === 'rgb(0, 128, 0)'");
    }
}
