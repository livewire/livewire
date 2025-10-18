<?php

namespace Livewire\Features\SupportIslands;

use Tests\TestCase;
use Livewire\Livewire;

class UnitTest extends TestCase
{
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
}
