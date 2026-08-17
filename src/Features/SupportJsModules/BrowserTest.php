<?php

namespace Livewire\Features\SupportJsModules;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public static function tweakApplicationHook()
    {
        return function () {
            app('livewire.finder')->addNamespace('testns', viewPath: __DIR__ . '/fixtures');

            Route::get('/test-module.js', function () {
                return response("export let greeting = 'js-import-loaded'", 200, [
                    'Content-Type' => 'application/javascript',
                ]);
            });
        };
    }

    public function test_nested_namespaced_component_loads_js_module()
    {
        // This tests that components with multiple dots in their path
        // (e.g., testns::nested.component.index) correctly load their JS modules.
        // Regression test for: https://github.com/livewire/livewire/discussions/9614
        Livewire::visit('testns::nested.component.index')
            ->waitForLivewireToLoad()
            // If the JS loaded correctly, it will have set the text to 'js-loaded'
            ->waitForTextIn('@target', 'js-loaded');
    }

    public function test_single_file_component_js_supports_es_imports()
    {
        // This tests that ES module import statements work in single-file
        // component <script> blocks. The imports should be hoisted above the
        // export function run() wrapper so they remain at the module top level.
        Livewire::visit('testns::sfc-with-imports')
            ->waitForLivewireToLoad()
            ->waitForTextIn('@target', 'js-import-loaded');
    }

    public function test_js_actions_can_call_other_js_actions()
    {
        // The $js object passed into a component's script module is created
        // before the script's own $js functions are registered. Reads must
        // consult the component's live action registry so that one $js
        // function can call another (e.g. $js.outer() calling $js.inner()).
        // Regression test for $js actions not seeing each other in SFC scripts.
        Livewire::visit('testns::sfc-with-js-actions')
            ->waitForLivewireToLoad()
            ->waitForTextIn('@target', 'js-actions-composed');
    }

    public function test_multi_file_component_js_supports_es_imports()
    {
        // This tests that ES module import statements work in multi-file
        // component .js files. The imports should be hoisted above the
        // export function run() wrapper so they remain at the module top level.
        // Regression test for: https://github.com/livewire/livewire/discussions/10163
        Livewire::visit('testns::mfc-with-imports')
            ->waitForLivewireToLoad()
            ->waitForTextIn('@target', 'js-import-loaded');
    }

    public function test_script_module_survives_a_back_navigation()
    {
        Livewire::visit('testns::back-forward')
            ->waitForLivewireToLoad()
            ->pause(100)
            ->assertSeeIn('@loaded', 'js-loaded')
            ->waitForNavigate()->click('@link')
            ->waitForText('On second page')
            ->back()
            ->waitForText('Mark')
            ->pause(100)
            ->click('@mark')
            ->assertSeeIn('@target', 'js-action-called')
            ->waitForNavigate()->click('@link')
            ->waitForText('On second page')
            ->back()
            ->waitForText('Mark')
            ->pause(100)
            ->click('@mark-again')
            ->assertSeeIn('@target', 'js-action-called-again');
    }
}
