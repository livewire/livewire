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

    public function test_alpine_data_works_in_single_file_component_script()
    {
        // This tests that Alpine.data() registrations inside SFC <script> tags
        // work correctly. The script module is pre-loaded before Alpine starts,
        // so Alpine.data() is registered before x-data attributes are evaluated.
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
            ->waitFor('@target')
            ->assertSeeIn('@target', 'alpine-data-loaded')
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
        // from the first page and reused on the second without re-importing.
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
