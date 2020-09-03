<?php

namespace Tests\Browser\SyncHistory;

use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        // FIXME: We need a PHP 7.3 and below test
        if (PHP_VERSION_ID < 70400) {
            $this->markTestSkipped('Route-bound parameters are only supported in PHP 7.4 and above.');
            return;
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('/livewire-dusk/tests/browser/sync-history/hera/aphrodite?foo=baz')
                /*
                 * Check that the intial property value is set from the query string.
                 */
                ->assertSeeIn('@parent-output', 'via-route:hera(column)')
                ->assertSeeIn('@child-output', 'via-parent:aphrodite(column)')
                ->assertInputValue('@parent-input', 'via-route:hera(column)')
                ->assertInputValue('@child-input', 'via-parent:aphrodite(column)');

                /**
                 * Change a property and see it reflected in the query string.
                 */
                //->waitForLivewire()->type('@input', 'bob')
                //->assertSeeIn('@output', 'bob')
                //->assertInputValue('@input', 'bob')
                //->assertQueryStringHas('foo', 'bob')

                /**
                 * Hit the back button and see the change reflected in both
                 * the query string AND the actual property value.
                 */
                //->waitForLivewire()->back()
                //->assertSeeIn('@output', 'baz')
                //->assertQueryStringHas('foo', 'baz')

                /**
                 * Setting a property to a value marked as "except"
                 * removes the property entirely from the query string.
                 */
                //->assertSeeIn('@bar-output', 'baz')
                //->assertQueryStringHas('bar')
                //->waitForLivewire()->type('@bar-input', 'except-value')
                //->assertQueryStringMissing('bar')

                /**
                 * Add a nested component on the page and make sure
                 * both components play nice with each other.
                 */
                //->assertQueryStringMissing('baz')
                //->waitForLivewire()->click('@show-nested')
                //->pause(25)
                //->assertQueryStringHas('baz', 'bop')
                //->assertSeeIn('@baz-output', 'bop')
                //->waitForLivewire()->type('@baz-input', 'lop')
                //->assertQueryStringHas('baz', 'lop')
                //->waitForLivewire()->type('@input', 'plop')
                //->waitForLivewire()->type('@baz-input', 'ploop')
                //->assertQueryStringHas('foo', 'plop')
                //->assertQueryStringHas('baz', 'ploop')
                //->back()
                //->back()
                //->assertQueryStringHas('baz', 'lop');
        });
    }
}
