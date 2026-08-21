<?php

namespace Livewire\Features\SupportSlots;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_action_promises_resolve_after_transitioned_slots_finish_morphing()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function increment()
                {
                    $this->count++;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div x-data="{ result: '' }">
                        <button
                            dusk="increment"
                            x-on:click="await $wire.increment(); result = document.querySelector('[dusk=slot-count]').textContent.trim()"
                        >Increment</button>

                        <span dusk="result" x-text="result"></span>

                        <livewire:modal>
                            <div dusk="slot-count" wire:transition>Count: {{ $count }}</div>
                        </livewire:modal>
                    </div>
                    HTML;
                }
            },
            'modal' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>{{ $slot }}</div>
                    HTML;
                }
            },
        ])
            ->assertSeeIn('@slot-count', 'Count: 0')
            ->click('@increment')
            ->waitForTextIn('@result', 'Count: 1')
            ->assertSeeIn('@slot-count', 'Count: 1')
            ;
    }

    public function test_default_slots()
    {
        Livewire::visit([
            new class extends Component {
                public $count = 0;

                public function increment()
                {
                    $this->count++;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <h1>Parent Component</h1>

                        <span dusk="outer-count">{{ $count }}</span>

                        <livewire:modal>
                            <button dusk="outer-slot-button" wire:click="increment">Increment</button>
                            <span dusk="outer-slot-count">{{ $count }}</span>

                            <livewire:slot name="header">
                                <button dusk="outer-header-slot-button" wire:click="increment">Increment</button>
                                <span dusk="outer-header-slot-count">{{ $count }}</span>
                            </livewire:slot>
                        </livewire:modal>
                    </div>
                    HTML;
                }
            },
            'modal' => new class extends Component {
                public $count = 0;

                public function increment()
                {
                    $this->count++;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div dusk="modal">
                        <span dusk="inner-count">{{ $count }}</span>
                        <button dusk="inner-count-button" wire:click="increment">Increment</button>

                        <div class="modal-header">
                            {{ $slot('header') }}
                        </div>

                        <div class="modal-body">
                            {{ $slot }}
                        </div>

                        <div x-text="'loaded'"></div>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@outer-count', '0')
            ->assertSeeIn('@outer-slot-count', '0')
            ->assertSeeIn('@outer-header-slot-count', '0')
            ->assertSeeIn('@inner-count', '0')
            ->waitForLivewire()->click('@outer-slot-button')
            ->assertSeeIn('@outer-count', '1')
            ->assertSeeIn('@outer-slot-count', '1')
            ->assertSeeIn('@outer-header-slot-count', '1')
            ->assertSeeIn('@inner-count', '0')
            ->waitForLivewire()->click('@outer-header-slot-button')
            ->assertSeeIn('@outer-count', '2')
            ->assertSeeIn('@outer-slot-count', '2')
            ->assertSeeIn('@outer-header-slot-count', '2')
            ->assertSeeIn('@inner-count', '0')
            ->waitForLivewire()->click('@inner-count-button')
            ->assertSeeIn('@outer-count', '2')
            ->assertSeeIn('@outer-slot-count', '2')
            ->assertSeeIn('@outer-header-slot-count', '2')
            ->assertSeeIn('@inner-count', '1')
            ;
    }
}
