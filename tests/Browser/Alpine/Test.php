<?php

namespace Tests\Browser\Alpine;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Alpine\Component;

class Test extends TestCase
{
    /** @test */
    public function alpine()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->tinker()
                /**
                 * Increment a server-side and front-end counter simulaneously.
                 */
                ->assertSeeIn('@count.alpine', '0')
                ->assertSeeIn('@count.livewire', '0')
                ->click('@increment.alpine')
                ->assertSeeIn('@count.alpine', '1')
                ->click('@increment.livewire')
                ->waitForLivewire()
                ->assertSeeIn('@count.livewire', '1')
                ->assertSeeIn('@count.alpine', '1')
                ->click('@increment.alpine')
                ->assertSeeIn('@count.alpine', '2')
                ->assertSeeIn('@count.livewire', '1')
                ->click('@refresh')
                ->waitForLivewire()
                ->assertSeeIn('@count.alpine', '2')
                ->assertSeeIn('@count.livewire', '1')

                /**
                 * Increment the Livewire counter with the magic $wire.
                 */
                ->assertSeeIn('@count.wire', '1')
                ->click('@increment1.wire')
                ->waitForLivewire()
                ->click('@increment2.wire')
                ->waitForLivewire()
                ->assertSeeIn('@count.wire', '3')
                ->assertSeeIn('@count.livewire', '3')

                /**
                 * Increment the Livewire counter with the $wire.entangle().
                 */
                ->assertSeeIn('@count.entangle', '3')
                ->captureLivewireRequest()
                ->click('@increment.entangle')
                ->assertSeeIn('@count.entangle', '4')
                ->assertSeeIn('@count.livewire', '3')
                ->replayLivewireRequest()
                ->waitForLivewire()
                ->assertSeeIn('@count.livewire', '4')

                /**
                 * Increment the Livewire counter with the $wire.entangle().
                 */
                ->assertSeeIn('@count.method', '0')
                ->click('@refresh.method')
                ->captureLivewireRequest()
                ->assertSeeIn('@count.method', '0')
                ->replayLivewireRequest()
                ->waitForLivewire()
                ->assertSeeIn('@count.method', '4');
        });
    }
}
