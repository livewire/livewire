<?php

namespace LegacyTests\Browser\Events;

use LegacyTests\Browser\TestCase;

class ListenerTest extends TestCase
{
    /** @test */
    public function it_attaches_and_removes_event_listener_correctly()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, ListenerComponent::class)
                /**
                 * emit the event and ensure the listener is triggered
                 */
                ->waitForLivewire()->click('@emit.event')
                ->waitUsing(5, 75, function () use ($browser) {
                    return $browser->assertSeeIn('@messageDisplay', 'Test Message')
                        ->assertScript('window.eventReceived', 'Test Message');
                })

                /**
                 * emit the event again and ensure the listener is not triggered
                 */
                ->click('@emit.event')
                ->pause(100)
                ->assertDontSeeIn('@messageDisplay', 'Test Message')
                ->assertScript('window.eventReceived', 'Test Message');
        });
    }
}
