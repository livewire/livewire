<?php

namespace Tests\Browser\StringNormalization;

use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Tests\Browser\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function (Browser $browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Click button to trigger string re-encoding in dehydrate
                 */
                ->waitForLivewire()->click('#add_number')
                ->pause('500')
                ->assertSee('Add Number') // current version throws an error in Safari
            ;
        });
    }
}
