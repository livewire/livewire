<?php

namespace Livewire\Features\SupportJsModules;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Statics like the rendered-assets bucket survive between tests in
        // the same process...
        Livewire::flushState();

        app('livewire.finder')->addNamespace('testns', viewPath: __DIR__.'/fixtures');
    }

    public function test_an_initial_render_injects_a_module_preload_link_into_the_head()
    {
        Route::get('/module-preload-test', fn () => app('livewire')->new('testns::alpine-data')());

        $response = $this->get('/module-preload-test');

        $response->assertSee('<link rel="modulepreload"', false);
        $response->assertSee('testns---alpine-data.js?v=', false);
    }

    public function test_a_component_without_a_script_module_injects_no_preload_link()
    {
        Route::get('/no-module-preload-test', fn () => app('livewire')->new('testns::no-module')());

        $response = $this->get('/no-module-preload-test');

        $response->assertDontSee('modulepreload');
    }

    public function test_an_update_that_mounts_a_child_announces_the_childs_script_module()
    {
        $component = Livewire::test(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    @if ($show)
                        <livewire:testns::alpine-data />
                    @endif
                </div>
                HTML;
            }
        });

        $this->assertArrayNotHasKey('childScriptModules', $component->effects);

        $component->set('show', true);

        $modules = $component->effects['childScriptModules'] ?? null;

        $this->assertNotNull($modules);
        $this->assertCount(1, $modules);
        $this->assertSame('testns::alpine-data', $modules[0]['name']);
        $this->assertArrayHasKey('hash', $modules[0]);
    }

    public function test_child_module_announcements_are_not_repeated_on_the_next_update()
    {
        $component = Livewire::test(new class extends Component {
            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div>
                    @if ($show)
                        <livewire:testns::alpine-data />
                    @endif
                </div>
                HTML;
            }
        });

        $component->set('show', true);

        $this->assertArrayHasKey('childScriptModules', $component->effects);

        // The collector drains on announcement — the next update mounts
        // nothing new and must announce nothing...
        $component->call('$refresh');

        $this->assertArrayNotHasKey('childScriptModules', $component->effects);
    }

    public function test_lazy_hydration_announces_the_script_modules_of_children_it_mounts()
    {
        \Livewire\Features\SupportLazyLoading\SupportLazyLoading::$disableWhileTesting = false;

        try {
            $component = Livewire::test('testns::lazy-wrapper');

            preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($component->html()), $matches);

            $this->assertNotEmpty($matches[1] ?? null);

            $component->call('__lazyLoad', $matches[1]);

            $modules = $component->effects['childScriptModules'] ?? null;

            $this->assertNotNull($modules);
            $this->assertCount(1, $modules);
            $this->assertSame('testns::alpine-data', $modules[0]['name']);
        } finally {
            \Livewire\Features\SupportLazyLoading\SupportLazyLoading::$disableWhileTesting = true;
        }
    }

    public function test_an_update_that_mounts_no_new_children_announces_nothing()
    {
        $component = Livewire::test(new class extends Component {
            public $count = 0;

            public function render()
            {
                return '<div>{{ $count }}</div>';
            }
        });

        $component->set('count', 1);

        $this->assertArrayNotHasKey('childScriptModules', $component->effects);
    }
}
