<?php

namespace Tests\Browser\Security;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                // See middleware from original request.
                ->assertSeeIn('@middleware', '["web","Tests\\\\Browser\\\\ExpectedDummyMiddleware"]')
                ->assertDontSeeIn('@url', 'http://127.0.0.1:8001/livewire-dusk/Tests%5CBrowser%5CSecurity%5CComponent')

                ->waitForLivewire()->click('@refresh')

                // See that the original request middleware was re-applied.
                ->assertSeeIn('@middleware', '["web","Tests\\\\Browser\\\\ExpectedDummyMiddleware"]')
                ->assertSeeIn('@url', 'http://127.0.0.1:8001/livewire-dusk/Tests%5CBrowser%5CSecurity%5CComponent')

                ->waitForLivewire()->click('@showNested')

                // Even to nested components shown AFTER the first load.
                ->assertSeeIn('@middleware', '["web","Tests\\\\Browser\\\\ExpectedDummyMiddleware"]')
                ->assertSeeIn('@url', 'http://127.0.0.1:8001/livewire-dusk/Tests%5CBrowser%5CSecurity%5CComponent')
                ->assertSeeIn('@nested-middleware', '["web","Tests\\\\Browser\\\\ExpectedDummyMiddleware"]')
                ->assertSeeIn('@nested-url', 'http://127.0.0.1:8001/livewire-dusk/Tests%5CBrowser%5CSecurity%5CComponent')

                ->waitForLivewire()->click('@refreshNested')

                // Make sure they are still applied when stand-alone requests are made to that component.
                ->assertSeeIn('@middleware', '["web","Tests\\\\Browser\\\\ExpectedDummyMiddleware"]')
                ->assertSeeIn('@url', 'http://127.0.0.1:8001/livewire-dusk/Tests%5CBrowser%5CSecurity%5CComponent')
                ->assertSeeIn('@nested-middleware', '["web","Tests\\\\Browser\\\\ExpectedDummyMiddleware"]')
                ->assertSeeIn('@nested-url', 'http://127.0.0.1:8001/livewire-dusk/Tests%5CBrowser%5CSecurity%5CComponent')
            ;
        });
    }
}
