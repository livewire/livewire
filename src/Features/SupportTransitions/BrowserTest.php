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
                    <div dusk="target" wire:transition.duration.250ms>
                        Transition Me!
                    </div>
                    @endif
                </div>
                HTML; }
        })
        ->assertDontSee('@target')
        ->waitForLivewire()->click('@toggle')
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).display', 'block')
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity < 1', true)
        ->pause(250)
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).display', 'block')
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity', 1)
        ->pause(250)
        ->waitForLivewire()->click('@toggle')
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).display', 'block')
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).opacity', 1)
        ->pause(250)
        ->assertScript('getComputedStyle(document.querySelector(\'[dusk="target"]\')).display', 'none')
        ;
    }
}
