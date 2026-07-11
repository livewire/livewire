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
        ->assertScript("document.querySelectorAll('[dusk=list] li').length", 3)
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
                        <template wire:for="fruit in fruits" wire:for:key="fruit">
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
                        <template wire:for="fruit in fruits" wire:for:key="fruit">
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
                        <template wire:for="list in lists" wire:for:key="list.name">
                            <div>
                                <h2 wire:text="list.name"></h2>

                                <template wire:for="item in list.items" :key="item">
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
    public function test_plain_x_for_templates_survive_server_updates_without_ghost_rows()
    {
        Livewire::visit(new class extends Component {
            public $fruits = ['apple', 'banana'];

            public function add() { $this->fruits[] = 'mango'; }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="add" dusk="add">Add</button>
                    <button wire:click="$refresh" dusk="refresh">Refresh</button>

                    <ul dusk="list">
                        <template x-for="fruit in $wire.fruits">
                            <li x-text="fruit"></li>
                        </template>
                    </ul>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@list', 'apple')
        ->assertScript("document.querySelectorAll('[dusk=list] li').length", 2)
        // Growing the list on the server used to leave behind an extra empty
        // row: morph would insert the raw clone that Alpine's seeding rendered
        // into the incoming tree, alongside the real row the live `x-for`
        // effect created...
        ->waitForLivewire()->click('@add')
        ->assertSeeIn('@list', 'mango')
        ->assertScript("document.querySelectorAll('[dusk=list] li').length", 3)
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@list', 'apple')
        ->assertSeeIn('@list', 'mango')
        ->assertScript("document.querySelectorAll('[dusk=list] li').length", 3)
        ->assertScript("[...document.querySelectorAll('[dusk=list] li')].every(li => li.innerText.trim() !== '')", true);
    }
    public function test_wire_for_keyed_items_keep_their_dom_state_when_reordered()
    {
        Livewire::visit(new class extends Component {
            public $fruits = ['apple', 'banana'];

            public function reverse() { $this->fruits = array_reverse($this->fruits); }

            public function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="reverse" dusk="reverse">Reverse</button>

                    <ul dusk="list">
                        <template wire:for="fruit in fruits" wire:for:key="fruit">
                            <li>
                                <span wire:text="fruit"></span>
                                <input type="text" x-bind:data-fruit="fruit">
                            </li>
                        </template>
                    </ul>
                </div>
                HTML;
            }
        })
        ->assertScript("document.querySelector('[dusk=list] li:nth-of-type(1) input').dataset.fruit", 'apple')
        ->tap(fn ($b) => $b->script("document.querySelector('[data-fruit=apple]').value = 'typed into apple'"))
        ->waitForLivewire()->click('@reverse')
        // With keyed items, reordering moves the existing elements instead of
        // rewriting them in place — the input's typed value travels with its row...
        ->assertScript("document.querySelector('[dusk=list] li:nth-of-type(1) input').dataset.fruit", 'banana')
        ->assertScript("document.querySelector('[data-fruit=apple]').value", 'typed into apple');
    }
}
