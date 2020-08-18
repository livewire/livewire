<?php

namespace Tests\Browser\DataBinding\InputText;

use Livewire\Livewire;
use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Has initial value.
                 */
                ->assertInputValue('@foo', 'initial')

                /**
                 * Can set value
                 */
                ->type('@foo', 'subsequent')
                ->waitForLivewire()
                ->assertSeeIn('@foo.output', 'subsequent')

                /**
                 * Can change value
                 */
                ->assertDontSeeIn('@foo.output', 'changed')
                ->click('@foo.change')
                ->waitForLivewire()
                ->assertSeeIn('@foo.output', 'changed')

                /**
                 * Value will change if marked as dirty AND input is focused.
                 */
                ->click('@foo')
                ->tap(function ($b) { $b->script('window.livewire.first().set("foo", "changed-again")'); })
                ->waitForLivewire()
                ->assertInputValue('@foo', 'changed-again')

                /**
                 * Value won't change if focused but NOT dirty.
                 */
                ->click('@foo')
                ->tap(function ($b) { $b->script('window.livewire.first().sync("foo", "changed-alot")'); })
                ->waitForLivewire()
                ->assertSeeIn('@foo.output', 'changed-alot')
                ->assertInputValue('@foo', 'changed-again')

                /**
                 * Can set nested value
                 */
                ->type('@bar', 'nested')
                ->waitForLivewire()
                ->assertSeeIn('@bar.output', '{"baz":{"bob":"nested"}}')

                /**
                 * Can set lazy value
                 */
                ->click('@baz') // Set focus.
                ->type('@baz', 'lazy')
                ->pause(150) // Wait for the amount of time it would have taken to do a round trip.
                ->assertDontSeeIn('@baz.output', 'lazy')
                ->click('@refresh') // Blur input and send action.
                ->waitForLivewire()
                ->assertSeeIn('@baz.output', 'lazy')

                /**
                 * Can set deferred value
                 */
                ->click('@bob') // Set focus.
                ->type('@bob', 'deferred')
                ->assertDontSeeIn('@bob.output', 'deferred')
                ->click('@foo') // Blur input to make sure this is more thans "lazy".
                ->pause(150) // Pause for upper-bound of most round-trip lengths.
                ->assertDontSeeIn('@bob.output', 'deferred')
                ->click('@refresh')
                ->waitForLivewire()
                ->assertSeeIn('@bob.output', 'deferred')
                ;
        });
    }
}
