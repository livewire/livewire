<?php

namespace Tests\Browser\DataBinding\InputCheckboxRadio;

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
                ->assertChecked('@foo')
                ->assertSeeIn('@foo.output', 'true')

                /**
                 * Can set value
                 */
                ->waitForLivewire()->uncheck('@foo')
                ->assertNotChecked('@foo')
                ->assertSeeIn('@foo.output', 'false')

                /**
                 * Can set value from an array
                 */
                ->assertNotChecked('@bar.a')->assertChecked('@bar.b')->assertNotChecked('@bar.c')
                ->assertSeeIn('@bar.output', '["b"]')
                ->waitForLivewire()->check('@bar.c')
                ->assertNotChecked('@bar.a')->assertChecked('@bar.b')->assertChecked('@bar.c')
                ->assertSeeIn('@bar.output', '["b","c"]')

                /**
                 * Can set value from a number
                 */
                ->assertChecked('@baz')
                ;
        });
    }

    /** @test */
    public function checkboxes_fuzzy_match_integer_values()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, CheckboxesWithIntsComponent::class)
                // ->tinker()
                ->assertNotChecked('@int1')
                ->assertChecked('@int2')
                ->assertChecked('@int3')

                ->waitForLivewire()->uncheck('@int2')

                ->assertNotChecked('@int1')
                ->assertNotChecked('@int2')
                ->assertChecked('@int3')

                ->waitForLivewire()->uncheck('@int3')

                ->assertNotChecked('@int1')
                ->assertNotChecked('@int2')
                ->assertNotChecked('@int3')

                ->waitForLivewire()->check('@int2')

                ->assertNotChecked('@int1')
                ->assertChecked('@int2')
                ->assertNotChecked('@int3')
                ;
        });
    }
}
