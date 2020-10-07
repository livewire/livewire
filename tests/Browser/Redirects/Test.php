<?php

namespace Tests\Browser\Redirects;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Flashing a message shows up right away, AND
                 * will show up if you redirect to a different
                 * page right after.
                 */
                ->assertNotPresent('@flash.message')
                ->waitForLivewire()->click('@flash')
                ->assertPresent('@flash.message')
                ->waitForLivewire()->click('@refresh')
                ->assertNotPresent('@flash.message')
                ->click('@redirect-with-flash')->waitForReload()
                ->assertPresent('@flash.message')
                ->waitForLivewire()->click('@refresh')
                ->assertNotPresent('@flash.message')

                /**
                 * Livewire response is not handled if redirecting.
                 */
                ->refresh()
                ->assertSeeIn('@redirect.blade.output', 'foo')
                ->assertSeeIn('@redirect.alpine.output', 'foo')
                ->runScript('window.addEventListener("beforeunload", e => { e.preventDefault(); e.returnValue = ""; });')
                ->click('@redirect.button')
                ->pause(500)
                ->dismissDialog()
                ->assertSeeIn('@redirect.blade.output', 'foo')
                ->assertSeeIn('@redirect.alpine.output', 'foo')
            ;
        });
    }
}
