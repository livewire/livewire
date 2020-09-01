<?php

namespace Tests\Browser\GlobalLivewire;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\GlobalLivewire\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Event listeners are removed on teardown.
                 **/
                ->pause(250)
                ->tap(function ($b) { $b->script('window.livewire.stop()'); })
                ->click('@foo')
                ->pause(100)
                ->assertDontSeeIn('@output', 'foo')
                ->refresh()

                /**
                 * Rescanned components dont register twice.
                 **/
                ->tap(function ($b) { $b->script("livewire.rescan()"); })
                ->waitForLivewire()->click('@foo')
                ->assertSeeIn('@output', 'foo')
                ->refresh()

                /**
                 * window.livewire.onLoad callback is called when Livewire is initialized
                 */
                ->waitUsing(5, 25, function () use ($browser) {
                    return $browser->driver->executeScript("return window.isLoaded === true");
                })
            ;
        });
    }
}
