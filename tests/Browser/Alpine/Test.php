<?php

namespace Tests\Browser\Alpine;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Alpine\Component;

class Test extends TestCase
{
    /** @test */
    public function happy_path()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * ->dispatchBrowserEvent()
                 */
                ->assertDontSeeIn('@foo.output', 'bar')
                ->click('@foo.button')
                ->waitForLivewire()
                ->assertSeeIn('@foo.output', 'bar')

                /**
                 * Basic counter Alpine component.
                 */
                ->assertSeeIn('@bar.output', '0')
                ->click('@bar.button')
                ->assertSeeIn('@bar.output', '1')
                ->click('@bar.refresh')
                ->waitForLivewire()
                ->assertSeeIn('@bar.output', '1')

                /**
                 * get, set, and call to Livewire from Alpine.
                 */
                ->assertSeeIn('@baz.output', '0')
                ->assertSeeIn('@baz.get', '0')
                ->assertSeeIn('@baz.get.proxy', '0')
                ->assertSeeIn('@baz.get.proxy.magic', '0')
                ->click('@baz.set')
                ->waitForLivewire()
                ->assertSeeIn('@baz.output', '1')
                ->click('@baz.set.proxy')
                ->waitForLivewire()
                ->assertSeeIn('@baz.output', '2')
                ->click('@baz.set.proxy.magic')
                ->waitForLivewire()
                ->assertSeeIn('@baz.output', '3')
                ->click('@baz.call')
                ->waitForLivewire()
                ->assertSeeIn('@baz.output', '4')
                ->click('@baz.call.proxy')
                ->waitForLivewire()
                ->assertSeeIn('@baz.output', '5')
                ->click('@baz.call.proxy.magic')
                ->waitForLivewire()
                ->assertSeeIn('@baz.output', '6')

                /**
                 * .call() return value
                 */
                ->assertDontSeeIn('@bob.output', '1')
                ->click('@bob.button.await')
                ->waitForLivewire()
                ->assertSeeIn('@bob.output', '1')
                ->click('@bob.button.promise')
                ->waitForLivewire()
                ->assertSeeIn('@bob.output', '2')
                ;
        });
    }
}
