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
                ->assertMissing('@output.offline')
                ->tap(function (Browser $browser) {
                    $this->assertTrue($browser->driver->executeScript('return window.livewire.components.livewireIsOffline === false'), 'Livewire is offline');
                })
                ->offline()
                ->tap(function (Browser $browser) {
                    $this->assertTrue($browser->driver->executeScript('return window.livewire.components.livewireIsOffline === true'), 'Livewire is online');
                })
                ->assertSeeIn('@output.offline', 'Offline')
                ->online()
                ->assertMissing('@output.offline')
            ;
        });
    }
}
