<?php

namespace LegacyTests\Browser\Headers;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Basic action (click).
                 */
                ->waitForLivewire()->click('@foo')
                ->assertSeeIn('@output', 'Bar')
                ->assertSeeIn('@altoutput', 'Bazz')
            ;
        });
    }
}
