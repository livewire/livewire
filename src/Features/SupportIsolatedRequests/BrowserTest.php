<?php

namespace Livewire\Features\SupportIsolatedRequests;

use Tests\BrowserTestCase;
use Livewire\Livewire;
use Livewire\Component;

class BrowserTest extends BrowserTestCase
{
    /** @test */
    public function can_interact_with_fast_component_while_slow_loads()
    {
        Livewire::visit([new class extends Component {
            public function render() { return <<<HTML
                <div>
                    <livewire:slow lazy isolate />
                    <livewire:fast isolate />
                </div>
            HTML; }
        }, 'slow' => new class extends Component {
            public $slowToggle = false;
            public function render() {
                sleep(2);
                return <<<'HTML'
                    <div id="slow">
                        Slow Toggle?
                        <input type="checkbox" wire:model.live="slowToggle" dusk="slowToggle">
                        {{ $slowToggle ? 'Slow Checked' : 'Slow Not Checked' }}
                    </div>
                HTML;
            }
        }, 'fast' => new class extends Component {
            public $fastToggle = false;
            public function render() {
                return <<<'HTML'
                    <div id="fast">
                        Fast Toggle?
                        <input type="checkbox" wire:model.live="fastToggle" dusk="fastToggle">
                        {{ $fastToggle ? 'Fast Checked' : 'Fast Not Checked' }}
                    </div>
                HTML;
            }
        }])
            ->waitForLivewireToLoad()
            // should be able to see "Fast Toggle?", but not yet "Slow Toggle?"
            ->assertSee('Fast Toggle')
            ->assertDontSee('Slow Toggle')
            // clicking on the fast toggle should reflect changes, even though slow toggle hasn't loaded yet
            ->click('@fastToggle')
            ->waitForText('Fast Checked')
            ->assertDontSee('Slow Toggle')
            // clicking again should also reflect changes, even though slow toggle still hasn't loaded yet
            ->click('@fastToggle')
            ->waitForText('Fast Not Checked')
            ->assertDontSee('Slow Toggle')
            // wait for slow toggle to load
            ->waitFor('#slow')
            ->assertSee('Slow Toggle')
            // now try clicking the slow toggle, and while it is still loading, test that it isn't blocking the fast toggle again
            ->click('@slowToggle')
            // should still show slow is not checked
            ->assertSee('Slow Not Checked')
            // clicking fast toggle should update that while still waiting for slow toggle change
            ->click('@fastToggle')
            ->waitForText('Fast Checked')
            ->assertSee('Slow Not Checked')
            // clicking fast toggle should again update that while still waiting for slow toggle change
            ->click('@fastToggle')
            ->waitForText('Fast Not Checked')
            ->assertSee('Slow Not Checked')
            // eventually slow toggle should reflect that it is now checked
            ->waitForText('Slow Checked')
            ->assertSee('Slow Checked')
        ;
    }
}

