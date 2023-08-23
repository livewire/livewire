<?php

namespace Livewire\Features\SupportTransitions;

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
}
