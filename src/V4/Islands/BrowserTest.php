<?php

namespace Livewire\V4\Islands;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_reference_an_external_view_island_and_it_inherits_scope()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public $counter = 0;

                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function changeItems()
                {
                    $this->counter = 1;

                    $this->items = ['baz', 'bob'];

                    $this->island('basic', 'islands::basic', ['otherCounter' => $this->counter + 5]);
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="button">Change Items</button>

                    <span dusk="counter">{{ $counter }}</span>

                    @if ($counter > 0)
                        <?php throw new \Exception('This should not be triggered'); ?>
                    @endif

                    <div>
                        @island('basic', 'islands::basic', ['otherCounter' => $this->counter + 5])
                    </div>
                </div>
                HTML; }
        })
        ->waitForText('foo')
        ->assertSee('foo')
        ->assertSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '5')
        ->waitForLivewire()->click('@button')
        ->waitForText('baz')
        ->assertSee('baz')
        ->assertSee('bob')
        ->assertDontSee('foo')
        ->assertDontSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '6')
        ;
    }

    public function test_can_append_and_prepend_islands()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public function boot() {
                    View::addNamespace('islands', __DIR__ . '/fixtures');
                }

                public function changeItems()
                {
                    $this->items = ['baz', 'bob'];

                    $this->island('items', 'islands::items');
                }

                public function prependItems()
                {
                    array_unshift($this->items, 'bar');

                    $this->island('items', 'islands::items', ['items' => ['bar']])->prepend();
                }

                public function appendItems()
                {
                    array_push($this->items, 'lob');

                    $this->island('items', 'islands::items', ['items' => ['lob']])->append();
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="change-button">Change Items</button>
                    <button wire:click="prependItems" dusk="prepend-button">Prepend Items</button>
                    <button wire:click="appendItems" dusk="append-button">Append Items</button>

                    <ul dusk="items">
                        @island('items', 'islands::items')
                    </ul>
                </div>
                HTML; }
        })
        ->waitForText('foo')
        ->assertSourceHas("<li>foo</li>\n<li>bar</li>")
        ->waitForLivewire()->click('@change-button')
        ->assertSourceHas("<li>baz</li>\n<li>bob</li>")
        ->waitForLivewire()->click('@prepend-button')
        ->assertSourceHas("<li>bar</li>\n<!--[if ENDBLOCK]><![endif]--><!--[if BLOCK]><![endif]--><li>baz</li>\n<li>bob</li>")
        ->waitForLivewire()->click('@append-button')
        ->assertSourceHas("<li>bar</li>\n<!--[if ENDBLOCK]><![endif]--><!--[if BLOCK]><![endif]--><li>baz</li>\n<li>bob</li>\n<!--[if ENDBLOCK]><![endif]--><!--[if BLOCK]><![endif]--><li>lob</li>")
        ;
    }

    public function test_can_use_inline_islands()
    {
        $this->markTestSkipped('This feature only works in single file components and we don\'t have a way to test those yet');

        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public $counter = 0;

                public function changeItems()
                {
                    $this->counter = 1;

                    $this->items = ['baz', 'bob'];

                    $this->island('basic', ['otherCounter' => $this->counter + 5]);
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="button">Change Items</button>

                    <span dusk="counter">{{ $counter }}</span>

                    @if ($counter > 0)
                        <?php throw new \Exception('This should not be triggered'); ?>
                    @endif

                    <div>
                        @island('basic', ['otherCounter' => $this->counter + 5])
                            <div>
                                <span dusk="other-counter">{{ $otherCounter }}</span>

                                @foreach ($items as $item)
                                    <div>{{ $item }}</div>
                                @endforeach
                            </div>
                        @endisland
                    </div>
                </div>
                HTML; }
        })
        ->waitForText('foo')
        ->assertSee('foo')
        ->assertSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '5')
        ->waitForLivewire()->click('@button')
        ->waitForText('baz')
        ->assertSee('baz')
        ->assertSee('bob')
        ->assertDontSee('foo')
        ->assertDontSee('bar')
        ->assertSeeIn('@counter', '0')
        ->assertSeeIn('@other-counter', '6')
        ;
    }
}