<?php

namespace Livewire\Features\SupportJsModules;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
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

            Route::get('/slow-slot-module.js', function () {
                usleep(2_000_000);

                return response("export let greeting = 'slot-alpine-data-loaded'", 200, [
                    'Content-Type' => 'application/javascript',
                ]);
            });

            Route::get('/alpine-data-page', function () {
                return app('livewire')->new('testns::alpine-data.index')();
            })->middleware('web');

            Route::get('/alpine-data-page-2', function () {
                return app('livewire')->new('testns::alpine-data.index')();
            })->middleware('web');
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

    public function test_alpine_data_is_available_during_initial_component_initialization()
    {
        Livewire::visit('testns::alpine-data.index')
            ->waitForLivewireToLoad()
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertSeeIn('@js-action', 'js-action-loaded')
            ->assertSeeIn('@js-action-state', 'ready')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_is_available_when_a_lazy_component_hydrates()
    {
        Livewire::visit('testns::lazy-with-alpine-data')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_is_available_when_a_component_is_added_dynamically()
    {
        Livewire::visit([new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>

                    @if ($show)
                        <livewire:testns::alpine-data.index />
                    @endif
                </div>
                HTML;
            }
        }])
            ->assertDontSee('alpine-data-loaded')
            ->waitForLivewire()->click('@toggle')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_is_available_in_a_slot_forwarded_through_a_lazy_component()
    {
        Livewire::visit('testns::lazy-with-alpine-data-in-slot.parent')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_is_available_when_an_existing_child_morphs_forwarded_slot_content()
    {
        Livewire::visit('testns::lazy-slot-with-existing-wrapper.parent')
            ->assertSeeIn('@target', 'Loading...')
            ->waitForTextIn('@target', 'slot-alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_is_available_in_a_component_added_to_an_island()
    {
        Livewire::visit('testns::island-with-alpine-data')
            ->assertSeeIn('@placeholder', 'No child yet')
            ->waitForLivewire()->click('@toggle')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_is_available_after_wire_navigate()
    {
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="source-page">Source page</div>
                    <a href="/alpine-data-page" wire:navigate dusk="link">Go to alpine data page</a>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@source-page', 'Source page')
            ->waitForNavigate()->click('@link')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_module_remains_available_across_wire_navigate()
    {
        Livewire::visit('testns::navigate-with-alpine-data')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->waitForNavigate()->click('@link')
            ->waitUntilMissing('@first-page')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_component_initialization_recovers_when_a_script_module_fails_to_import()
    {
        Livewire::visit('testns::failed-module')
            ->waitForTextIn('@ready', 'ready')
            ->waitForLivewire()->click('@increment')
            ->assertSeeIn('@count', '1');
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
