<?php

namespace LegacyTests\Browser\StringNormalization;

use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        // @todo: This test passes now, but it probably no longer needed, as we're not needing to normalise strings to prevent corrupt payload exception.
        // Old todo: Get test running by copying implementation from both PR's and updating to V3 style https://github.com/livewire/livewire/pull/4942 and https://github.com/livewire/livewire/pull/5379
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * Click button to trigger string re-encoding in dehydrate
                 */
                ->waitForLivewire()->click('#add_number')
                ->pause('500')
                ->assertSee('Add Number') // current version throws an error in Safari
            ;
        });
    }
}
