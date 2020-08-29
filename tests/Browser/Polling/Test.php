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
                ->assertSeeIn('@output', '1')->tap(function (Browser $browser) {
                    $browser->offline();
                    usleep(85 * 1000);
                    $browser->assertSeeIn('@output', '1');
                })

                /**
                 * polling without specifying method refreshes by default
                 */
                ->tap(function (Browser $browser) {
                    $browser->assertSeeIn('@output', 1);
                    $browser->online();
                    usleep(85 * 1000);
                    $browser->offline();
                    $browser->assertSeeIn('@output', '2');
            });
        });
    }
}
