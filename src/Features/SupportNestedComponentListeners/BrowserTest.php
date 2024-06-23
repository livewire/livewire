<?php

namespace Livewire\Features\SupportNestedComponentListeners;

use Livewire\Livewire;
use Livewire\Component as BaseComponent;
use Tests\BrowserTestCase;

class BrowserTest extends BrowserTestCase
{
    function test_can_listen_for_child_events_directly_on_child()
    {
        Livewire::visit([
            new class extends BaseComponent {
                public $count = 0;

                function increment() {
                    $this->count++;
                }

                public function render() {
                    return <<<'HTML'
                        <div>
                            <h1 dusk="count">{{ $count }}</h1>

                            <livewire:child @fired="increment" />
                        </div>
                    HTML;
                }
            },
            'child' => new class extends BaseComponent {
                public function fire() {
                    $this->dispatch('fired');
                }

                public function render() {
                    return <<<'HTML'
                        <div>
                            <button dusk="button" wire:click="fire">fire from child</button>
                            <button dusk="button2" wire:click="$dispatch('fired')">fire from child (directly)</button>
                        </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@count', '0')
        ->click('@button')
        ->waitForTextIn('@count', '1')
        ->click('@button')
        ->waitForTextIn('@count', '2')
        ->click('@button2')
        ->waitForTextIn('@count', '3');
    }

    function test_can_dispatch_parameters_to_listeners()
    {
        Livewire::visit([
            new class extends BaseComponent {
                public $count = 0;

                function increment($by = 1) {
                    $this->count = $this->count + $by;
                }

                public function render() {
                    return <<<'HTML'
                        <div>
                            <h1 dusk="count">{{ $count }}</h1>

                            <livewire:child @fired="increment($event.detail.by)" />
                        </div>
                    HTML;
                }
            },
            'child' => new class extends BaseComponent {
                public function render() {
                    return <<<'HTML'
                        <div>
                            <button dusk="button" wire:click="$dispatch('fired', { by: 5 })">fire from child</button>
                        </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@count', '0')
        ->click('@button')
        ->waitForTextIn('@count', '5');
    }

    function test_can_dispatch_multi_word_event_names()
    {
        Livewire::visit([
            new class extends BaseComponent {
                public $count = 0;

                function increment() {
                    $this->count++;
                }

                public function render() {
                    return <<<'HTML'
                        <div>
                            <h1 dusk="count">{{ $count }}</h1>

                            <livewire:child @fired-event="increment" />
                        </div>
                    HTML;
                }
            },
            'child' => new class extends BaseComponent {
                public function fire() {
                    $this->dispatch('fired-event');
                }

                public function render() {
                    return <<<'HTML'
                        <div>
                            <button dusk="button" wire:click="fire">fire from child</button>
                            <button dusk="button2" wire:click="$dispatch('fired-event')">fire from child (directly)</button>
                        </div>
                    HTML;
                }
            }
        ])
        ->assertSeeIn('@count', '0')
        ->click('@button')
        ->waitForTextIn('@count', '1')
        ->click('@button')
        ->waitForTextIn('@count', '2')
        ->click('@button2')
        ->waitForTextIn('@count', '3');
    }
}

