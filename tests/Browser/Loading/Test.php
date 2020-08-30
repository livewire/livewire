<?php

namespace Tests\Browser\Loading;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                ->tap($this->initialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@button');

                    $browser->assertVisible('@show');
                    $browser->assertNotVisible('@hide');

                    $browser->assertHasClass('@add-class', 'foo');
                    $browser->assertMissingClass('@remove-class', 'hidden');

                    $browser->assertAttribute('@add-attr', 'disabled', 'true');
                    $browser->assertAttributeMissing('@remove-attr', 'disabled');

                    $browser->assertNotVisible('@targeting');
                })
                ->tap($this->initialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@target-button');

                    $browser->assertVisible('@targeting');
                })
            ;
        });
    }

    protected function initialState()
    {
        return function (Browser $browser) {
            $browser->assertNotVisible('@show');
            $browser->assertVisible('@hide');

            $browser->assertAttribute('@add-class', 'class', '');
            $browser->assertAttribute('@remove-class', 'class', 'foo');

            $browser->assertAttributeMissing('@add-attr', 'disabled');
            $browser->assertAttribute('@remove-attr', 'disabled', 'true');

            $browser->assertNotVisible('@targeting');
        };
    }
}
