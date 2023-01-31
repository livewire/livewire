<?php

namespace Tests\Browser\Dirty;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Add class for dirty data.
                 */
                ->assertSourceMissing(' class="foo-dirty"')
                ->type('@foo', 'bar')
                ->assertSourceHas(' class="foo-dirty"')
                ->pause(150)
                ->waitForLivewire()->click('@dummy')
                ->assertSourceMissing(' class="foo-dirty"')

                /**
                 * Remove class.
                 */
                ->assertSourceHas(' class="bar-dirty"')
                ->type('@bar', 'baz')
                ->assertSourceMissing(' class="bar-dirty"')
                ->pause(150)
                ->waitForLivewire()->click('@dummy')
                ->pause(25)
                ->assertSourceHas(' class="bar-dirty"')

                /**
                 * Set dirty using wire:target
                 */
                ->assertSourceMissing(' class="baz-dirty"')
                ->type('@baz.input', 'baz')
                ->assertSourceHas(' class="baz-dirty"')
                ->pause(150)
                ->waitForLivewire()->click('@dummy')
                ->pause(25)
                ->assertSourceMissing(' class="baz-dirty"')

                /**
                 * wire:dirty without modifiers, but with wire:target
                 */
                ->assertMissing('@bob.target')
                ->type('@bob.input', 'baz')
                ->assertVisible('@bob.target')
                ->pause(150)
                ->waitForLivewire()->click('@dummy')
                ->pause(25)
                ->assertMissing('@bob.target')

                /**
                 * multiple wire:dirty directives on a single element
                 */
                ->assertSourceMissing(' class="ted-dirty"')
                ->assertSourceHas(' class="ted-clean"')
                ->assertSourceMissing(' data-ted-dirty')
                ->assertSourceHas(' data-ted-clean')
                ->type('@ted.input', 'baz')
                ->assertSourceHas(' class="ted-dirty"')
                ->assertSourceMissing(' class="ted-clean"')
                ->assertSourceHas(' data-ted-dirty')
                ->assertSourceMissing(' data-ted-clean')
                ->pause(150)
                ->waitForLivewire()->click('@dummy')
                ->pause(25)
                ->assertSourceMissing(' class="ted-dirty"')
                ->assertSourceHas(' class="ted-clean"')
                ->assertSourceMissing(' data-ted-dirty')
                ->assertSourceHas(' data-ted-clean')
            ;
        });
    }
}
