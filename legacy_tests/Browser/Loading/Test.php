<?php

namespace LegacyTests\Browser\Loading;

use Laravel\Dusk\Browser;
use LegacyTests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@button');

                    $browser->pause(1);
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
                    $browser->assertNotVisible('@targeting-js-param');
                    $browser->assertClassMissing('@self-target-button', 'foo');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@button');

                    $browser->pause(101);

                    $browser->assertNotVisible('@show-w-delay');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@button');

                    $browser->pause(225);

                    $browser->assertVisible('@show-w-delay');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@target-button');

                    $browser->waitFor('@targeting');
                    $browser->assertVisible('@targeting-both');
                    $browser->assertNotVisible('@target-top-level-property');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@target-button-w-param');

                    $browser->waitFor('@targeting');
                    $browser->assertVisible('@targeting-both');
                    $browser->assertVisible('@targeting-param');
                    $browser->assertVisible('@targeting-js-param');
                    $browser->assertNotVisible('@target-top-level-property');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@target-button-w-js-object-param');

                    $browser->waitFor('@targeting');
                    $browser->assertVisible('@targeting-both');
                    $browser->assertVisible('@targeting-js-object-param');
                    $browser->assertNotVisible('@targeting-js-wrong-object-param');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@self-target-button');

                    $browser->assertNotVisible('@targeting');
                    $browser->assertVisible('@targeting-both');
                    $browser->assertNotVisible('@target-top-level-property');
                    $browser->assertHasClass('@self-target-button', 'foo');
                })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->check('@self-target-model');

                    $browser->assertNotVisible('@targeting');
                    $browser->assertNotVisible('@targeting-both');
                    $browser->assertNotVisible('@target-top-level-property');
                    $browser->assertHasClass('@self-target-model', 'foo');
                })
                ->tap($this->assertInitialState())
                // @todo: See if this loading behavior is right for error requests...
                // ->waitForLivewire(function (Browser $browser) {
                    // $browser->click('@error-button');

                    // $browser->pause(1);
                    // $browser->assertNotVisible('@hide');
                    // $browser->assertVisible('@show');

                    // $browser->waitFor('#livewire-error');

                    // $browser->assertVisible('@hide');
                    // $browser->assertNotVisible('@show');
                // })
                ->tap($this->assertInitialState())
                ->waitForLivewire(function (Browser $browser) {
                    $browser->type('@nested-property-input', 'a');

                    $browser->waitFor('@target-top-level-property');

                    $browser->assertNotVisible('@targeting');
                    $browser->assertNotVisible('@targeting-both');
                    $browser->assertVisible('@target-top-level-property');
                })
            ;
        });
    }

    public function test_different_display_properties_when_loading()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, CustomDisplayProperty::class)
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="default"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline-block"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="block"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="flex"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="table"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="grid"]\')).display', 'none')
                ->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline-flex"]\')).display', 'none')
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
                    $b->assertScript('getComputedStyle(document.querySelector(\'[dusk="inline-flex"]\')).display', 'inline-flex');
                })
            ;
        });
    }

    public function test_different_delay_durations()
    {
        $this->browse(function ($browser) {
            $this->visitLivewireComponent($browser, ComponentWithLoadingDelays::class)
                ->assertNotVisible('@delay-none')
                ->assertNotVisible('@delay-shortest')
                ->waitForLivewire(function (Browser $browser) {
                    $browser->click('@load')
                        ->assertNotVisible('@delay-shortest')
                        ->assertVisible('@delay-none');
                })->waitForLivewire(function (Browser $browser) {
                    $browser->click('@load')
                            ->pause(51)
                            ->assertNotVisible('@delay-shorter')
                            ->assertVisible('@delay-shortest');
                })->waitForLivewire(function (Browser $browser) {
                    $browser->click('@load')
                            ->pause(101)
                            ->assertNotVisible('@delay-short')
                            ->assertVisible('@delay-shorter');
                })->waitForLivewire(function (Browser $browser) {
                    $browser->click('@load')
                            ->pause(151)
                            ->assertNotVisible('@delay')
                            ->assertNotVisible('@delay-default')
                            ->assertVisible('@delay-short');
                })->waitForLivewire(function (Browser $browser) {
                    $browser->click('@load')
                            ->pause(201)
                            ->assertNotVisible('@delay-long')
                            ->assertVisible('@delay')
                            ->assertVisible('@delay-default');
                })->waitForLivewire(function (Browser $browser) {
                    $browser->click('@load')
                            ->pause(301)
                            ->assertNotVisible('@delay-longer')
                            ->assertVisible('@delay-long');
                })->waitForLivewire(function (Browser $browser) {
                    $browser->click('@load')
                            ->pause(501)
                            ->assertNotVisible('@delay-longest')
                            ->assertVisible('@delay-longer');
                });
                // @todo: this is flaky...
                // })->waitForLivewire(function (Browser $browser) {
                //     $browser->click('@load')
                //             ->pause(1002)
                //             ->assertVisible('@delay-longest');
                // });
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
            $browser->assertNotVisible('@targeting-js-param');
            $browser->assertNotVisible('@targeting-js-object-param');
            $browser->assertNotVisible('@targeting-js-wrong-object-param');

            $browser->assertClassMissing('@self-target-button', 'foo');
            $browser->assertClassMissing('@self-target-model', 'foo');
        };
    }
}
