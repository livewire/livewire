<?php

namespace LegacyTests\Browser\StringNormalization;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->markTestSkipped(); // @todo: Get test running by copying implementation from PR and updating to V3 style https://github.com/livewire/livewire/pull/4942 
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
