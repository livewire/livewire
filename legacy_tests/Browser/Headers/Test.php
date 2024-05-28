<?php

namespace LegacyTests\Browser\Headers;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->markTestSkipped(); // @todo: Caleb needs to think more deeply about JS hooks for V3...

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
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
