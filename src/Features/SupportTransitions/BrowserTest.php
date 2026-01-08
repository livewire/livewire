<?php

namespace Livewire\Features\SupportTransitions;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_transition_blade_conditional_dom_segments()
    {
        $animationsRunning = 'document.getAnimations().some(a => a.playState === "running")';

        Livewire::visit(
            new class extends \Livewire\Component {
                public $show = false;

                function toggle()
                {
                    $this->show = ! $this->show;
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="toggle" dusk="toggle">Toggle</button>

                    <style>
                        ::view-transition-old(transition) {
                            animation: 500ms ease-out fade-out;
                        }

                        ::view-transition-new(transition) {
                            animation: 500ms ease-in fade-in;
                        }

                        @keyframes fade-out {
                            to { opacity: 0; }
                        }

                        @keyframes fade-in {
                            from { opacity: 0; }
                        }
                    </style>

                    @if ($show)
                    <div dusk="target" wire:transition="transition">
                        Transition Me!
                    </div>
                    @endif
                </div>
                HTML; }
        })
        ->assertDontSee('@target')

        ->waitForLivewire()->click('@toggle')
        ->waitUntil($animationsRunning) // In progress.
        ->waitUntil("!$animationsRunning") // Now it's done.
        ->assertPresent('@target')

        ->waitForLivewire()->click('@toggle')
        ->waitUntil($animationsRunning) // In progress.
        ->waitUntil("!$animationsRunning") // Now it's done.
        ->assertMissing('@target')
        ;
    }

    public function test_elements_the_contain_transition_are_displayed_on_page_load()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $messages = [
                    'message 1',
                    'message 2',
                    'message 3',
                    'message 4',
                ];

                public function addMessage()
                {
                    $this->messages[] = 'message ' . count($this->messages) + 1;
                }

                public function render()
                {
                    return <<< 'HTML'
                    <div>
                        <ul class="text-xs">
                            @foreach($messages as $index => $message)
                                <li wire:transition.fade.duration.1000ms dusk="message-{{ $index + 1 }}">{{$message}}</li>
                            @endforeach
                        </ul>

                        <button type="button" wire:click="addMessage" dusk="add-message">Add message</button>
                    </div>
                    HTML;
                }
            }
        )
        ->assertVisible('@message-1')
        ->assertVisible('@message-2')
        ->assertVisible('@message-3')
        ->assertVisible('@message-4')
        ;
    }
}
