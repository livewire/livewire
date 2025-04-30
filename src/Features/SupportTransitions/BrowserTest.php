<?php

namespace Livewire\Features\SupportTransitions;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    public function test_can_transition_blade_conditional_dom_segments()
    {
        $opacity = 'parseFloat(getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity, 10)';
        $isBlock = 'getComputedStyle(document.querySelector(\'[dusk="target"]\')).display === "block"';

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

                    @if ($show)
                    <div dusk="target" wire:transition.duration.2000ms>
                        Transition Me!
                    </div>
                    @endif
                </div>
                HTML; }
        })
        ->assertDontSee('@target')
        ->waitForLivewire()->click('@toggle')
        ->waitFor('@target')
        ->waitUntil($isBlock)
        ->waitUntil("$opacity > 0 && $opacity < 1") // In progress.
        ->waitUntil("$opacity === 1") // Now it's done.
        ->assertScript($opacity, 1) // Assert that it's done.
        ->waitForLivewire()->click('@toggle')
        ->assertPresent('@target')
        ->assertScript($isBlock, true) // That should not have changed yet.
        ->waitUntil("$opacity > 0 && $opacity < 1") // In progress.
        ->waitUntilMissing('@target')
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
