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
                    $browser->assertNotVisible('@targeting-param');
                    $browser->assertClassMissing('@self-target-button', 'foo');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@button');

                    $browser->pause(100);

                    $browser->assertNotVisible('@show-w-delay');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@button');

                    $browser->pause(200);

                    $browser->assertVisible('@show-w-delay');
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
                    $browser->assertVisible('@targeting-param');
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

    public function test_different_display_properties_when_loading()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, CustomDisplayProperty::class)
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="default"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline-block"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="block"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="flex"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="table"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="grid"]\')).display', 'none')
                ->waitForLivewire(function ($b) {
                    $b->click('@refresh');
                    $b->pause(50);
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="default"]\')).display', 'inline-block');
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline-block"]\')).display', 'inline-block');
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline"]\')).display', 'inline');
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="block"]\')).display', 'block');
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="flex"]\')).display', 'flex');
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="table"]\')).display', 'table');
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="grid"]\')).display', 'grid');
                })
            ;
        });
    }

    protected function assertInitialState()
    {
        return function (Browser $browser) {
            $browser->assertNotVisible('@show');
            $browser->assertVisible('@hide');

            $browser->assertNotVisible('@show-w-delay');

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
            $browser->assertNotVisible('@targeting-param');

            $browser->assertClassMissing('@self-target-button', 'foo');
            $browser->assertClassMissing('@self-target-model', 'foo');
        };
    }
}
