<?php

namespace LegacyTests\Browser\GlobalLivewire;

use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->markTestSkipped(); // @todo: Caleb needs to think more deeply about JS hooks for V3...

        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * Event listeners are removed on teardown.
                 **/
                ->pause(250)
                ->tap(function ($b) { $b->script('window.Livewire.stop()'); })
                ->click('@foo')
                ->pause(100)
                ->assertDontSeeIn('@output', 'foo')
                ->refresh()

                /**
                 * Rescanned components dont register twice.
                 **/
                ->tap(function ($b) { $b->script('window.Livewire.rescan()'); })
                ->waitForLivewire()->click('@foo')
                ->assertSeeIn('@output', 'foo')
                ->refresh()

                /**
                 * window.Livewire.onLoad callback is called when Livewire is initialized
                 */
                ->assertScript('window.isLoaded', true)

                /**
                 * livewire:load DOM event is fired after start
                 */
                ->assertScript('window.loadEventWasFired', true)
            ;
        });
    }
}
