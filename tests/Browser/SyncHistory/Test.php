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
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active');

            $browser->waitForLivewire()->click('@step-2')
                ->assertRouteIs('sync-history', ['step' => 2]);

            $browser
                ->back()
                ->assertRouteIs('sync-history', ['step' => 1]);
        });
    }

    public function test_that_query_bound_properties_are_synced_with_browser_history()
    {
        $this->require74();

        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');

            $browser->waitForLivewire()->click('@toggle-help')
                ->assertQueryStringHas('showHelp', 'true');

            $browser->waitForLivewire()->click('@toggle-help')
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');

            $browser->back()
                ->waitForText('Help is currently enabled')
                ->assertQueryStringHas('showHelp', 'true');

            $browser->back()
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');
        });
    }

    public function test_that_route_and_query_bound_properties_can_both_be_synced_with_browser_history()
    {
        $this->require74();

        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active')
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');

            $browser->waitForLivewire()->click('@toggle-help')
                ->assertQueryStringHas('showHelp', 'true');

            $browser->waitForLivewire()->click('@step-2')
                ->assertRouteIs('sync-history', ['step' => 2])
                ->assertQueryStringHas('showHelp', 'true');

            $browser->waitForLivewire()->click('@toggle-help')
               ->assertQueryStringHas('showHelp', 'false');

            $browser->back()
                ->waitForText('Help is currently enabled')
                ->assertQueryStringHas('showHelp', 'true')
                ->assertRouteIs('sync-history', ['step' => 2]);

            $browser->back()
                ->waitForText('Step 1 Active')
                ->assertRouteIs('sync-history', ['step' => 1])
                ->assertQueryStringHas('showHelp', 'true');

            $browser->back()
               ->waitForText('Help is currently disabled')
               ->assertQueryStringHas('showHelp', 'false');
        });
    }

    public function test_that_query_updates_from_child_components_can_coexist()
    {
        $this->require74();

        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active')
                ->waitForText('Dark mode is currently disabled')
                ->assertQueryStringHas('darkmode', 'false');

            $browser->waitForLivewire()->click('@toggle-darkmode')
                ->assertQueryStringHas('darkmode', 'true');

            $browser->waitForLivewire()->click('@step-2')
                ->assertRouteIs('sync-history', ['step' => 2])
                ->assertQueryStringHas('darkmode', 'true');

            $browser->waitForLivewire()->click('@toggle-darkmode')
                ->assertQueryStringHas('darkmode', 'false');

            $browser->back()
                ->waitForText('Dark mode is currently enabled')
                ->assertQueryStringHas('darkmode', 'true')
                ->assertRouteIs('sync-history', ['step' => 2]);

            $browser->back()
                ->waitForText('Step 1 Active')
                ->assertRouteIs('sync-history', ['step' => 1])
                ->assertQueryStringHas('darkmode', 'true');

            $browser->back()
                ->assertRouteIs('sync-history', ['step' => 1])
                ->waitForText('Dark mode is currently disabled')
                ->assertQueryStringHas('darkmode', 'false');
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
