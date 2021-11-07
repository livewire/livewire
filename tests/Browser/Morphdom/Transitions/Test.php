<?php

namespace Tests\Browser\Morphdom\Transitions;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                ->assertSee('Mark')
                ->assertSee('Mario')
                ->assertSee('Milton')
                ->assertDontSee('John')
                ->type('search', 'J')
                ->waitForLivewire()
                ->pause(500)
                ->assertHasClass('[name="names.John"]', 'slide-up');
        });
    }
}
