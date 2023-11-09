<?php

namespace Livewire\Features\SupportTransitions;

use Illuminate\Support\Arr;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_transition_blade_conditional_dom_segments()
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

    /** @test */
    public function elements_the_contain_transition_are_displayed_on_page_load()
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

    /** @test */
    public function removing_element_from_an_iteration_should_not_disappear_before_transition_out()
    {
        $isInline = fn($target) => 'getComputedStyle(document.querySelector(\'[dusk="' . $target . '"]\')).display === "inline"';

        Livewire::visit(
            new class extends \Livewire\Component {

                public $lines = [
                    'target1' => 'code',
                    'target2' => '    indented code',
                    'target3' => '/code',
                ];


                function remove()
                {
                    unset($this->lines['target2']);
                }

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <button wire:click='remove' dusk='remove'>Remove</button>

                    <pre><code class='text-xs'>@foreach($lines as $index => $line)<span class='inline-block' wire:key='{{ $index }}' wire:transition.fade.duration.2000ms dusk='{{ $index }}'>{!! $line !!}</span>
                @endforeach</code></pre>
                </div>
                HTML;
                }
            })
            // Removing an element in the middle
            ->assertPresent('@target2')
            ->waitForLivewire()->click('@remove')
            ->assertScript($isInline('target2'), true) // That should not have changed yet.
            ->waitUntilMissing('@target2')
            ->assertMissing('@target2');
    }


    /** @test */
    public function removing_last_element_from_an_iteration_should_only_remove_himself()
    {
        $opacity = fn($target) => 'parseFloat(getComputedStyle(document.querySelector(\'[dusk="' . $target . '"]\')).opacity, 10)';
        $isInline = fn($target) => 'getComputedStyle(document.querySelector(\'[dusk="' . $target . '"]\')).display === "inline"';
        Livewire::visit(
            new class extends \Livewire\Component {

                public $lines = [
                    'target1' => 'code',
                    'target2' => '    indented code',
                    'target3' => '/code',
                ];


                function remove()
                {
                    unset($this->lines['target3']);
                }

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <button wire:click='remove' dusk='remove'>Remove</button>

                    <pre><code class='text-xs'>@foreach($lines as $index => $line)<span class='inline-block' wire:key='{{ $index }}' wire:transition.fade.duration.2000ms dusk='{{ $index }}'>{!! $line !!}</span>
                @endforeach</code></pre>
                </div>
                HTML;
                }
            })
            // removing the last element
            ->assertVisible('@target1')
            ->assertVisible('@target3')
            ->waitForLivewire()->click('@remove')
            ->assertScript($isInline('target3'), true) // That should not have changed yet.
            ->waitUntilMissing('@target3')
            ->assertMissing('@target3')
            ->assertPresent('@target1')
            ->assertVisible('@target1')
            ->assertScript($isInline('target1'), true) // Still inline
            ->assertScript($opacity('target1'), 1) // Still opacity 1
        ;

    }

    /** @test */
    public function removing_last_element_from_an_iteration_should_only_remove_himself_with_transition()
    {
        $opacity = fn($target) => 'parseFloat(getComputedStyle(document.querySelector(\'[dusk="' . $target . '"]\')).opacity, 10)';
        $isInline = fn($target) => 'getComputedStyle(document.querySelector(\'[dusk="' . $target . '"]\')).display === "inline"';
        Livewire::visit(
            new class extends \Livewire\Component {

                public $lines = [
                    'target1' => 'code',
                    'target2' => '    indented code',
                    'target3' => '/code',
                ];


                function remove()
                {
                    unset($this->lines['target3']);
                }

                public function render()
                {
                    return <<<'HTML'
                <div>
                    <button wire:click='shift' dusk='shift'>Shift</button>
                    <button wire:click='remove' dusk='remove'>Remove</button>

                    <pre><code class='text-xs'>@foreach($lines as $index => $line)<span class='inline-block' wire:key='{{ $index }}' wire:transition.fade.duration.2000ms dusk='{{ $index }}'>{!! $line !!}</span>@endforeach</code></pre>
                </div>
                HTML;
                }
            })

            // removing the last element
            ->assertVisible('@target1')
            ->assertVisible('@target3')
            ->waitForLivewire()->click('@remove')
            ->assertScript($isInline('target3'), true) // That should not have changed yet.
            ->waitUntilMissing('@target3')
            ->assertMissing('@target3')
            ->assertPresent('@target1')
            ->assertVisible('@target1')
            ->assertScript($isInline('target1'), true) // Still inline
            ->assertScript($opacity('target1'), 1) // Still opacity 1
        ;

    }
}
