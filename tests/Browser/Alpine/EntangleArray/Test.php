<?php

namespace Tests\Browser\Alpine\EntangleArray;

use Livewire\Livewire;
use Tests\Browser\TestCase;
use Tests\Browser\Alpine\EntangleArray\Component;

class Test extends TestCase
{
    public function test()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, Component::class)
                /**
                 * Can entangle arrays
                 */
                ->assertSeeIn('@output.alpine', 1)
                ->assertSeeIn('@output.alpine', 2)
                ->assertSeeIn('@output.alpine', 3)
                ->assertSeeIn('@output.alpine', 4)
                ->assertSeeIn('@output.livewire', 1)
                ->assertSeeIn('@output.livewire', 2)
                ->assertSeeIn('@output.livewire', 3)
                ->assertSeeIn('@output.livewire', 4)
                ->pause(150)

                /**
                 * The value 's' doesn't matter here, just triggers a change
                 * on the input to send a request to the server
                 */
                ->type('@search', 's')
                ->pause(150)

                ->assertSeeIn('@output.alpine', 5)
                ->assertSeeIn('@output.alpine', 6)
                ->assertSeeIn('@output.alpine', 7)
                ->assertSeeIn('@output.alpine', 8)
                ->assertSeeIn('@output.livewire', 5)
                ->assertSeeIn('@output.livewire', 6)
                ->assertSeeIn('@output.livewire', 7)
                ->assertSeeIn('@output.livewire', 8)
            ;
        });
    }
}
