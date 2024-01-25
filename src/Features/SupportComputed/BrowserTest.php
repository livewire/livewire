<?php

namespace Livewire\Features\SupportComputed;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_persist_computed_between_requests_and_bust_them()
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

    /** @test */
    public function can_cache_computed_properties_for_all_components_and_bust_them()
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

    /** @test */
    public function can_persist_computed_with_key_between_requests_and_bust_them()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            protected $thing = 'hey';

            #[Computed(persist: true, key: 'there')]
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
}
