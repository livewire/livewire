<?php

namespace Livewire\V4\Islands;

use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;
use Illuminate\Support\Facades\View;

class BrowserTest extends BrowserTestCase
{
    public function test_can_reference_an_external_view_island_and_it_inherits_scope()
    {
        Livewire::visit([
            new class extends Component {
                use HandlesIslands;

                public $counter = 1;

                public function increment() { $this->counter++; }

                public function render() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    return <<<'HTML'
                    <div>
                        <span dusk="counter">{{ $counter }}</span>
                        <button dusk="increment" wire:click="increment">+</button>

                        <div dusk="island-output">
                            {!! $this->island('basic', 'islands::basic', ['otherCounter' => $this->counter + 5]) !!}
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@counter', '1')
        ->assertSeeIn('@island-output', 'Counter: 1')
        ->assertSeeIn('@island-output', 'Other counter: 6')
        ->click('@increment')
        ->assertSeeIn('@counter', '2')
        ->assertSeeIn('@island-output', 'Counter: 2')
        ->assertSeeIn('@island-output', 'Other counter: 7')
        ;
    }

    public function test_can_use_blade_directive_and_it_inherits_scope()
    {
        Livewire::visit([
            new class extends Component {
                use HandlesIslands;

                public $counter = 1;

                public function increment() { $this->counter++; }

                public function render() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    return <<<'HTML'
                    <div>
                        <span dusk="counter">{{ $counter }}</span>
                        <button dusk="increment" wire:click="increment">+</button>

                        <div dusk="island-output">
                            @island('basic', 'islands::basic', ['otherCounter' => $this->counter + 5])
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@counter', '1')
        ->assertSeeIn('@island-output', 'Counter: 1')
        ->assertSeeIn('@island-output', 'Other counter: 6')
        ->click('@increment')
        ->assertSeeIn('@counter', '2')
        ->assertSeeIn('@island-output', 'Counter: 2')
        ->assertSeeIn('@island-output', 'Other counter: 7')
        ;
    }

    public function test_can_append_and_prepend_islands()
    {
        Livewire::visit([
            new class extends Component {
                use HandlesIslands;

                public $items = ['foo'];

                public function addBar() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    $this->island('items', 'islands::items', ['items' => ['bar']])->prepend();
                }

                public function addLob() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    $this->island('items', 'islands::items', ['items' => ['lob']])->append();
                }

                public function render() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    return <<<'HTML'
                    <div>
                        <button dusk="add-bar" wire:click="addBar">Add Bar</button>
                        <button dusk="add-lob" wire:click="addLob">Add Lob</button>

                        <div dusk="island-output">
                            {!! $this->island('items', 'islands::items') !!}
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@island-output', 'foo')
        ->click('@add-bar')
        ->assertSeeIn('@island-output', 'bar')
        ->assertSeeIn('@island-output', 'foo')
        ->click('@add-lob')
        ->assertSeeIn('@island-output', 'bar')
        ->assertSeeIn('@island-output', 'foo')
        ->assertSeeIn('@island-output', 'lob')
        ;
    }

    public function test_can_use_blade_directive_with_append_and_prepend()
    {
        Livewire::visit([
            new class extends Component {
                use HandlesIslands;

                public $items = ['foo'];

                public function addBar() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    $this->island('items', 'islands::items', ['items' => ['bar']])->prepend();
                }

                public function addLob() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    $this->island('items', 'islands::items', ['items' => ['lob']])->append();
                }

                public function render() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');

                    return <<<'HTML'
                    <div>
                        <button dusk="add-bar" wire:click="addBar">Add Bar</button>
                        <button dusk="add-lob" wire:click="addLob">Add Lob</button>

                        <div dusk="island-output">
                            @island('items', 'islands::items')
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@island-output', 'foo')
        ->click('@add-bar')
        ->assertSeeIn('@island-output', 'bar')
        ->assertSeeIn('@island-output', 'foo')
        ->click('@add-lob')
        ->assertSeeIn('@island-output', 'bar')
        ->assertSeeIn('@island-output', 'foo')
        ->assertSeeIn('@island-output', 'lob')
        ;
    }

    public function test_can_use_inline_islands()
    {
        Livewire::visit([
            new class extends Component {
                use HandlesIslands;

                public $counter = 1;

                public function increment() { $this->counter++; }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <span dusk="counter">{{ $counter }}</span>
                        <button dusk="increment" wire:click="increment">+</button>

                        <div dusk="island-output">
                            {!! $this->island('basic', ['otherCounter' => $this->counter + 5]) !!}
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@counter', '1')
        ->assertSeeIn('@island-output', 'Counter: 1')
        ->assertSeeIn('@island-output', 'Other counter: 6')
        ->click('@increment')
        ->assertSeeIn('@counter', '2')
        ->assertSeeIn('@island-output', 'Counter: 2')
        ->assertSeeIn('@island-output', 'Other counter: 7')
        ;
    }

    public function test_can_use_inline_islands_with_blade_directive()
    {
        Livewire::visit([
            new class extends Component {
                use HandlesIslands;

                public $counter = 1;

                public function increment() { $this->counter++; }

                public function render() {
                    return <<<'HTML'
                    <div>
                        <span dusk="counter">{{ $counter }}</span>
                        <button dusk="increment" wire:click="increment">+</button>

                        <div dusk="island-output">
                            @island('basic', ['otherCounter' => $this->counter + 5])
                                Counter: {{ $counter }}
                                Other counter: {{ $otherCounter }}
                            @endisland
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@counter', '1')
        ->assertSeeIn('@island-output', 'Counter: 1')
        ->assertSeeIn('@island-output', 'Other counter: 6')
        ->click('@increment')
        ->assertSeeIn('@counter', '2')
        ->assertSeeIn('@island-output', 'Counter: 2')
        ->assertSeeIn('@island-output', 'Other counter: 7')
        ;
    }
}