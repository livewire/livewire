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
            // Pause for a moment to allow the script to be loaded...
            ->pause(100)
            // If the JS loaded correctly, it will have set the text to 'js-loaded'
            ->assertSeeIn('@target', 'js-loaded');
    }

    public function test_single_file_component_js_supports_es_imports()
    {
        // This tests that ES module import statements work in single-file
        // component <script> blocks. The imports should be hoisted above the
        // export function run() wrapper so they remain at the module top level.
        Livewire::visit('testns::sfc-with-imports')
            ->waitForLivewireToLoad()
            ->pause(100)
            ->assertSeeIn('@target', 'js-import-loaded');
    }

    public function test_multi_file_component_js_supports_es_imports()
    {
        // This tests that ES module import statements work in multi-file
        // component .js files. The imports should be hoisted above the
        // export function run() wrapper so they remain at the module top level.
        // Regression test for: https://github.com/livewire/livewire/discussions/10163
        Livewire::visit('testns::mfc-with-imports')
            ->waitForLivewireToLoad()
            ->pause(100)
            ->assertSeeIn('@target', 'js-import-loaded');
    }

    public function test_script_module_survives_a_back_navigation()
    {
        // The `scriptModule` effect is only sent while mounting, so navigating
        // away has to carry it over onto the element: inscribeSnapshotAndEffectsOnElement()
        // rewrites wire:effects and navigate caches that markup for the back button.
        // When the effect is dropped there, the restored component never imports its
        // module and all of its $js actions are gone.
        //
        // Note the assertion is a $js action *call* after coming back, not text the
        // module wrote before navigating away: navigate caches the mutated HTML, so
        // that text is restored either way and would pass without the module loading.
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
            // And it has to survive doing all of that again, so the restored
            // component carries the effect for the next navigation as well...
            ->waitForNavigate()->click('@link')
            ->waitForText('On second page')
            ->back()
            ->waitForText('Mark')
            ->pause(100)
            ->click('@mark-again')
            ->assertSeeIn('@target', 'js-action-called-again');
    }
}
