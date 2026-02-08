<?php

namespace Livewire\Features\SupportIslands;

use Tests\TestCase;
use Livewire\Livewire;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Illuminate\Support\Facades\File;

class UnitTest extends TestCase
{
    public function test_class_component_island_recovers_when_cached_file_is_deleted_between_requests()
    {
        $component = Livewire::test(new class extends \Livewire\Component {
            public int $count = 0;

            public function increment()
            {
                $this->count++;
                $this->renderIsland('counter');
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'counter')
                        <div>count: {{ $count }}</div>
                    @endisland
                </div>
                HTML;
            }
        });

        $component->assertSee('count: 0');

        // Get the island token from the component's stored islands...
        $islands = $component->instance()->getIslands();
        $token = $islands[0]['token'];
        $cachedPath = IslandCompiler::getCachedPathFromToken($token);

        // Verify the island cache file exists after initial render...
        $this->assertFileExists($cachedPath);

        // Delete the island cache file and its compiled Blade cache to simulate a deployment...
        File::delete($cachedPath);

        $compiledPath = app('blade.compiler')->getCompiledPath($cachedPath);
        if (file_exists($compiledPath)) {
            File::delete($compiledPath);
        }

        $this->assertFileDoesNotExist($cachedPath);

        // A subsequent request should still work, not throw FileNotFoundException...
        $component->call('increment');
    }

    public function test_sfc_island_recovers_when_cached_file_is_deleted_between_requests()
    {
        // Create a temporary view file to simulate an SFC's compiled view...
        $viewPath = storage_path('framework/views/livewire/test-sfc-island.blade.php');
        File::ensureDirectoryExists(dirname($viewPath));
        File::put($viewPath, <<<'HTML'
        <div>
            @island(name: 'counter')
                <div>count: {{ $count }}</div>
            @endisland
        </div>
        HTML);

        $component = Livewire::test(new class($viewPath) extends \Livewire\Component {
            public int $count = 0;
            protected static string $viewPath;

            public function __construct($viewPath = null)
            {
                if ($viewPath) {
                    static::$viewPath = $viewPath;
                }
            }

            public function increment()
            {
                $this->count++;
                $this->renderIsland('counter');
            }

            // SFCs use view() instead of render()...
            protected function view($data = [])
            {
                return app('view')->file(static::$viewPath, $data);
            }
        }, ['viewPath' => $viewPath]);

        $component->assertSee('count: 0');

        // Get the island token from the component's stored islands...
        $islands = $component->instance()->getIslands();
        $token = $islands[0]['token'];
        $cachedPath = IslandCompiler::getCachedPathFromToken($token);

        // Verify the island cache file exists after initial render...
        $this->assertFileExists($cachedPath);

        // Delete the island cache file and its compiled Blade cache to simulate a deployment...
        File::delete($cachedPath);

        $compiledPath = app('blade.compiler')->getCompiledPath($cachedPath);
        if (file_exists($compiledPath)) {
            File::delete($compiledPath);
        }

        $this->assertFileDoesNotExist($cachedPath);

        // A subsequent request should still work, not throw FileNotFoundException...
        $component->call('increment');

        // Clean up...
        File::delete($viewPath);
    }

    public function test_render_island_directives()
    {
        Livewire::test(new class extends \Livewire\Component {
            public function render() {
                return <<<'HTML'
                <div>
                    Outside island

                    @island
                        Inside island

                        @island
                            Nested island
                        @endisland

                        after
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertDontSee('@island')
            ->assertDontSee('@endisland')
            ->assertSee('Outside island')
            ->assertSee('Inside island')
            ->assertSee('Nested island')
            ->assertSee('!--[if FRAGMENT:')
            ->assertSee('!--[if ENDFRAGMENT:');
    }

    public function test_island_with_raw_block()
    {
        Livewire::test(new class extends \Livewire\Component {
            public function render() {
                return <<<'HTML'
                <div>
                    Outside island

                    @island
                        <div>
                            @php $foo = 'bar'; @endphp
                            Inside island: {{ $foo }}
                        </div>
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertSee('Inside island: bar')
            ;
    }

    public function test_island_with_parameter_provides_scope()
    {
        Livewire::test(new class extends \Livewire\Component {
            public $componentData = 'component value';

            public function render() {
                return <<<'HTML'
                <div>
                    @island(with: ['bar' => 'baz', 'number' => 42])
                        <div>
                            bar: {{ $bar ?? 'not set' }}
                            number: {{ $number ?? 'not set' }}
                        </div>
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertSee('bar: baz')
            ->assertSee('number: 42');
    }

    public function test_island_with_parameter_can_reference_component_properties()
    {
        Livewire::test(new class extends \Livewire\Component {
            public $myData = 'from component';

            public function render() {
                return <<<'HTML'
                <div>
                    @island(with: ['data' => $this->myData])
                        <div>data: {{ $data ?? 'not set' }}</div>
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertSee('data: from component');
    }

    public function test_island_with_empty_parameter_still_renders()
    {
        Livewire::test(new class extends \Livewire\Component {
            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'test')
                        <div>content without with parameter</div>
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertSee('content without with parameter');
    }

    public function test_island_with_parameter_overrides_component_properties()
    {
        Livewire::test(new class extends \Livewire\Component {
            public $count = 999;

            public function render() {
                return <<<'HTML'
                <div>
                    @island(with: ['count' => 123])
                        <div>count: {{ $count }}</div>
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertSee('count: 123')
            ->assertDontSee('count: 999');
    }

    public function test_runtime_with_overrides_directive_with()
    {
        Livewire::test(new class extends \Livewire\Component {
            public $count = 999;

            public function refreshWithData()
            {
                $this->renderIsland('test', null, 'morph', ['count' => 456]);
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'test', with: ['count' => 123])
                        <div>count: {{ $count }}</div>
                    @endisland

                    <button wire:click="refreshWithData">Refresh</button>
                </div>
                HTML;
            }
        })
            ->assertSee('count: 123')
            ->call('refreshWithData');

        // After calling refreshWithData, the island should show the runtime value
        // Note: we can't easily assert on the fragment, but we can verify no errors occur
    }

    public function test_precedence_order()
    {
        Livewire::test(new class extends \Livewire\Component {
            public $value = 'component';

            public function render() {
                return <<<'HTML'
                <div>
                    @island(with: ['value' => 'directive'])
                        <div>value: {{ $value }}</div>
                    @endisland
                </div>
                HTML;
            }
        })
            ->assertSee('value: directive')
            ->assertDontSee('value: component');
    }

    public function test_runtime_with_works_on_island_without_directive_with()
    {
        Livewire::test(new class extends \Livewire\Component {
            public function refreshWithData()
            {
                $this->renderIsland('plain', null, 'morph', ['data' => 'runtime']);
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island(name: 'plain')
                        <div>data: {{ $data ?? 'not set' }}</div>
                    @endisland

                    <button wire:click="refreshWithData">Refresh</button>
                </div>
                HTML;
            }
        })
            ->assertSee('data: not set')
            ->call('refreshWithData');
    }

    public function test_runtime_with_works_on_island_with_no_parameters()
    {
        Livewire::test(new class extends \Livewire\Component {
            public function refreshWithData()
            {
                // Find the token for the unnamed island
                $islands = $this->getIslands();
                $token = $islands[0]['token'] ?? null;

                if ($token) {
                    $this->renderIsland($token, null, 'morph', ['data' => 'runtime']);
                }
            }

            public function render() {
                return <<<'HTML'
                <div>
                    @island
                        <div>data: {{ $data ?? 'not set' }}</div>
                    @endisland

                    <button wire:click="refreshWithData">Refresh</button>
                </div>
                HTML;
            }
        })
            ->assertSee('data: not set')
            ->call('refreshWithData');
    }

    public function test_commented_out_island_directives_do_not_affect_content()
    {
        Livewire::test(new class extends \Livewire\Component {
            public function render() {
                return <<<'HTML'
                <div>
                    {{-- @island(defer: true) --}}
                        This content should be visible
                    {{-- @endisland --}}
                </div>
                HTML;
            }
        })
            ->assertSee('This content should be visible')
            ->assertDontSee('@island')
            ->assertDontSee('@endisland');
    }

    public function test_commented_out_island_with_livewire_component_inside()
    {
        Livewire::component('inner-component', new class extends \Livewire\Component {
            public function render() {
                return '<span>Inner component rendered</span>';
            }
        });

        Livewire::test(new class extends \Livewire\Component {
            public function render() {
                return <<<'HTML'
                <div>
                    {{-- @island(defer: true) --}}
                        <livewire:inner-component />
                    {{-- @endisland --}}
                </div>
                HTML;
            }
        })
            ->assertSee('Inner component rendered');
    }
}
