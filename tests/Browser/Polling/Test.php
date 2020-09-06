<?php

namespace Tests\Browser\Polling;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Enable polling by adding a wire:poll directive to an element.
                 */
                ->assertSeeIn('@output', '1')
                ->pause('500') // Wait the time for a wire:poll in the view.
                ->assertSeeIn('@output', '1')
                ->waitForLivewire()->click('@enable')
                ->assertSeeIn('@output', '2')
                ->waitForLivewire(function () {}) // Wait for the next Livewire roundtrip
                ->assertSeeIn('@output', '3')
                ->waitForLivewire(function () {})
                ->assertSeeIn('@output', '4')

                /**
                 * Disable polling by removing wire:poll from an element.
                 */
                ->waitForLivewire()->click('@disable')
                ->assertSeeIn('@output', '5')
                ->pause('500')
                ->assertSeeIn('@output', '5')

                /**
                 * Re-enable polling, then test that polling stops when offline and resumes when back online.
                 */
                ->waitForLivewire()->click('@enable')
                ->assertSeeIn('@output', '6')
                ->waitForLivewire(function () {})
                ->assertSeeIn('@output', '7')
                ->offline()
                ->pause('500')
                ->assertSeeIn('@output', '7')
                ->online()
                ->waitForLivewire(function () {})
                ->assertSeeIn('@output', '8')
            ;
        });
    }
}
