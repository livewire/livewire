<?php

namespace Tests\Browser\Alpine\Entangle;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Alpine\Entangle\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Can mutate an array in Alpine and reflect in Livewire.
                 */
                ->assertDontSeeIn('@output.alpine', 'baz')
                ->assertDontSeeIn('@output.blade', 'baz')
                ->waitForLivewire()->click('@button')
                ->assertSeeIn('@output.alpine', 'baz')
                ->assertSeeIn('@output.blade', 'baz')

                /**
                 * Can conditionally load in a new Alpine component that uses @entangle
                 */
                ->assertNotPresent('@bob.alpine')
                ->assertSeeIn('@bob.blade', 'before')
                ->waitForLivewire()->click('@bob.show')
                ->assertSeeIn('@bob.alpine', 'before')
                ->assertSeeIn('@bob.blade', 'before')
                ->waitForLivewire()->click('@bob.button')
                ->assertSeeIn('@bob.alpine', 'after')
                ->assertSeeIn('@bob.blade', 'after')
            ;
        });
    }
}
