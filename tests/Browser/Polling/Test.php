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
                 * polling is disabled if livewire is offline
                 */
                ->assertSeeIn('@output', '1')
                ->tap(function (Browser $browser) {
                    $browser->offline();
                    $browser->pause(85);
                    $browser->assertSeeIn('@output', '1');
                    $browser->online();
                })

                /**
                 * polling without specifying method refreshes by default
                 */
                ->tap(function (Browser $browser) {
                    $browser->assertSeeIn('@output', '1');
                    $browser->pause(85);
                    $browser->assertSeeIn('@output', '2');
                })

                /**
                 * polling will stop if directive is removed
                 */
                ->tap(function (Browser $browser) {
                    $browser->pause(85);
                    $browser->assertSeeIn('@output', '3');
                    $browser->pause(85);
                    $browser->assertSeeIn('@output', '3');
                })

                /**
                 * polling will start if directive is added
                 * polling on root div
                 */
                ->tap(function (Browser $browser) {
                    $browser->waitForLivewire()->click('@button');
                    $browser->assertSeeIn('@output', '4');
                    $browser->pause(100);
                    $browser->assertSeeIn('@output', '5');
                })
            ;
        });
    }
}
