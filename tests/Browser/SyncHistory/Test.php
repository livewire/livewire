<?php

namespace Tests\Browser\SyncHistory;

use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test_route_bound_properties_are_synced_with_browser_history()
    {
        $this->require74();

        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['user' => 1], false))
                ->assertSeeIn('h1', 'Current: @danielcoulbourne');

            $browser->click('@user-2')
                ->waitForText('Current: @calebporzio')
                ->assertRouteIs('sync-history', ['user' => 2]);

            $browser->back()
                ->waitForText('Current: @danielcoulbourne')
                ->assertRouteIs('sync-history', ['user' => 1]);
        });
    }

    public function test_that_query_bound_properties_are_synced_with_browser_history()
    {
        $this->require74();

        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['user' => 1], false))
                ->waitForText('not-liked')
                ->assertQueryStringHas('liked', 'false');

            $browser->click('@toggle-like')
                ->waitForText('liked')
                ->assertQueryStringHas('liked', 'true');

            $browser
                ->click('@toggle-like')
                ->waitForText('not-liked')
                ->assertQueryStringHas('liked', 'false');

            $browser->back()
                ->waitForText('liked')
                ->assertQueryStringHas('liked', 'true');

            $browser->back()
                ->waitForText('not-liked')
                ->assertQueryStringHas('liked', 'false');
        });
    }

    public function test_that_route_and_query_bound_properties_can_both_be_synced_with_browser_history()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['user' => 1], false))
                ->pause(2000)
                ->waitForText('Current: @danielcoulbourne')
                ->waitForText('not-liked')
                ->assertQueryStringHas('liked', 'false');

            $browser->click('@toggle-like')
                ->waitForText('liked')
                ->assertQueryStringHas('liked', 'true');

            $browser->click('@user-2')
                ->waitForText('Current: @calebporzio')
                ->assertRouteIs('sync-history', ['user' => 2])
                ->assertQueryStringHas('liked', 'true');

            $browser->click('@toggle-like')
                ->waitForText('not-liked')
                ->assertQueryStringHas('liked', 'false')
                ->tinker();
            // FIXME: something is causing livewire to get corrupt data after this. figue this out

            $browser->back()
                ->waitForText('liked')
                ->assertQueryStringHas('liked', 'true')
                ->assertRouteIs('sync-history', ['user' => 2]);

            $browser->back()
                ->waitForText('Current: @danielcoulbourne')
                ->assertRouteIs('sync-history', ['user' => 1]);

            $browser->back()
                ->waitForText('not-liked')
                ->assertQueryStringHas('liked', 'false');
        });
    }

    protected function require74()
    {
        // FIXME: We need a PHP 7.3 and below test

        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('Route-bound parameters are only supported in PHP 7.4 and above.');
            return;
        }
    }
}
