<?php

namespace LegacyTests\Browser\DataBinding\InputTextarea;

use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                /**
                 * Can change value
                 */
                ->assertDontSeeIn('@foo.output', 'changed')
                ->waitForLivewire()->click('@foo.change')
                ->assertSeeIn('@foo.output', 'changed')

                /**
                 * Class change works as expected and doesn't wipe the textarea's value
                 */
                ->assertInputValue('@foo', 'changed')
                ->assertSourceMissing('class="foo"')
                ->waitForLivewire()->click('@foo.add-class')
                ->assertInputValue('@foo', 'changed')
                ->assertSourceHas('class="foo"')

                /**
                 * Value will change if marked as dirty AND input is focused.
                 */
                ->waitForLivewire(function ($b) {
                    $b->click('@foo');
                    $b->script('window.Livewire.first().updateFooTo("changed-again")');
                })
                ->assertInputValue('@foo', 'changed-again')

                /**
                 * Value won't change if focused but NOT dirty.
                 */
                // @todo: waiting to see if we need to bring this "unintrusive" V2 functionality back...
                // ->waitForLivewire(function ($b) {
                //     $b->click('@foo');
                //     $b->script('window.Livewire.first().set("foo", "changed-alot")');
                // })
                // ->assertSeeIn('@foo.output', 'changed-alot')
                // ->assertInputValue('@foo', 'changed-again')

                /**
                 * Can set lazy value
                 */
                ->click('@baz') // Set focus.
                ->type('@baz', 'lazy')
                ->pause(150) // Wait for the amount of time it would have taken to do a round trip.
                ->assertDontSeeIn('@baz.output', 'lazy')
                ->waitForLivewire()->click('@refresh') // Blur input and send action.
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
                ->waitForLivewire()->click('@refresh')
                ->assertSeeIn('@bob.output', 'deferred')
                ;
        });
    }
}
