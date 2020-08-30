<?php

namespace Tests\Browser\Offline;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->assertMissing('@whileOffline')
                ->tap(function (Browser $browser) {
                    $this->assertTrue($browser->driver->executeScript('return window.livewire.components.livewireIsOffline === false'), 'Livewire is offline');
                })
                ->offline()
                ->tap(function (Browser $browser) {
                    $this->assertTrue($browser->driver->executeScript('return window.livewire.components.livewireIsOffline === true'), 'Livewire is online');
                })
                ->assertSeeIn('@whileOffline', 'Offline')
                ->online()
                ->assertMissing('@whileOffline')

                /**
                 * add element class while offline
                 */
                ->online()
                ->assertClassMissing('@addClass', 'foo')
                ->offline()
                ->assertHasClass('@addClass', 'foo')

                /**
                 * add element class while offline
                 */
                ->online()
                ->assertHasClass('@removeClass', 'hidden')
                ->offline()
                ->assertClassMissing('@removeClass', 'hidden')

                /**
                 * add element attribute while offline
                 */
                ->online()
                ->assertAttributeMissing('@withAttribute', 'disabled')
                ->offline()
                ->assertAttribute('@withAttribute', 'disabled', 'true')

                /**
                 * remove element attribute while offline
                 */
                ->online()
                ->assertAttribute('@withoutAttribute', 'disabled', 'true')
                ->offline()
                ->assertAttributeMissing('@withoutAttribute', 'disabled')
            ;
        });
    }
}
