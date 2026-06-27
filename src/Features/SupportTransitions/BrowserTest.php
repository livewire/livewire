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

    public function test_wire_transition_names_are_cleared_after_transition_completes()
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
                            animation: 300ms ease-out fade-out;
                        }
                        ::view-transition-new(transition) {
                            animation: 300ms ease-in fade-in;
                        }
                        @keyframes fade-out { to { opacity: 0; } }
                        @keyframes fade-in { from { opacity: 0; } }
                    </style>

                    @if ($show)
                    <div dusk="target" wire:transition="transition">
                        Transition Me!
                    </div>
                    @endif
                </div>
                HTML; }
        })
        // Toggle on to trigger a transition...
        ->waitForLivewire()->click('@toggle')
        ->waitUntil($animationsRunning)
        ->waitUntil("!$animationsRunning")
        ->assertPresent('@target')

        // After transition completes, viewTransitionName should be cleared
        // to avoid creating a permanent stacking context...
        ->assertScript(
            "document.querySelector('[dusk=\"target\"]').style.viewTransitionName",
            ''
        )
        ;
    }

    public function test_transition_is_skipped_when_dialog_opens_during_morph()
    {
        Livewire::visit(
            new class extends \Livewire\Component {
                public $items = ['Item A', 'Item B'];
                public $showModal = false;

                public function openModal()
                {
                    $this->showModal = true;
                }

                public function render() { return <<<'HTML'
                <div>
                    <style>
                        ::view-transition-old(*) { animation: 2s ease-out fade-out; }
                        ::view-transition-new(*) { animation: 2s ease-in fade-in; }
                        @keyframes fade-out { to { opacity: 0; } }
                        @keyframes fade-in { from { opacity: 0; } }
                    </style>

                    @foreach ($items as $index => $item)
                        <div
                            wire:transition="card-{{ $index }}"
                            wire:key="item-{{ $index }}"
                            dusk="card-{{ $index }}"
                        >
                            {{ $item }}
                        </div>
                    @endforeach

                    <button wire:click="openModal" dusk="open">Open</button>

                    <dialog
                        wire:ignore.self
                        x-ref="modal"
                        x-effect="
                            if ($wire.showModal) { if (!$refs.modal.open) $refs.modal.showModal() }
                        "
                    >
                        <p>Modal Content</p>
                    </dialog>
                </div>
                HTML; }
            }
        )
        ->waitForLivewire()->click('@open')
        ->waitFor('dialog[open]')

        // Without the fix, the 2s view transition animations would still be playing
        // and the transitioning elements would appear above the dialog.
        // With the fix, the transition is skipped the instant the dialog opens...
        ->assertScript('document.getAnimations().some(a => a.playState === "running")', false)
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

    public function test_unnamed_wire_transition_rides_with_parent_during_typed_transition()
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

                public function render() { return <<<'HTML'
                <div>
                    <livewire:is :component="$current" :wire:key="$current" />
                </div>
                HTML; }
            },
            'first-child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div wire:transition="slide">
                    <button wire:click="$parent.showSecond" dusk="show-second">Show Second</button>
                </div>
                HTML; }
            },
            'second-child' => new class extends \Livewire\Component {
                public function render() { return <<<'HTML'
                <div wire:transition="slide">
                    <p dusk="second-text">Second Child</p>

                    <div wire:transition dusk="inner">
                        <p>Inner content</p>
                    </div>
                </div>
                HTML; }
            },
        ])
        ->assertSee('Show Second')

        ->waitForLivewire()->click('@show-second')
        ->waitUntil($animationsRunning)

        // The named outer wire:transition is still given its explicit name so it
        // animates as an independent group with the user's "slide" CSS...
        ->assertScript(
            "document.querySelector('[wire\\\\:transition=\"slide\"]').style.viewTransitionName",
            'slide'
        )

        // The unnamed inner wire:transition is intentionally NOT given a
        // view-transition-name during a typed transition. This lets the browser
        // capture it as part of its parent's snapshot so it rides along with the
        // orchestrated transition instead of popping with a default UA fade...
        ->assertScript(
            "document.querySelector('[dusk=\"inner\"]').style.viewTransitionName",
            ''
        )

        ->waitUntil("!$animationsRunning")
        ->assertSee('Second Child')
        ;
    }

    public function test_unnamed_wire_transition_still_animates_independently_during_regular_morph()
    {
        $animationsRunning = 'document.getAnimations().some(a => a.playState === "running")';

        Livewire::visit(
            new class extends \Livewire\Component {
                public bool $expanded = false;

                public function toggle()
                {
                    $this->expanded = ! $this->expanded;
                }

                public function render() { return <<<'HTML'
                <div>
                    <button wire:click="toggle" dusk="toggle">Toggle</button>

                    @if ($expanded)
                        <p dusk="extra">Extra content</p>
                    @endif

                    <div wire:transition dusk="target">
                        Footer
                    </div>
                </div>
                HTML; }
            }
        )
        ->waitForLivewire()->click('@toggle')
        ->waitUntil($animationsRunning)

        // No transition type is active, so unnamed wire:transition elements should
        // still get an auto-generated view-transition-name and animate independently
        // for in-place layout shifts...
        ->assertScript(
            "document.querySelector('[dusk=\"target\"]').style.viewTransitionName !== ''",
            true
        )

        ->waitUntil("!$animationsRunning")
        ;
    }
}
