<?php

namespace Livewire\Features\SupportEvents;

use Tests\BrowserTestCase;
use Livewire\Component;
use Livewire\Livewire;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_listen_for_component_event_with_x_on()
    {
        Livewire::visit(new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>

                    <span x-on:test-event="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                </div>
                HTML;
            }
        })
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@target', 'bar');
    }

    /** @test */
    public function can_listen_for_component_event_with_x_on_window()
    {
        Livewire::visit(new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>

                    <span x-on:test-event.window="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                </div>
                HTML;
            }
        })
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@target', 'bar');
    }

    /** @test */
    public function can_listen_for_component_self_event_with_x_on()
    {
        Livewire::visit(new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event')->self();
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>

                    <span x-on:test-event="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                </div>
                HTML;
            }
        })
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@target', 'bar');
    }

    /** @test */
    public function cant_listen_for_component_self_event_with_x_on_window()
    {
        Livewire::visit(new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event')->self();
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>

                    <span x-on:test-event.window="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                </div>
                HTML;
            }
        })
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertDontSeeIn('@target', 'bar');
    }

    /** @test */
    public function can_listen_for_child_component_event_with_x_on()
    {
        Livewire::visit([new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    <span x-on:test-event="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                    <livewire:child />
                </div>
                HTML;
            }
        }, 'child' => new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>
                </div>
                HTML;
            }
        }])
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@target', 'bar');
    }

    /** @test */
    public function cant_listen_for_child_component_self_event_with_x_on()
    {
        Livewire::visit([new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    <span x-on:test-event="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                    <livewire:child />
                </div>
                HTML;
            }
        }, 'child' => new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event')->self();
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>
                </div>
                HTML;
            }
        }])
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertDontSeeIn('@target', 'bar');
    }

    /** @test */
    public function can_listen_for_child_component_event_with_x_on_window()
    {
        Livewire::visit([new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    <span x-on:test-event.window="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                    <livewire:child />
                </div>
                HTML;
            }
        }, 'child' => new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>
                </div>
                HTML;
            }
        }])
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertSeeIn('@target', 'bar');
    }

    /** @test */
    public function cant_listen_for_child_component_self_event_with_x_on_window()
    {
        Livewire::visit([new class extends Component {
            function render()
            {
                return <<<'HTML'
                <div>
                    <span x-on:test-event.window="$el.textContent = 'bar'" dusk="target" wire:ignore></span>
                    <livewire:child />
                </div>
                HTML;
            }
        }, 'child' => new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event')->self();
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>
                </div>
                HTML;
            }
        }])
            ->assertDontSeeIn('@target', 'bar')
            ->waitForLivewire()->click('@button')
            ->assertDontSeeIn('@target', 'bar');
    }

    /** @test */
    public function can_listen_for_component_event_with_this_on_in_javascript()
    {
        Livewire::visit(new class extends Component {
            function dispatchTestEvent() {
                $this->dispatch('test-event');
            }

            function render()
            {
                return <<<'HTML'
                <div>
                    <button wire:click="dispatchTestEvent" dusk="button">Dispatch "foo"</button>

                    <span x-init="@this.on('test-event', () => { $el.textContent = 'bar' })" dusk="target" wire:ignore></span>
                </div>
                HTML;
            }
        })
        ->assertDontSeeIn('@target', 'bar')
        ->waitForLivewire()->click('@button')
        ->assertSeeIn('@target', 'bar');
    }
}
