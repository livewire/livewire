<?php

namespace Livewire\Features\SupportComputed;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    public function test_can_persist_computed_between_requests_and_bust_them()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            protected $thing = 'hey';

            #[Computed(persist: true)]
            public function foo() {
                $this->count++;

                return 'bar';
            }

            function unset()
            {
                unset($this->foo);
            }

            function render()
            {
                $noop = $this->foo;

                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                    <button wire:click="unset" dusk="unset">unset</button>

                    <div dusk="count">{{ $count }}</div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@count', '1')
        ->waitForLivewire()->click('@unset')
        ->assertSeeIn('@count', '2')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@count', '2');
    }

    public function test_can_cache_computed_properties_for_all_components_and_bust_them()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Computed(cache: true)]
            public function foo() {
                return $this->count;
            }

            function increment()
            {
                $this->count++;
                unset($this->foo);
            }

            function render()
            {
                $noop = $this->foo;

                return <<<'HTML'
                <div>
                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                    <button wire:click="increment" dusk="increment">unset</button>

                    <div dusk="count">{{ $this->foo }}</div>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@count', '0')
        ->waitForLivewire()->click('@increment')
        ->assertSeeIn('@count', '1')
        ->refresh()
        ->assertSeeIn('@count', '1');
    }

    public function test_computed_properties_cannot_be_set_on_front_end()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            #[Computed]
            public function foo() {
                return 'bar';
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <p>Foo: <span dusk="foo">{{ $this->foo }}</span></p>
                    <button wire:click="$set('foo', 'other')" dusk="change-foo">Change Foo</button>
                </div>
                HTML;
            }
        })
        ->assertSeeIn('@foo', 'bar')
        ->waitForLivewire()->click('@change-foo')
        ->assertSeeIn('@foo', 'bar')
        ;
    }
}
