<?php

namespace Tests\Browser\Alpine;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * ->dispatchBrowserEvent()
                 */
                ->assertDontSeeIn('@foo.output', 'bar')
                ->waitForLivewire()->click('@foo.button')
                ->assertSeeIn('@foo.output', 'bar')

                /**
                 * Basic counter Alpine component.
                 */
                ->assertSeeIn('@bar.output', '0')
                ->click('@bar.button')
                ->assertSeeIn('@bar.output', '1')
                ->waitForLivewire()->click('@bar.refresh')
                ->assertSeeIn('@bar.output', '1')

                /**
                 * get, set, and call to Livewire from Alpine.
                 */
                ->assertSeeIn('@baz.output', '0')
                ->assertSeeIn('@baz.get', '0')
                ->assertSeeIn('@baz.get.proxy', '0')
                ->assertSeeIn('@baz.get.proxy.magic', '0')
                ->waitForLivewire()->click('@baz.set')
                ->assertSeeIn('@baz.output', '1')
                ->waitForLivewire()->click('@baz.set.proxy')
                ->assertSeeIn('@baz.output', '2')
                ->waitForLivewire()->click('@baz.set.proxy.magic')
                ->assertSeeIn('@baz.output', '3')
                ->waitForLivewire()->click('@baz.call')
                ->assertSeeIn('@baz.output', '4')
                ->waitForLivewire()->click('@baz.call.proxy')
                ->assertSeeIn('@baz.output', '5')
                ->waitForLivewire()->click('@baz.call.proxy.magic')
                ->assertSeeIn('@baz.output', '6')

                /**
                 * .call() return value
                 */
                ->assertDontSeeIn('@bob.output', '1')
                ->waitForLivewire()->click('@bob.button.await')
                ->assertSeeIn('@bob.output', '1')
                ->waitForLivewire()->click('@bob.button.promise')
                ->assertSeeIn('@bob.output', '2')

                /**
                 * $wire.entangle
                 */
                ->assertSeeIn('@lob.output', '6')
                ->waitForLivewire(function ($b) {
                    $b->click('@lob.increment');
                    $b->assertSeeIn('@lob.output', '6');
                })
                ->assertSeeIn('@lob.output', '7')
                ->waitForLivewire()->click('@lob.decrement')
                ->assertSeeIn('@lob.output', '6')

                /**
                 * Make sure property change from Livewire doesn't trigger an additional
                 * request because of @entangle.
                 */
                ->waitForLivewire(function ($b) {
                    $b->click('@lob.reset');
                    $b->assertSeeIn('@lob.output', '6');
                })
                ->pause(500)
                ->assertMissing('#livewire-error')
                ->assertSeeIn('@lob.output', '100')
                ;
        });
    }
}
