<?php

namespace Tests\Browser\PushState;

use Livewire\Livewire;
use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class, '?foo=baz')
                /**
                 * Check that the intial property value is set from the query string.
                 */
                ->assertSeeIn('@output', 'baz')
                ->assertInputValue('@input', 'baz')

                /**
                 * Change a property and see it reflected in the query string.
                 */
                ->type('@input', 'bob')
                ->waitForLivewire()
                ->assertSeeIn('@output', 'bob')
                ->assertInputValue('@input', 'bob')
                ->assertQueryStringHas('foo', 'bob')

                /**
                 * Hit the back button and see the change reflected in both
                 * the query string AND the actual property value.
                 */
                ->back()
                ->waitForLivewire()
                ->assertSeeIn('@output', 'baz')
                ->assertQueryStringHas('foo', 'baz')

                /**
                 * Setting a property to a value marked as "except"
                 * removes the property entirely from the query string.
                 */
                ->assertSeeIn('@bar-output', 'baz')
                ->assertQueryStringHas('bar')
                ->type('@bar-input', 'except-value')
                ->waitForLivewire()
                ->assertQueryStringMissing('bar')

                /**
                 * Add a nested component on the page and make sure
                 * both components play nice with each other.
                 */
                ->assertQueryStringMissing('baz')
                ->click('@show-nested')
                ->waitForLivewire()
                ->assertQueryStringHas('baz', 'bop')
                ->assertSeeIn('@baz-output', 'bop')
                ->type('@baz-input', 'lop')
                ->waitForLivewire()
                ->assertQueryStringHas('baz', 'lop')
                ->type('@input', 'plop')
                ->waitForLivewire()
                ->type('@baz-input', 'ploop')
                ->waitForLivewire()
                ->assertQueryStringHas('foo', 'plop')
                ->assertQueryStringHas('baz', 'ploop')
                ->back()
                ->back()
                ->assertQueryStringHas('baz', 'lop');
        });
    }
}
