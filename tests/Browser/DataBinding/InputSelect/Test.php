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
                ->waitForLivewire()->select('@multiple.input', ['bar'])
                ->assertSelected('@multiple.input', 'bar')
                ->assertSeeIn('@multiple.output', 'bar')
                ->waitForLivewire()->select('@multiple.input', ['bar', 'baz'])
                ->assertSelected('@multiple.input', 'baz')
                ->assertSeeIn('@multiple.output', 'bar')
                ->assertSeeIn('@multiple.output', 'baz');
        });
    }

    /** @test */
    public function it_can_handle_having_selected_on_an_option()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, SelectWithSelectedOnOption::class)
                ->assertSeeIn('@output', '3')
                ->assertSelected('@select-input', '3')
                ->waitForLivewire()->select('@select-input', '4')
                ->assertSeeIn('@output', '4')
                ->assertSelected('@select-input', '4')
                ;
        });
    }

    /** @test */
    public function it_renders_wire_model_selected_option_even_if_html_selected_is_different()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, SelectWithIncorrectSelectedOnOption::class)
                ->assertSeeIn('@output', '3')
                ->assertSelected('@select-input', '3')
                ->waitForLivewire()->click('@toggle')
                ->assertSeeIn('@output', '3')
                ->assertSelected('@select-input', '3')
                ->waitForLivewire()->select('@select-input', '2')
                ->assertSeeIn('@output', '2')
                ->assertSelected('@select-input', '2')
                ;
        });
    }
}
