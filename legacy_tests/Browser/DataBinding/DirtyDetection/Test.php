<?php

namespace LegacyTests\Browser\DataBinding\DirtyDetection;

use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * If a value is changed server-side, the input updates.
                 */
                ->assertValue('@foo.input', 'initial')
                ->waitForLivewire()->click('@foo.button')
                ->assertValue('@foo.input', 'changed')

                /**
                 * If an uninitialized nested value is reset server-side, the input updates.
                 */
                ->assertValue('@bar.input', '')
                ->type('@bar.input', 'changed')
                ->pause(250)
                ->waitForLivewire()->click('@bar.button')
                ->assertValue('@bar.input', '')
            ;
        });
    }
}
