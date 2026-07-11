<?php

namespace Livewire\Features\SupportWireFor;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_wire_for_renders_a_list_and_reacts_to_server_updates()
    {
        Livewire::visit(new class extends Component {
            public $fruits = ['apple', 'banana'];

            public function add() { $this->fruits[] = 'mango'; }

            public function removeFirst() { array_shift($this->fruits); }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="add" dusk="add">Add</button>
                    <button wire:click="removeFirst" dusk="remove">Remove</button>

                    <ul dusk="list">
                        <template wire:for="fruit in fruits">
                            <li wire:text="fruit"></li>
                        </template>
                    </ul>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@list', 'apple')
        ->assertSeeIn('@list', 'banana')
        ->waitForLivewire()->click('@add')
        ->assertSeeIn('@list', 'mango')
        ->waitForLivewire()->click('@remove')
        ->assertDontSeeIn('@list', 'apple')
        ->assertSeeIn('@list', 'banana')
        ->assertSeeIn('@list', 'mango');
    }

    public function test_wire_for_supports_an_index_alias()
    {
        Livewire::visit(new class extends Component {
            public $fruits = ['apple', 'banana'];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <ul dusk="list">
                        <template wire:for="(fruit, index) in fruits">
                            <li wire:text="index + ' - ' + fruit"></li>
                        </template>
                    </ul>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@list', '0 - apple')
        ->assertSeeIn('@list', '1 - banana');
    }

    public function test_wire_for_reacts_to_client_side_state_changes_without_a_request()
    {
        Livewire::visit(new class extends Component {
            public $fruits = ['apple'];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button x-on:click="$wire.fruits.push('banana')" dusk="push">Push</button>

                    <ul dusk="list">
                        <template wire:for="fruit in fruits">
                            <li wire:text="fruit"></li>
                        </template>
                    </ul>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@list', 'apple')
        ->assertDontSeeIn('@list', 'banana')
        ->click('@push')
        ->assertSeeIn('@list', 'banana');
    }

    public function test_wire_for_items_survive_unrelated_livewire_updates()
    {
        Livewire::visit(new class extends Component {
            public $fruits = ['apple', 'banana'];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>

                    <ul dusk="list">
                        <template wire:for="fruit in fruits" wire:key="fruit">
                            <li><input type="text" dusk="input"></li>
                        </template>
                    </ul>

                    <p dusk="after">After</p>
                </div>
                HTML;
            }
        })
        ->assertPresent('@input')
        ->type('@input', 'preserve me')
        ->waitForLivewire()->click('@refresh')
        // The generated list items aren't in the server-rendered HTML, so
        // morphing must skip over them. The typed input value proves the
        // first item wasn't torn down and recreated...
        ->assertValue('@input', 'preserve me')
        ->assertSeeIn('@after', 'After');
    }

    public function test_wire_for_actions_inside_the_loop_receive_the_iterated_item()
    {
        Livewire::visit(new class extends Component {
            public $fruits = ['apple', 'banana'];

            public function remove($fruit)
            {
                $this->fruits = array_values(array_diff($this->fruits, [$fruit]));
            }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <ul dusk="list">
                        <template wire:for="fruit in fruits" wire:key="fruit">
                            <li>
                                <span wire:text="fruit"></span>
                                <button wire:click="remove(fruit)" dusk="remove">Remove</button>
                            </li>
                        </template>
                    </ul>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@list', 'apple')
        ->waitForLivewire()->click('@remove')
        ->assertDontSeeIn('@list', 'apple')
        ->assertSeeIn('@list', 'banana');
    }

    public function test_wire_for_can_loop_over_a_nested_wire_for()
    {
        Livewire::visit(new class extends Component {
            public $lists = [
                ['name' => 'fruits', 'items' => ['apple', 'banana']],
                ['name' => 'veggies', 'items' => ['carrot']],
            ];

            public function render()
            {
                return <<<'HTML'
                <div>
                    <div dusk="lists">
                        <template wire:for="list in lists" wire:key="list.name">
                            <div>
                                <h2 wire:text="list.name"></h2>

                                <template wire:for="item in list.items" wire:key="item">
                                    <p wire:text="item"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@lists', 'fruits')
        ->assertSeeIn('@lists', 'apple')
        ->assertSeeIn('@lists', 'banana')
        ->assertSeeIn('@lists', 'veggies')
        ->assertSeeIn('@lists', 'carrot');
    }
}
