<?php

namespace LegacyTests\Browser\PollingViewport;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->assertSeeIn('@output', '1')
                ->waitForLivewire(function () {})
                ->assertSeeIn('@output', '2')
                ->scrollTo('#bottom')
                ->pause(2000)
                ->scrollTo('#top')
                ->waitForLivewire(function () {})
                ->assertSeeIn('@output', '3');
        });
    }
}
