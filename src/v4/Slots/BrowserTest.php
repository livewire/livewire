<?php

namespace Livewire\V4\Slots;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    public function test_basic_slot_rendering()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <h1>Parent Component</h1>
                        <wire:modal>
                            <p dusk="slot-content">This is slot content</p>
                        </wire:modal>
                    </div>
                    HTML;
                }
            },
            'modal' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="modal">
                        <div class="modal-body">
                            {!! $slot !!}
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertPresent('@modal')
            ->assertPresent('@slot-content')
            ->assertSeeIn('@modal', 'This is slot content');
    }

    public function test_named_slots_rendering()
    {
        Livewire::visit([
            new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <wire:modal>
                            <wire:slot name="header">
                                <h2 dusk="header-content">Modal Header</h2>
                            </wire:slot>

                            <p dusk="body-content">Modal body content</p>

                            <wire:slot name="footer">
                                <button dusk="footer-button">Close</button>
                            </wire:slot>
                        </wire:modal>
                    </div>
                    HTML;
                }
            },
            'modal' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="modal">
                        @if($slot->has('header'))
                            <div class="modal-header">{!! $slot->get('header') !!}</div>
                        @endif

                        <div class="modal-body">{!! $slot !!}</div>

                        @if($slot->has('footer'))
                            <div class="modal-footer">{!! $slot->get('footer') !!}</div>
                        @endif
                    </div>
                    HTML;
                }
            }
        ])
            ->assertPresent('@modal')
            ->assertPresent('@header-content')
            ->assertPresent('@body-content')
            ->assertPresent('@footer-button')
            ->assertSeeIn('@modal', 'Modal Header')
            ->assertSeeIn('@modal', 'Modal body content')
            ->assertSeeIn('@modal', 'Close');
    }

    public function test_slot_scope_binding_to_parent()
    {
        Livewire::visit([
            new class extends Component {
                public $name = 'John';
                public $clicked = false;

                public function updateName($newName)
                {
                    $this->name = $newName;
                }

                public function handleClick()
                {
                    $this->clicked = true;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <p dusk="parent-name">Parent name: {{ $name }}</p>
                        <p dusk="parent-clicked">Clicked: {{ $clicked ? 'true' : 'false' }}</p>

                        <wire:modal>
                            <input dusk="slot-input" type="text" wire:model="name" placeholder="Enter name">
                            <button dusk="slot-button" wire:click="handleClick">Click me</button>
                        </wire:modal>
                    </div>
                    HTML;
                }
            },
            'modal' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="modal">
                        <div class="modal-content">
                            {!! $slot !!}
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@parent-name', 'John')
            ->assertSeeIn('@parent-clicked', 'false')
            ->type('@slot-input', 'Jane')
            ->waitForLivewire()->blur('@slot-input')
            ->assertSeeIn('@parent-name', 'Jane')
            ->waitForLivewire()->click('@slot-button')
            ->assertSeeIn('@parent-clicked', 'true');
    }

    public function test_slot_content_updates_during_subsequent_renders()
    {
        Livewire::visit([
            new class extends Component {
                public $message = 'Initial message';
                public $counter = 0;

                public function updateMessage()
                {
                    $this->counter++;
                    $this->message = "Updated message {$this->counter}";
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button dusk="update-button" wire:click="updateMessage">Update</button>

                        <wire:display-component>
                            <wire:slot name="content">
                                <span dusk="dynamic-content">{{ $message }}</span>
                            </wire:slot>
                        </wire:display-component>
                    </div>
                    HTML;
                }
            },
            'display-component' => new class extends Component {
                public $componentCounter = 0;

                public function refresh()
                {
                    $this->componentCounter++;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div dusk="display">
                        <button dusk="child-refresh" wire:click="refresh">Child Refresh ({{ $componentCounter }})</button>
                        <div class="content">
                            {!! $slot->get('content') !!}
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertSeeIn('@dynamic-content', 'Initial message')
            ->waitForLivewire()->click('@update-button')
            ->assertSeeIn('@dynamic-content', 'Updated message 1')
            ->waitForLivewire()->click('@update-button')
            ->assertSeeIn('@dynamic-content', 'Updated message 2')
            // Test that child re-renders don't break slot content
            ->waitForLivewire()->click('@child-refresh')
            ->assertSeeIn('@dynamic-content', 'Updated message 2')
            ->assertSeeIn('@child-refresh', 'Child Refresh (1)');
    }

    public function test_empty_slots_are_handled_correctly()
    {
        Livewire::visit([
            new class extends Component {
                public $showHeader = false;

                public function toggleHeader()
                {
                    $this->showHeader = !$this->showHeader;
                }

                public function render()
                {
                    return <<<'HTML'
                    <div>
                        <button dusk="toggle-header" wire:click="toggleHeader">Toggle Header</button>

                        <wire:card>
                            @if($showHeader)
                                <wire:slot name="header">
                                    <h3 dusk="header-text">Card Header</h3>
                                </wire:slot>
                            @endif

                            <p dusk="body-text">Card body</p>
                        </wire:card>
                    </div>
                    HTML;
                }
            },
            'card' => new class extends Component {
                public function render()
                {
                    return <<<'HTML'
                    <div dusk="card">
                        @if($slot->has('header') && $slot->get('header')->isNotEmpty())
                            <div dusk="card-header" class="card-header">
                                {!! $slot->get('header') !!}
                            </div>
                        @else
                            <div dusk="no-header">No header provided</div>
                        @endif

                        <div class="card-body">
                            {!! $slot !!}
                        </div>
                    </div>
                    HTML;
                }
            }
        ])
            ->assertPresent('@no-header')
            ->assertNotPresent('@card-header')
            ->assertSeeIn('@card', 'No header provided')
            ->waitForLivewire()->click('@toggle-header')
            ->assertPresent('@card-header')
            ->assertNotPresent('@no-header')
            ->assertSeeIn('@card-header', 'Card Header')
            ->waitForLivewire()->click('@toggle-header')
            ->assertPresent('@no-header')
            ->assertNotPresent('@card-header');
    }
}
