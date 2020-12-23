<?php

namespace Tests\Browser\DataBinding\InputSelect;

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
                 * Standard select.
                 */
                ->assertDontSeeIn('@single.output', 'bar')
                ->waitForLivewire()->select('@single.input', 'bar')
                ->assertSelected('@single.input', 'bar')
                ->assertSeeIn('@single.output', 'bar')

                /**
                 * Standard select with value attributes.
                 */
                ->assertDontSeeIn('@single-value.output', 'par')
                ->waitForLivewire()->select('@single-value.input', 'par')
                ->assertSelected('@single-value.input', 'par')
                ->assertSeeIn('@single-value.output', 'par')

                /**
                 * Standard select with value attributes.
                 */
                ->assertSeeIn('@single-number.output', '3')
                ->assertSelected('@single-number.input', '3')
                ->waitForLivewire()->select('@single-number.input', '4')
                ->assertSeeIn('@single-number.output', '4')
                ->assertSelected('@single-number.input', '4')

                /**
                 * Select with placeholder default.
                 */
                ->assertSelected('@placeholder.input', '')
                ->assertDontSeeIn('@placeholder.output', 'foo')
                ->waitForLivewire()->select('@placeholder.input', 'foo')
                ->assertSelected('@placeholder.input', 'foo')
                ->assertSeeIn('@placeholder.output', 'foo')

                /**
                 * Select multiple.
                 */
                ->assertDontSeeIn('@multiple.output', 'bar')
                ->waitForLivewire()->select('@multiple.input', 'bar')
                ->assertSelected('@multiple.input', 'bar')
                ->assertSeeIn('@multiple.output', 'bar')
                ->waitForLivewire()->select('@multiple.input', 'baz')
                ->assertSelected('@multiple.input', 'baz')
                ->assertSeeIn('@multiple.output', 'bar')
                ->assertSeeIn('@multiple.output', 'baz');
        });
    }
}
