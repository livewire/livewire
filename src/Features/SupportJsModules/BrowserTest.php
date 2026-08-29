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

            // A module that stays pending until the test releases it. Gating
            // client-side keeps the (single-threaded) test server free to
            // handle Livewire updates while the module is "loading"...
            Route::get('/slow-module.js', function () {
                return response(
                    'await new Promise(resolve => { window.releaseSlowModule = resolve })' . "\n\n" . 'export default true',
                    200,
                    ['Content-Type' => 'application/javascript'],
                );
            });

            Route::get('/navigate-page', function () {
                return app('livewire')->new('testns::navigate-page')();
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

    public function test_alpine_data_works_in_single_file_component_script()
    {
        // Alpine walks the DOM synchronously while script modules load
        // asynchronously. The component's tree must be suspended until its
        // module has registered Alpine.data() providers, or `x-data` on the
        // component's own root evaluates against nothing.
        // Regression test for: https://github.com/livewire/livewire/discussions/9591
        Livewire::visit('testns::alpine-data')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_keeps_working_after_a_morph()
    {
        Livewire::visit('testns::alpine-data')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->waitForLivewire()->click('@refresh')
            ->assertSeeIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_with_two_instances_on_one_page()
    {
        // The first instance is the one that historically failed: later
        // instances only worked because the first one's module had already
        // registered the shared Alpine.data() name globally.
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <livewire:testns::alpine-data key="first" />
                    <livewire:testns::alpine-data key="second" />
                </div>
                HTML;
            }
        }])
            ->waitUntil("[...document.querySelectorAll('[dusk=\"target\"]')].filter(el => el.textContent === 'alpine-data-loaded').length === 2")
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
                        <livewire:testns::alpine-data />
                    @endif
                </div>
                HTML;
            }
        }])
            ->assertConsoleLogHasNoErrors()
            ->assertDontSee('alpine-data-loaded')
            ->waitForLivewire()->click('@toggle')
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_in_lazy_loaded_component()
    {
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <livewire:testns::alpine-data lazy />
                </div>
                HTML;
            }
        }])
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_alpine_data_works_in_component_inside_island()
    {
        Livewire::visit('testns::island-with-alpine-data')
            ->assertConsoleLogHasNoErrors()
            ->assertSeeIn('@placeholder', 'No child yet')
            ->assertDontSee('alpine-data-loaded')
            ->waitForLivewire()->click('@toggle')
            ->waitForTextIn('@target', 'alpine-data-loaded')
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

                    <a href="/navigate-page" wire:navigate dusk="link">Go to the alpine data page</a>
                </div>
                HTML;
            }
        }])
            ->assertSeeIn('@source-page', 'Source page')
            ->assertConsoleLogHasNoErrors()
            ->waitForNavigate()->click('@link')
            ->waitForTextIn('@target', 'navigate-data-loaded')
            ->assertConsoleLogHasNoErrors()
            // Navigating on to another page containing the same component
            // reuses the cached module and still initializes correctly...
            ->waitForNavigate()->click('@next-link')
            ->waitForTextIn('@page-marker', 'page-two')
            ->waitForTextIn('@target', 'navigate-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_a_lazy_components_script_runs_against_its_real_markup_not_the_placeholder()
    {
        // The non-lazy instance warms the module cache, so the lazy instance's
        // import resolves instantly — before the hydration morph, without the
        // morph gate. The script records whether it saw the real markup...
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <livewire:testns::lazy-probe key="eager" />
                    <livewire:testns::lazy-probe key="lazy" lazy />
                </div>
                HTML;
            }
        }])
            ->waitUntil("window.lazyProbeResults && window.lazyProbeResults.length === 2")
            ->assertScript('window.lazyProbeResults.every(sawRealMarkup => sawRealMarkup)', true)
            ->assertConsoleLogHasNoErrors();
    }

    public function test_a_module_preload_link_is_injected_into_the_head()
    {
        // The initial page render emits a modulepreload link so the browser
        // starts fetching the module while parsing — before Livewire boots...
        Livewire::visit('testns::alpine-data')
            ->assertScript('!! document.head.querySelector(\'link[rel="modulepreload"][href*="testns---alpine-data.js"]\')', true)
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_a_module_preload_link_is_injected_for_lazy_components()
    {
        // Even though a lazy placeholder mounts without its scriptModule
        // effect, the page render still warms the module it will need...
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <livewire:testns::alpine-data lazy />
                </div>
                HTML;
            }
        }])
            ->assertScript('!! document.head.querySelector(\'link[rel="modulepreload"][href*="testns---alpine-data.js"]\')', true)
            ->waitForTextIn('@target', 'alpine-data-loaded')
            ->assertConsoleLogHasNoErrors();
    }

    public function test_x_cloak_is_honored_while_the_module_is_loading()
    {
        // The /slow-module.js import keeps the component's module pending
        // until the test releases it, holding the suspended window open...
        Livewire::visit('testns::slow-alpine-data')
            ->waitUntil('typeof window.releaseSlowModule === "function"')
            ->assertScript('document.querySelector(\'[dusk="wrapper"]\').hasAttribute(\'x-cloak\')', true)
            ->tap(fn ($b) => $b->script('window.releaseSlowModule()'))
            ->waitForTextIn('@target', 'slow-data-loaded')
            ->assertScript('document.querySelector(\'[dusk="wrapper"]\').hasAttribute(\'x-cloak\')', false)
            ->assertConsoleLogHasNoErrors();
    }

    public function test_a_component_removed_while_its_module_loads_never_runs_its_script()
    {
        Livewire::visit([new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$toggle('show')" dusk="toggle">Toggle</button>

                    @if ($show)
                        <livewire:testns::slow-alpine-data />
                    @endif
                </div>
                HTML;
            }
        }])
            ->waitForLivewire()->click('@toggle')
            ->waitUntil('typeof window.releaseSlowModule === "function"')
            // Remove the component before its module resolves...
            ->waitForLivewire()->click('@toggle')
            ->tap(fn ($b) => $b->script('window.releaseSlowModule()'))
            ->pause(300)
            ->assertScript('window.slowModuleRan === undefined', true)
            ->assertConsoleLogHasNoErrors();
    }

    public function test_a_failed_module_import_still_initializes_the_component_and_the_rest_of_the_page()
    {
        // The /missing-module.js import 404s, so the module never loads. The
        // component's tree must initialize anyway (fail open), and other
        // components on the page must be unaffected...
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <livewire:testns::broken-import />

                    <div x-data="{ message: 'sibling-works' }">
                        <span dusk="sibling" x-text="message"></span>
                    </div>
                </div>
                HTML;
            }
        }])
            ->waitForTextIn('@sibling', 'sibling-works')
            ->assertSeeIn('@broken-target', 'server-rendered')
            // The failed component's own tree still initializes (fail open):
            // an Alpine binding inside it that doesn't need the module renders...
            ->waitForTextIn('@broken-fail-open', 'initialized-anyway');
    }

    public function test_a_script_that_throws_still_initializes_the_component_and_the_rest_of_the_page()
    {
        Livewire::visit([new class extends Component {
            public function render()
            {
                return <<<'HTML'
                <div>
                    <livewire:testns::run-throws />

                    <div x-data="{ message: 'sibling-works' }">
                        <span dusk="sibling" x-text="message"></span>
                    </div>
                </div>
                HTML;
            }
        }])
            ->waitForTextIn('@sibling', 'sibling-works')
            ->assertSeeIn('@throws-target', 'server-rendered')
            ->waitForTextIn('@throws-fail-open', 'initialized-anyway');
    }
}
