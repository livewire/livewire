<?php

namespace LegacyTests\Browser\SyncHistory;

use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;
use LegacyTests\Browser\DataBinding\Defer\Component as DeferComponent;

class Test extends TestCase
{
    function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped(); // Removed this entire system in favor of SPA mode.
        // Leaving this test here in case we want to use these tests for SPA mode...
    }

    public function test_route_bound_properties_are_synced_with_browser_history()
    {
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

    public function test_route_bound_properties_are_synced_with_browser_history_when_no_query_string_is_present()
    {
        $this->browse(function(Browser $browser) {
            $browser->visit(route('sync-history-without-query-string', [ 'step' => 1 ], false))->waitForText('Step 1 Active');

            $browser->waitForLivewire()->click('@step-2')->assertRouteIs('sync-history-without-query-string', [ 'step' => 2 ]);

            $browser->back()->assertRouteIs('sync-history-without-query-string', [ 'step' => 1 ]);
        });
    }

    public function test_that_query_bound_properties_are_synced_with_browser_history()
    {
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

    public function test_that_if_a_parameter_comes_in_from_the_route_and_doesnt_have_a_matching_property_things_dont_break()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history-without-mount', ['id' => 1], false))
                ->assertSeeIn('@output', '1')
                ->waitForLivewire()->click('@button')
                ->assertSeeIn('@output', '5');
        });
    }

    public function test_that_we_are_not_leaking_old_components_into_history_state_on_refresh()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->assertScript('Object.keys(window.history.state.livewire).length', 2)
                ->refresh()
                ->assertScript('Object.keys(window.history.state.livewire).length', 2);
        });
    }

    public function test_that_livewire_does_not_overwrite_existing_history_state()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->script('window.history.pushState({ ...window.history.state, userHistoryState: { foo: "bar" }}, document.title)');

            $browser->refresh()
                ->assertScript('Object.keys(window.history.state.userHistoryState).length', 1);
        });
    }

    public function test_that_we_are_not_setting_history_state_unless_there_are_route_bound_params_or_query_string_properties()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, DeferComponent::class)
                ->assertScript('history.state', null)
            ;
        });
    }

    public function test_that_changing_a_radio_multiple_times_and_hitting_back_multiple_times_works()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, SingleRadioComponent::class)
                ->waitForLivewire()->radio('@foo.bar', 'bar')
                ->assertRadioSelected('@foo.bar', 'bar')
                ->assertQueryStringHas('foo', 'bar')

                ->waitForLivewire()->radio('@foo.baz', 'baz')
                ->assertRadioSelected('@foo.baz', 'baz')
                ->assertRadioNotSelected('@foo.bar', 'bar')
                ->assertQueryStringHas('foo', 'baz')

                ->waitForLivewire()->radio('@foo.bar', 'bar')
                ->assertRadioSelected('@foo.bar', 'bar')
                ->assertQueryStringHas('foo', 'bar')

                ->back()
                ->assertRadioSelected('@foo.baz', 'baz')
                ->assertRadioNotSelected('@foo.bar', 'bar')
                ->assertQueryStringHas('foo', 'baz')

                ->back()
                ->assertRadioSelected('@foo.bar', 'bar')
                ->assertRadioNotSelected('@foo.baz', 'baz')
                ->assertQueryStringHas('foo', 'bar')

                ->back()
                ->assertRadioNotSelected('@foo.baz', 'baz')
                ->assertRadioNotSelected('@foo.bar', 'bar')
                ->assertQueryStringMissing('foo')
            ;
        });
    }

    public function test_that_alpine_watchers_used_by_entangle_are_fired_when_back_button_is_hit()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, ComponentWithAlpineEntangle::class)
                ->assertSeeIn('@blade.output', '1')
                ->assertSeeIn('@alpine.output', 'bar')
                ->waitForLivewire()->click('@next')
                ->assertSeeIn('@blade.output', '2')
                ->waitForLivewire()->click('@changeFoo')
                ->assertSeeIn('@alpine.output', 'baz')
                ->back()
                ->assertSeeIn('@blade.output', '1')
                ->assertSeeIn('@alpine.output', 'bar')
            ;
        });
    }

    public function test_optional_route_bound_properties_are_synced_with_browser_history()
    {
        $this->browse(function(Browser $browser) {
            $browser->visit(route('sync-history-with-optional-parameter', [], false))
                ->waitForText('Activate Step 1')
                ->waitForLivewire()
                ->click('@step-1')
                ->assertRouteIs('sync-history-with-optional-parameter', [ 'step' => 1 ])
                ->back()
                ->assertRouteIs('sync-history-with-optional-parameter', []);
        });
    }
}
