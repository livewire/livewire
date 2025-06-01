<?php

namespace Livewire\Features\SupportPartials;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_reference_an_external_view_partial_and_it_inherits_scope()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public $counter = 0;

                public function boot() {
                    View::addNamespace('partials', __DIR__ . '/stubs');
                }

                public function changeItems()
                {
                    $this->counter = 1;

                    $this->items = ['baz', 'bob'];

                    $this->partial('basic', 'partials::basic', ['otherCounter' => $this->counter + 5]);
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="button">Change Items</button>

                    <span dusk="counter">{{ $counter }}</span>

                    @if ($counter > 0)
                        <?php throw new \Exception('This should not be triggered'); ?>
                    @endif

                    <div>
                        @partial('basic', 'partials::basic', ['otherCounter' => $this->counter + 5])
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

    public function test_can_append_and_prepend_partials()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['foo', 'bar'];

                public function boot() {
                    View::addNamespace('partials', __DIR__ . '/stubs');
                }

                public function changeItems()
                {
                    $this->items = ['baz', 'bob'];

                    $this->partial('items', 'partials::items');
                }

                public function prependItems()
                {
                    array_unshift($this->items, 'bar');

                    $this->partial('items', 'partials::items', ['items' => ['bar']])->prepend();
                }

                public function appendItems()
                {
                    array_push($this->items, 'lob');

                    $this->partial('items', 'partials::items', ['items' => ['lob']])->append();
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="changeItems" dusk="change-button">Change Items</button>
                    <button wire:click="prependItems" dusk="prepend-button">Prepend Items</button>
                    <button wire:click="appendItems" dusk="append-button">Append Items</button>

                    <ul dusk="items">
                        @partial('items', 'partials::items')
                    </ul>
                </div>
                HTML; }
        })
        ->waitForText('foo')
        ->assertSourceHas("<li>foo</li>\n<li>bar</li>")
        ->waitForLivewire()->click('@change-button')
        ->assertSourceHas("<li>baz</li>\n<li>bob</li>")
        ->waitForLivewire()->click('@prepend-button')
        ->assertSourceHas("<li>bar</li>\n<li>baz</li>\n<li>bob</li>")
        ->waitForLivewire()->click('@append-button')
        ->assertSourceHas("<li>bar</li>\n<li>baz</li>\n<li>bob</li>\n<li>lob</li>")
        ;
    }
}