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

            $browser->click('@step-2')
                ->waitForText('Step 2 Active')
                ->assertRouteIs('sync-history', ['step' => 2]);

            $browser->back()
                ->waitForText('Step 1 Active')
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

            $browser->click('@toggle-help')
                ->waitForText('Help is currently enabled')
                ->assertQueryStringHas('showHelp', 'true');

            $browser->click('@toggle-help')
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
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active')
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');
            
            $browser->click('@toggle-help')
                ->waitForText('Help is currently enabled')
                ->assertQueryStringHas('showHelp', 'true');
            
            $browser->click('@step-2')
                ->waitForText('Step 2 Active')
                ->assertRouteIs('sync-history', ['step' => 2])
                ->assertQueryStringHas('showHelp', 'true');
            
            $browser->click('@toggle-help')
               ->waitForText('Help is currently disabled')
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
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active')
                ->waitForText('Darkmode is currently disabled')
                ->assertQueryStringHas('darkmode', 'false');
            
            $browser->click('@toggle-darkmode')
                ->waitForText('Darkmode is currently enabled')
                ->assertQueryStringHas('darkmode', 'true');
            
            $browser->click('@step-2')
                ->waitForText('Step 2 Active')
                ->assertRouteIs('sync-history', ['step' => 2])
                ->assertQueryStringHas('darkmode', 'true');
            
            $browser->click('@toggle-darkmode')
               ->waitForText('Darkmode is currently disabled')
               ->assertQueryStringHas('darkmode', 'false');
            
            $browser->back()
                ->waitForText('Darkmode is currently enabled')
                ->assertQueryStringHas('darkmode', 'true')
                ->assertRouteIs('sync-history', ['step' => 2]);
            
            $browser->back()
                ->waitForText('Step 1 Active')
                ->assertRouteIs('sync-history', ['step' => 1])
                ->assertQueryStringHas('darkmode', 'true');

            $browser->back()
               ->waitForText('Darkmode is currently disabled')
               ->assertQueryStringHas('showDarkmode', 'false');
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
