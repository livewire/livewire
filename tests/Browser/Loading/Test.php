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
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@button');

                    $browser->assertVisible('@show');
                    $browser->assertNotVisible('@hide');

                    $browser->assertHasClass('@add-class', 'foo');
                    $browser->assertClassMissing('@remove-class', 'hidden');

                    $browser->assertAttribute('@add-attr', 'disabled', 'true');
                    $browser->assertAttributeMissing('@remove-attr', 'disabled');

                    $browser->assertAttribute('@add-both', 'disabled', 'true');
                    $browser->assertAttributeMissing('@remove-both', 'disabled');
                    $browser->assertHasClass('@add-both', 'foo');
                    $browser->assertClassMissing('@remove-both', 'hidden');

                    $browser->assertNotVisible('@targeting');
                    $browser->assertNotVisible('@targeting-both');
                    $browser->assertClassMissing('@self-target-button', 'foo');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@target-button');

                    $browser->waitFor('@targeting');
                    $browser->assertVisible('@targeting-both');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@target-button-w-param');

                    $browser->waitFor('@targeting');
                    $browser->assertVisible('@targeting-both');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@self-target-button');

                    $browser->assertNotVisible('@targeting');
                    $browser->assertVisible('@targeting-both');
                    $browser->assertHasClass('@self-target-button', 'foo');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->check('@self-target-model');

                    $browser->assertNotVisible('@targeting');
                    $browser->assertNotVisible('@targeting-both');
                    $browser->assertHasClass('@self-target-model', 'foo');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire()->click('@error-button')
                ->click('#livewire-error')
                ->tap($this->assertInitialState())
            ;
        });
    }

    protected function assertInitialState()
    {
        return function (Browser $browser) {
            $browser->assertNotVisible('@show');
            $browser->assertVisible('@hide');

            $browser->assertAttribute('@add-class', 'class', '');
            $browser->assertAttribute('@remove-class', 'class', 'foo');

            $browser->assertAttributeMissing('@add-attr', 'disabled');
            $browser->assertAttribute('@remove-attr', 'disabled', 'true');

            $browser->assertClassMissing('@add-both', 'foo');
            $browser->assertHasClass('@remove-both', 'foo');
            $browser->assertAttributeMissing('@add-both', 'disabled');
            $browser->assertAttribute('@remove-both', 'disabled', 'true');

            $browser->assertNotVisible('@targeting');
            $browser->assertNotVisible('@targeting-both');

            $browser->assertClassMissing('@self-target-button', 'foo');
            $browser->assertClassMissing('@self-target-model', 'foo');
        };
    }
}
