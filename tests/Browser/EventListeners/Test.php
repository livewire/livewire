<?php

namespace Tests\Browser\EventListeners;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    /** @test */
    public function it_respects_a_static_listeners_list_defined_dynamically()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire(function ($browser) {
                    $browser->click('@foo');
                })
                ->assertSeeIn('@lastEvent', 'foo')
                ->assertSeeIn('@eventCount', '1')
                ->waitForLivewire(function ($browser) {
                    $browser->click('@bar');
                })
                ->assertSeeIn('@lastEvent', 'bar')
                ->assertSeeIn('@eventCount', '2')
                ->waitForLivewire(function ($browser) {
                    $browser->click('@baz');
                })
                ->assertSeeIn('@lastEvent', 'baz')
                ->assertSeeIn('@eventCount', '3');
        });
    }

    /** @test */
    public function it_does_not_handle_a_dynamically_removed_event_if_fired()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire(function ($browser) {
                    $browser->click('@remove2');
                })
                ->click('@bar')
                ->pause(50)
                ->assertDontSeeIn('@lastEvent', 'bar')
                ->assertSeeIn('@eventCount', '0');
        });
    }

    /** @test */
    public function it_does_not_error_when_a_dynamically_removed_event_is_fired()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire(function ($browser) {
                    $browser->click('@remove2');
                })
                ->click('@bar')
                ->pause(50)
                ->assertNoLivewireError();
        });
    }

    /** @test */
    public function it_handles_a_dynamically_added_event_when_fired()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->waitForLivewire(function ($browser) {
                    $browser->click('@add4');
                })
                ->actionTriggersLivewireMessage(function ($browser) {
                    $browser->click('@goo');
                })
                ->assertSeeIn('@lastEvent', 'goo')
                ->assertSeeIn('@eventCount', '1');
        });
    }

}
