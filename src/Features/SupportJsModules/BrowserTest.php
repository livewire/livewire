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
            // Pause for a moment to allow the script to be loaded...
            ->pause(100)
            // If the JS loaded correctly, it will have set the text to 'js-loaded'
            ->assertSeeIn('@target', 'js-loaded')
            ->assertConsoleLogHasNoErrors();
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

    public function test_alpine_data_works_in_single_file_component_script()
    {
        // Regression test for: https://github.com/livewire/livewire/discussions/9591
        Livewire::visit('testns::alpine-data.index')
            ->waitForLivewireToLoad()
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_in_dynamically_added_component()
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
            ->assertConsoleLogHasNoErrors()
            ->assertDontSee('alpine-data-loaded')
            ->waitForLivewire()->click('@toggle')
            ->waitFor('@target')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_in_lazy_loaded_component()
    {
        Livewire::visit('testns::lazy-with-alpine-data')
            ->waitForText('alpine-data-loaded')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_in_slot_forwarded_to_child_of_lazy_component()
    {
        // Regression test for: the parent (not the child wrapping it) owns the
        // script module here. The `x-data` element sits inside a slot physically
        // rendered inside the child's DOM, so component lookups involved in
        // deferring Alpine init need to resolve slot ownership correctly rather
        // than just the nearest `wire:id` ancestor.
        Livewire::visit('testns::lazy-with-alpine-data-in-slot.parent')
            ->waitForText('alpine-data-loaded')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_in_lazy_slot_when_existing_child_wrapper_morphs_forwarded_content()
    {
        Livewire::visit('testns::lazy-slot-with-existing-wrapper.parent')
            ->assertSeeIn('@target', 'Loading...')
            ->waitForText('slot-alpine-data-loaded')
            ->assertSeeIn('@target', 'slot-alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_in_component_inside_island()
    {
        Livewire::visit('testns::island-with-alpine-data')
            ->assertConsoleLogHasNoErrors()
            ->assertSeeIn('@placeholder', 'No child yet')
            ->assertDontSee('alpine-data-loaded')
            ->waitForLivewire()->click('@toggle')
            ->waitFor('@target')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_after_wire_navigate()
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
            ->assertConsoleLogHasNoErrors()
            ->click('@link')
            ->waitFor('@target')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_module_persists_across_wire_navigate()
    {
        // The alpine-data component is on both pages. The module should be cached
        // by the browser and reused on the second page without re-importing.
        Livewire::visit('testns::navigate-with-alpine-data')
            ->waitForLivewireToLoad()
            ->assertSeeIn('@first-page', 'First page')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors()
            ->click('@link')
            ->waitUntilMissing('@first-page')
            ->waitFor('@target')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }
}
