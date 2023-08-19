<?php

namespace Livewire\Features\SupportTransitions;

use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_transition_blade_conditional_dom_segments()
    {
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
                    <div dusk="target" wire:transition.duration.500ms>
                        Transition Me!
                    </div>
                    @endif
                </div>
                HTML; }
        })
        ->assertDontSee('@target')
        ->waitForLivewire()->click('@toggle')
        ->waitFor('@target')
        ->pause(100) // Let the transition start.
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).display', 'block')
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity > 0', true) // In progress.
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity < 0.75', true) // But not completed
        ->pause(600) // It really should have completed by now.
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity', 1)
        ->waitForLivewire()->click('@toggle')
        ->assertPresent('@target')
        ->pause(100) // Let the transition start.
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).display', 'block')
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity < 1', true) // In progress.
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity > 0.25', true) // But not completed.
        ->pause(600) // It really should have completed by now.
        ->assertMissing('@target')
        ;
    }
}
