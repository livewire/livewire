<?php

namespace Tests\Browser\DeferredAlpine;

use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test_dollar_wire_works_on_late_interaction()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, FooComponent::class)
                ->assertValue('@fooInput', 'Hello Livewire')
                ->click('@fooButton')
                ->assertValue('@fooInput', 'Hello Alpine')
            ;
        });
    }

    public function test_dollar_wire_works_without_entangle()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, BarComponent::class)
                ->assertSeeIn('@barOutput', '1')
                ->waitForLivewire()
                ->click('@barButton')
                ->assertSeeIn('@barOutput', '2')
            ;
        });
    }

    public function test_dollar_wire_entangle_works()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, BazComponent::class)
                ->assertSeeIn('@bazOutput', '10')
                ->assertValue('@bazModelInput', '10')
                ->assertValue('@bazWireInput', '10')
                ->waitForLivewire()
                ->click('@bazModelButton')
                ->assertSeeIn('@bazOutput', '11')
                ->assertValue('@bazWireInput', '11')
                ->assertValue('@bazModelInput', '11')
                ->waitForLivewire()
                ->click('@bazWireButton')
                ->assertSeeIn('@bazOutput', '12')
                ->assertValue('@bazWireInput', '12')
                ->assertValue('@bazModelInput', '12')
            ;
        });
    }
}
