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
            Livewire::visit($browser, TestComponent::class)
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
            Livewire::visit($browser, TestComponent::class)
                ->waitForLivewire(function ($browser) {
                    $browser->click('@removeBar');
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
            Livewire::visit($browser, TestComponent::class)
                ->waitForLivewire(function ($browser) {
                    $browser->click('@removeBar');
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
            Livewire::visit($browser, TestComponent::class)
                ->waitForLivewire(function ($browser) {
                    $browser->click('@addGoo');
                })
                ->assertLivewireSendsMessage(function ($browser) {
                    $browser->click('@goo');
                })
                ->assertSeeIn('@lastEvent', 'goo')
                ->assertSeeIn('@eventCount', '1');
        });
    }

    /** @test */
    public function it_works_as_expected_with_embedded_components() {
        $this->browse(function ($browser) {
            $browser = Livewire::visit($browser, ParentComponent::class)
                ->assertLivewireSendsMessage(function ($browser) {
                    $browser->click('@emitFoo');
                })
                ->assertSeeIn('@parent_lastEvent', 'foo')
                ->assertSeeIn('@parent_eventCount', '1')
                ->assertSeeIn('@child_lastEvent', 'foo')
                ->assertSeeIn('@child_eventCount', '1')
                ->waitForLivewire(function ($browser) {
                    $browser->click('@parent_removeBar');
                })
                ->assertLivewireSendsMessage(function($browser) {
                    $browser->click('@emitBar');
                })
                ->assertSeeIn('@parent_lastEvent', 'foo')
                ->assertSeeIn('@parent_eventCount', '1')
                ->assertSeeIn('@child_lastEvent', 'bar')
                ->assertSeeIn('@child_eventCount', '2')
                ->waitForLivewire(function ($browser) {
                    $browser->click('@child_removeBaz');
                })
                ->assertLivewireSendsMessage(function($browser) {
                    $browser->click('@emitBaz');
                })
                ->assertSeeIn('@parent_lastEvent', 'baz')
                ->assertSeeIn('@parent_eventCount', '2')
                ->assertSeeIn('@child_lastEvent', 'bar')
                ->assertSeeIn('@child_eventCount', '2')
                ->waitForLivewire(function ($browser) {
                    $browser->click('@child_removeBar');
                })
                ->assertLivewireDoesNotSendMessage(function($browser) {
                    $browser->click('@emitBar');
                    $browser->click('@emitGoo');
                })
                ->assertSeeIn('@parent_lastEvent', 'baz')
                ->assertSeeIn('@parent_eventCount', '2')
                ->assertSeeIn('@child_lastEvent', 'bar')
                ->assertSeeIn('@child_eventCount', '2')
                ->waitForLivewire(function ($browser) {
                    $browser->click('@parent_addGoo');
                })
                ->assertLivewireSendsMessage(function($browser) {
                    $browser->click('@emitGoo');
                })
                ->assertSeeIn('@parent_lastEvent', 'goo')
                ->assertSeeIn('@parent_eventCount', '3')
                ->assertSeeIn('@child_lastEvent', 'bar')
                ->assertSeeIn('@child_eventCount', '2')
                ->waitForLivewire(function ($browser) {
                    $browser->click('@child_addGoo');
                })
                ->assertLivewireSendsMessage(function($browser) {
                    $browser->click('@emitGoo');
                })
                ->assertSeeIn('@parent_lastEvent', 'goo')
                ->assertSeeIn('@parent_eventCount', '4')
                ->assertSeeIn('@child_lastEvent', 'goo')
                ->assertSeeIn('@child_eventCount', '3');
        });
    }

}
