<?php

namespace Tests\Browser\Nesting;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class, '?showChild=true')
                /**
                 * click inside nested component is assigned to nested component
                 */
                ->waitForLivewire()->click('@button.nested')
                ->assertSeeIn('@output.nested', 'foo')
                ->waitForLivewire()->click('@button.toggleChild')
                ->refresh()->pause(500)

                /**
                 * added component gets initialized
                 */
                ->waitForLivewire()->click('@button.toggleChild')
                ->waitForLivewire()->click('@button.nested')
                ->assertSeeIn('@output.nested', 'foo')

                /**
                 * can switch components
                 */
                ->waitForLivewire()->click('@button.changeKey')
                ->assertDontSeeIn('@output.nested', 'foo')
                ->waitForLivewire()->click('@button.nested')
                ->assertSeeIn('@output.nested', 'foo')

                /**
                 * Nested component constructors aren't called again when they already exist on the page
                 */
                ->waitForLivewire()->type('@input.search', 2)
                ->assertDontSee('Item #1')
                ->assertSee('Item #2')
                ->waitForLivewire()->type('@input.search', ' ')
                ->assertSee('Item #1')
                ->assertSee('Item #2');
        });
    }
}
