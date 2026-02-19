<?php

namespace Livewire\Features\SupportDataBinding;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

/**
 * Tests for mergeNewSnapshot() surgical update behavior.
 *
 * These tests verify that:
 * 1. Server changes are applied surgically (not at root level)
 * 2. Client ephemeral state is preserved during server updates
 * 3. Array removals are handled correctly (reverse index order)
 */
class MergeSnapshotBrowserTest extends BrowserTestCase
{
    function test_server_can_add_item_to_nested_array()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'My Chart',
                'series' => [
                    ['name' => 'Series A'],
                    ['name' => 'Series B'],
                ]
            ];

            public function addSeries()
            {
                $this->config['series'][] = ['name' => 'Series C'];
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="add" wire:click="addSeries">Add Series</button>
                        <div dusk="count">Series count: {{ count($config['series']) }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@count', 'Series count: 2')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@count', 'Series count: 3');
    }

    function test_server_can_modify_deep_nested_property()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'My Chart',
                'series' => [
                    ['name' => 'Series A', 'data' => [1, 2, 3]],
                ]
            ];

            public function changeSeriesName()
            {
                $this->config['series'][0]['name'] = 'CHANGED';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="change" wire:click="changeSeriesName">Change Name</button>
                        <div dusk="name">Name: {{ $config['series'][0]['name'] }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@name', 'Name: Series A')
            ->waitForLivewire()->click('@change')
            ->assertSeeIn('@name', 'Name: CHANGED');
    }

    function test_client_ephemeral_changes_preserved_when_server_changes_different_nested_property()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'Original Title',
                'series' => [
                    ['name' => 'Series A'],
                ]
            ];

            public function changeSeriesOnServer()
            {
                $this->config['series'][0]['name'] = 'SERVER CHANGED';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="title-input" type="text" wire:model="config.title" />
                        <button dusk="server-change" wire:click="changeSeriesOnServer">Server Change</button>
                        <div dusk="title-ephemeral" x-text="$wire.config.title"></div>
                        <div dusk="series-name">{{ $config['series'][0]['name'] }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@title-ephemeral', 'Original Title')
            // Client changes title (ephemeral only)
            ->type('@title-input', 'CLIENT TITLE')
            ->pause(50)
            ->assertSeeIn('@title-ephemeral', 'CLIENT TITLE')
            // Server changes series (different property in same root)
            ->waitForLivewire()->click('@server-change')
            ->assertSeeIn('@series-name', 'SERVER CHANGED')
            // Client's title change should be preserved
            ->assertSeeIn('@title-ephemeral', 'CLIENT TITLE');
    }

    function test_server_authoritative_changes_override_client_changes_to_same_property()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'Original',
            ];

            public function serverChangeTitle()
            {
                $this->config['title'] = 'SERVER VALUE';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="input" type="text" wire:model="config.title" />
                        <button dusk="server" wire:click="serverChangeTitle">Server Change</button>
                        <div dusk="ephemeral" x-text="$wire.config.title"></div>
                    </div>
                BLADE;
            }
        })
            ->type('@input', 'CLIENT VALUE')
            ->pause(50)
            ->assertSeeIn('@ephemeral', 'CLIENT VALUE')
            ->waitForLivewire()->click('@server')
            // Server wins when it explicitly changes the same property
            ->assertSeeIn('@ephemeral', 'SERVER VALUE');
    }

    function test_server_can_remove_item_from_array()
    {
        Livewire::visit(new class extends Component {
            public $items = ['a', 'b', 'c', 'd'];

            public function removeItem()
            {
                array_pop($this->items);
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="remove" wire:click="removeItem">Remove</button>
                        <div dusk="items">{{ implode(',', $items) }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items', 'a,b,c,d')
            ->waitForLivewire()->click('@remove')
            ->assertSeeIn('@items', 'a,b,c');
    }

    function test_client_ephemeral_changes_to_different_root_property_preserved()
    {
        Livewire::visit(new class extends Component {
            public $propA = ['nested' => 'value A'];
            public $propB = ['nested' => 'value B'];

            public function changePropB()
            {
                $this->propB['nested'] = 'CHANGED B';
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="change" wire:click="changePropB">Change B</button>
                        <button dusk="mutate-a" x-on:click="$wire.propA.nested = 'CLIENT A'">Mutate A</button>
                        <div dusk="a-ephemeral" x-text="$wire.propA.nested"></div>
                        <div dusk="b">{{ $propB['nested'] }}</div>
                    </div>
                BLADE;
            }
        })
            // Client mutates propA
            ->click('@mutate-a')
            ->pause(50)
            ->assertSeeIn('@a-ephemeral', 'CLIENT A')
            // Server changes propB
            ->waitForLivewire()->click('@change')
            ->assertSeeIn('@b', 'CHANGED B')
            // Client's propA mutation should be preserved
            ->assertSeeIn('@a-ephemeral', 'CLIENT A');
    }

    function test_client_changes_during_pending_request_preserved_for_same_root_property()
    {
        Livewire::visit(new class extends Component {
            public $config = [
                'title' => 'Original',
                'count' => 0,
            ];

            public function slowIncrement()
            {
                usleep(500000); // 500ms delay
                $this->config['count']++;
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <input dusk="title-input" type="text" wire:model="config.title" />
                        <button dusk="slow" wire:click="slowIncrement">Slow Increment</button>
                        <div dusk="count">Count: {{ $config['count'] }}</div>
                        <div dusk="title-ephemeral" x-text="$wire.config.title"></div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@count', 'Count: 0')
            // Start slow request
            ->click('@slow')
            // Type while request is pending
            ->pause(100)
            ->type('@title-input', 'TYPED DURING REQUEST')
            ->pause(100)
            ->assertSeeIn('@title-ephemeral', 'TYPED DURING REQUEST')
            // Wait for slow request to complete
            ->waitForTextIn('@count', 'Count: 1')
            // Typing should be preserved (surgical updates)
            ->assertSeeIn('@title-ephemeral', 'TYPED DURING REQUEST');
    }

    function test_dot_containing_keys_preserved_across_multiple_requests()
    {
        Livewire::visit(new class extends Component {
            public $articles = [];

            public function mount()
            {
                $this->articles['order.foo'] = ['show' => 'abc'];
            }

            public function addSomething()
            {
                $this->articles['order.foo.bar'] = ['show' => 'ghl'];
            }

            public function addSomethingAgain()
            {
                $this->articles['order.foo.lol'] = ['show' => 'xyz'];
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="add" wire:click="addSomething">Add</button>
                        <button dusk="add-again" wire:click="addSomethingAgain">Add Again</button>
                        <div dusk="output">
                            @foreach($articles as $key => $value)
                                <span>{{ $key }}:{{ $value['show'] }}</span>
                            @endforeach
                        </div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@output', 'order.foo:abc')
            ->waitForLivewire()->click('@add')
            ->assertSeeIn('@output', 'order.foo:abc')
            ->assertSeeIn('@output', 'order.foo.bar:ghl')
            // Second interaction — this is where the bug occurs
            ->waitForLivewire()->click('@add-again')
            ->assertSeeIn('@output', 'order.foo:abc')
            ->assertSeeIn('@output', 'order.foo.bar:ghl')
            ->assertSeeIn('@output', 'order.foo.lol:xyz');
    }

    function test_nested_array_removals_at_different_levels()
    {
        Livewire::visit(new class extends Component {
            public $data = [
                'items' => [
                    ['id' => 1, 'tags' => ['a', 'b', 'c']],
                    ['id' => 2, 'tags' => ['d', 'e', 'f']],
                    ['id' => 3, 'tags' => ['g', 'h', 'i']],
                ]
            ];

            public function removeMultiple()
            {
                // Remove tags from item 0 AND remove item 2 entirely
                array_pop($this->data['items'][0]['tags']); // removes 'c'
                array_pop($this->data['items'][0]['tags']); // removes 'b'
                array_pop($this->data['items']); // removes item 3
            }

            public function render()
            {
                return <<<'BLADE'
                    <div>
                        <button dusk="remove" wire:click="removeMultiple">Remove</button>
                        <div dusk="items-count">Items: {{ count($data['items']) }}</div>
                        <div dusk="tags-count">Tags: {{ count($data['items'][0]['tags']) }}</div>
                    </div>
                BLADE;
            }
        })
            ->assertSeeIn('@items-count', 'Items: 3')
            ->assertSeeIn('@tags-count', 'Tags: 3')
            ->waitForLivewire()->click('@remove')
            ->assertSeeIn('@items-count', 'Items: 2')
            ->assertSeeIn('@tags-count', 'Tags: 1');
    }
}
