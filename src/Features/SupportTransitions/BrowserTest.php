<?php

namespace Livewire\Features\SupportTransitions;

use Livewire\Attributes\Transition;
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

    public function test_can_transition_dynamic_component_swap()
    {
        $animationsRunning = 'document.getAnimations().some(a => a.playState === "running")';

        Livewire::visit([
            new class extends \Livewire\Component {
                public $current = 'first-child';

                #[Transition(type: 'forward')]
                public function showSecond()
                {
                    $this->current = 'second-child';
                }

                #[Transition(type: 'backward')]
                public function showFirst()
                {
                    $this->current = 'first-child';
                }

                public function render() { return <<<'HTML'
                <div>
                    <style>
                        html:active-view-transition-type(forward) {
                            &::view-transition-old(content) {
                                animation: 500ms ease-out fade-out;
                            }
                            &::view-transition-new(content) {
                                animation: 500ms ease-in fade-in;
                            }
                        }

                        html:active-view-transition-type(backward) {
                            &::view-transition-old(content) {
                                animation: 500ms ease-out fade-out;
                            }
                            &::view-transition-new(content) {
                                animation: 500ms ease-in fade-in;
                            }
                        }

                        @keyframes fade-out {
                            to { opacity: 0; }
                        }

                        @keyframes fade-in {
                            from { opacity: 0; }
                        }
                    </style>

                    <livewire:is :component="$current" :wire:key="$current" />
                </div>
                HTML; }
            },
            'first-child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div wire:transition="content">
                    <p dusk="first-text">First Child</p>
                    <button wire:click="$parent.showSecond" dusk="show-second">Show Second</button>
                </div>
                HTML; }
            },
            'second-child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div wire:transition="content">
                    <p dusk="second-text">Second Child</p>
                    <button wire:click="$parent.showFirst" dusk="show-first">Show First</button>
                </div>
                HTML; }
            },
        ])
        ->assertSee('First Child')
        ->assertDontSee('Second Child')

        // Patch startViewTransition to check if viewTransitionName is set synchronously
        // within the update callback (not relying on async MutationObserver)...
        ->tap(fn ($browser) => $browser->script("
            let originalSVT = document.startViewTransition.bind(document);
            window.__transitionNameSetSynchronously = null;
            document.startViewTransition = function(configOrCallback) {
                let originalUpdate = typeof configOrCallback === 'function' ? configOrCallback : configOrCallback.update;
                let wrappedUpdate = function() {
                    let result = originalUpdate();
                    let allSet = Array.from(document.querySelectorAll('[wire\\\\:transition]')).every(
                        el => !!el.style.viewTransitionName
                    );
                    window.__transitionNameSetSynchronously = allSet;
                    return result;
                };
                if (typeof configOrCallback === 'function') {
                    return originalSVT(wrappedUpdate);
                }
                return originalSVT({ ...configOrCallback, update: wrappedUpdate });
            };
        "))

        // Swap to second child...
        ->waitForLivewire()->click('@show-second')
        ->waitUntil($animationsRunning)
        ->waitUntil("!$animationsRunning")
        ->assertSee('Second Child')
        ->assertDontSee('First Child')

        // Assert viewTransitionName was set synchronously during the update callback...
        ->assertScript('window.__transitionNameSetSynchronously', true)

        // Reset flag and swap back...
        ->tap(fn ($browser) => $browser->script("window.__transitionNameSetSynchronously = null;"))
        ->waitForLivewire()->click('@show-first')
        ->waitUntil($animationsRunning)
        ->waitUntil("!$animationsRunning")
        ->assertSee('First Child')
        ->assertDontSee('Second Child')

        ->assertScript('window.__transitionNameSetSynchronously', true)
        ;
    }
}
