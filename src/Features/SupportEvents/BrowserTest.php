<?php

namespace Livewire\Features\SupportEvents;

use Illuminate\Support\Facades\Blade;
use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_listen_for_component_event_with_this_on_in_javascript()
    {
        Livewire::visit(new class extends Component {
            function foo() {
                $this->dispatch('foo');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="foo" dusk="button">Dispatch "foo"</button>

                    <span x-init="@this.on('foo', () => { $el.textContent = 'bar' })" dusk="target" wire:ignore></span>
                </div>
                HTML;
            }
        })
        ->assertDontSeeIn('@target', 'bar')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@target', 'bar');
    }

    /** @test */
    public function dispatch_from_javascript_should_only_be_called_once()
    {
        Livewire::visit(new class extends Component {
            public $count = 0;

            protected $listeners = ['foo' => 'onFoo'];

            function onFoo()
            {
                $this->count++;
            }

            function render()
            {
                return Blade::render(<<<'HTML'
                <div>
                    <button @click="$dispatch('foo')" dusk="button">{{ $count }}</button>
                </div>
                HTML, ['count' => $this->count]);
            }
        })
            ->assertSeeIn('@button', '0')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@button', '1');
    }
}
