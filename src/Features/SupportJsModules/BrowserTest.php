<?php

namespace Livewire\Features\SupportJsModules;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            app('livewire.finder')->addNamespace('testns', viewPath: __DIR__ . '/fixtures');
        };
    }

    public function test_nested_namespaced_component_loads_js_module()
    {
        // This tests that components with multiple dots in their path
        // (e.g., testns::nested.component.index) correctly load their JS modules.
        // Regression test for: https://github.com/livewire/livewire/discussions/9614
        Livewire::visit('testns::nested.component.index')
            ->waitForLivewireToLoad()
            // Pause for a moment to allow the script to be loaded...
            ->pause(100)
            // If the JS loaded correctly, it will have set the text to 'js-loaded'
            ->assertSeeIn('@target', 'js-loaded');
    }
}
